<?php

namespace App\Console\Commands;

use App\Models\Driver;
use App\Models\InsurancePolicy;
use App\Models\Maintenance;
use App\Models\SparePart;
use App\Models\User;
use App\Models\Vehicle;
use App\Notifications\FleetAlertNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

class CheckFleetAlerts extends Command
{
    protected $signature = 'fleet:check-alerts';

    protected $description = 'Check oil/filter/insurance/license/stopped/maintenance thresholds and notify permitted users';

    public function handle(): void
    {
        $this->checkOil();
        $this->checkFilters();
        $this->checkInsurance();
        $this->checkDriverLicenses();
        $this->checkStoppedVehicles();
        $this->checkOverdueMaintenance();
        $this->checkLowStock();

        $this->info('Fleet alert check complete.');
    }

    private function checkOil(): void
    {
        $config = config('fleet_alerts.oil_due');

        Vehicle::all()->each(function (Vehicle $vehicle) use ($config) {
            foreach ($vehicle->currentOilStatus() as $record) {
                if ($record->remaining_change <= $config['threshold_km']) {
                    $this->notifyPermittedUsers(
                        'oil_due',
                        $config['permission'],
                        "موعد تغيير الزيت للمركبة {$vehicle->plate_number} — {$record->oil->oil_name} ({$this->remainingKmText($record->remaining_change)})",
                        $record,
                    );
                }
            }
        });
    }

    private function checkFilters(): void
    {
        $config = config('fleet_alerts.filter_due');

        Vehicle::all()->each(function (Vehicle $vehicle) use ($config) {
            foreach ($vehicle->currentFilterStatus() as $record) {
                if ($record->remaining_change <= $config['threshold_km']) {
                    $this->notifyPermittedUsers(
                        'filter_due',
                        $config['permission'],
                        "موعد تغيير الفلتر للمركبة {$vehicle->plate_number} — {$record->filter->filter_name} ({$this->remainingKmText($record->remaining_change)})",
                        $record,
                    );
                }
            }
        });
    }

    private function checkInsurance(): void
    {
        $config = config('fleet_alerts.insurance_expiring');

        InsurancePolicy::where('is_current', true)
            ->get()
            ->each(function (InsurancePolicy $policy) use ($config) {
                if ($policy->days_until_expiry <= $config['threshold_days']) {
                    $status = $policy->is_expired ? 'بوليصة التأمين منتهية' : 'بوليصة التأمين توشك على الانتهاء';
                    $this->notifyPermittedUsers(
                        'insurance_expiring',
                        $config['permission'],
                        "{$status} للمركبة {$policy->vehicle->plate_number} — رقم البوليصة: {$policy->policy_number} ({$this->remainingDaysText($policy->days_until_expiry)})",
                        $policy,
                    );
                }
            });
    }

    private function checkDriverLicenses(): void
    {
        $config = config('fleet_alerts.driver_license_expiring');

        Driver::whereNotNull('license_expiry_date')
            ->get()
            ->each(function (Driver $driver) use ($config) {
                $daysRemaining = now()->diffInDays($driver->license_expiry_date, false);

                if ($daysRemaining <= $config['threshold_days']) {
                    $status = $daysRemaining < 0 ? 'منتهية' : 'توشك على الانتهاء';
                    $this->notifyPermittedUsers(
                        'driver_license_expiring',
                        $config['permission'],
                        "رخصة القيادة {$status} — {$driver->full_name} ({$this->remainingDaysText($daysRemaining)})",
                        $driver,
                    );
                }
            });
    }

    /**
     * Vehicles marked as "stopped" for longer than the configured threshold.
     * The stop time comes from the stopped_at column, kept in sync with the
     * status field by the Vehicle model.
     */
    private function checkStoppedVehicles(): void
    {
        $config = config('fleet_alerts.vehicle_stopped');

        Vehicle::where('status', 'stopped')
            ->whereNotNull('stopped_at')
            ->where('stopped_at', '<=', now()->subDays($config['threshold_days']))
            ->get()
            ->each(function (Vehicle $vehicle) use ($config) {
                $daysStopped = (int) $vehicle->stopped_at->diffInDays(now());

                $this->notifyPermittedUsers(
                    'vehicle_stopped',
                    $config['permission'],
                    "المركبة {$vehicle->plate_number} متوقفة منذ أكثر من {$daysStopped} يوم",
                    $vehicle,
                );
            });
    }

    /**
     * Maintenance orders that are still open (not completed or cancelled) past
     * their end date.
     */
    private function checkOverdueMaintenance(): void
    {
        $config = config('fleet_alerts.maintenance_overdue');

        Maintenance::whereNotIn('status', ['completed', 'cancelled'])
            ->whereNotNull('end_date')
            ->where('end_date', '<', now()->startOfDay())
            ->with('vehicle')
            ->get()
            ->each(function (Maintenance $maintenance) use ($config) {
                $daysOverdue = (int) $maintenance->end_date->diffInDays(now());

                $this->notifyPermittedUsers(
                    'maintenance_overdue',
                    $config['permission'],
                    "أمر الصيانة {$maintenance->maintenance_number} للمركبة {$maintenance->vehicle->plate_number} لم يُغلق — تاريخ الانتهاء {$maintenance->end_date->format('Y-m-d')} (متأخر {$daysOverdue} يوم)",
                    $maintenance,
                );
            });
    }

    /**
     * Spare parts whose current quantity on hand has dropped to (or below)
     * their configured minimum.
     */
    private function checkLowStock(): void
    {
        $config = config('fleet_alerts.spare_parts_low_stock');

        SparePart::all()->each(function (SparePart $part) use ($config) {
            if ($part->is_low_stock) {
                $this->notifyPermittedUsers(
                    'spare_parts_low_stock',
                    $config['permission'],
                    "قطع الغيار منخفضة المخزون: {$part->name} ({$part->part_number}) — المتوفر ".number_format($part->quantity_on_hand).' / الحد الأدنى '.number_format($part->minimum_quantity),
                    $part,
                );
            }
        });
    }

    /**
     * Human-friendly Arabic wording for a remaining-distance figure, covering
     * overdue (negative) values as well.
     */
    private function remainingKmText(int $remaining): string
    {
        return $remaining <= 0
            ? 'متأخر '.abs($remaining).' كم'
            : 'باقٍ '.$remaining.' كم';
    }

    /**
     * Human-friendly Arabic wording for a remaining-days figure, covering
     * expired (negative) values as well.
     */
    private function remainingDaysText(int $remaining): string
    {
        return $remaining < 0
            ? 'انتهى منذ '.abs($remaining).' يوم'
            : 'باقٍ '.$remaining.' يوم';
    }

    /**
     * Notify every user with the given permission, skipping anyone who already has an
     * unread alert for this exact record — prevents the same overdue item from spamming
     * a notification every single day the check runs.
     */
    private function notifyPermittedUsers(string $alertType, string $permission, string $message, Model $related): void
    {
        User::permission($permission)->get()->each(function (User $user) use ($alertType, $message, $related) {
            $alreadyNotified = $user->unreadNotifications()
                ->where('data->related_type', $related::class)
                ->where('data->related_id', $related->id)
                ->exists();

            if (! $alreadyNotified) {
                $user->notify(new FleetAlertNotification($alertType, $message, $related));
            }
        });
    }
}
