<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Expense;
use App\Models\FuelLog;
use App\Models\InsurancePolicy;
use App\Models\Maintenance;
use App\Models\SparePart;
use App\Models\SupplierInvoice;
use App\Models\SupplierPayment;
use App\Models\Vehicle;
use App\Models\VehicleFilterChange;
use App\Models\VehicleOilChange;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard with live stats.
     */
    public function index(): View
    {
        // ---------------------------------------------------------------
        // المركبات تُحمَّل مرة واحدة مع علاقات تغيير الزيت/الفلاتر، وتُشتق
        // منها بطاقات العدّ وبيانات توزيع المركبات — بدلًا من استعلام لكل
        // بطاقة. الـ eager loading يمنع مشكلة N+1 في أقسام "المستحقة" بالأسفل.
        // ---------------------------------------------------------------
        $vehicles = Vehicle::with(['oilChanges.oil', 'filterChanges.filter'])->get();

        $totalVehicles = $vehicles->count();
        $activeVehicles = $vehicles->where('status', 'active')->count();
        $maintenanceVehicles = $vehicles->where('status', 'maintenance')->count();
        $stoppedVehicles = $vehicles->where('status', 'stopped')->count();

        // توزيع المركبات حسب الحالة لمخطط الدونات — من نفس مجموعة $vehicles.
        $vehicleStatusData = $vehicles->groupBy('status')->map->count()->toArray();

        // ---------------------------------------------------------------
        // الرخص والتأمينات القريبة من الانتهاء (خلال 30 يومًا). جلب واحد
        // لكل منهما يُستخدم للعدّ ولعرض جدول التفاصيل أسفل البطاقات.
        // ---------------------------------------------------------------
        $expiringLicenses = Driver::whereBetween('license_expiry_date', [
            now()->startOfDay(),
            now()->addDays(30)->endOfDay(),
        ])->orderBy('license_expiry_date')
            ->get();

        $licensesExpiringSoon = $expiringLicenses->count();

        $expiringPolicies = InsurancePolicy::where('is_current', true)
            ->where('end_date', '<=', now()->addDays(30)->endOfDay())
            ->with('vehicle')
            ->orderBy('end_date')
            ->get();

        $insurancesExpiringSoon = $expiringPolicies->count();

        // ---------------------------------------------------------------
        // تغييرات الزيت/الفلاتر المستحقة: تُعاد من نفس منطق currentOilStatus()/
        // currentFilterStatus() (أحدث سجل لكل نوع). العلاقات محمّلة مسبقًا
        // أعلاه، وفحص is_overdue يقرأ العداد الحالي للمركبة دون استعلام إضافي.
        // ---------------------------------------------------------------
        $oilChangesDue = collect();
        $filterChangesDue = collect();

        foreach ($vehicles as $vehicle) {
            $overdueOils = $vehicle->currentOilStatus()
                ->each(fn(VehicleOilChange $change) => $change->setRelation('vehicle', $vehicle))
                ->filter(fn(VehicleOilChange $change) => $change->is_overdue);

            if ($overdueOils->isNotEmpty()) {
                $oilChangesDue = $oilChangesDue->merge($overdueOils);
            }

            $overdueFilters = $vehicle->currentFilterStatus()
                ->each(fn(VehicleFilterChange $change) => $change->setRelation('vehicle', $vehicle))
                ->filter(fn(VehicleFilterChange $change) => $change->is_overdue);

            if ($overdueFilters->isNotEmpty()) {
                $filterChangesDue = $filterChangesDue->merge($overdueFilters);
            }
        }

        // عدد المركبات التي عليها أي تغيير مستحق (لا عدد السجلات نفسها).
        $dueOilChanges = $oilChangesDue->pluck('vehicle_id')->unique()->count();
        $dueFilterChanges = $filterChangesDue->pluck('vehicle_id')->unique()->count();

        // تكلفة الوقود الشهرية = مجموع total_value (ليترات × سعر − خصم)
        // المحسوب داخل FuelLog::record() — لا إعادة حساب في SQL.
        $fleetFuelCost = FuelLog::whereMonth('filled_at', now()->month)
            ->whereYear('filled_at', now()->year)
            ->sum('total_value');

        // تكلفة الصيانة الشهرية من الأوامر المستكملة فقط (completed):
        // الأوامر قيد الصيانة أو الملغاة لا تمثل تكلفة فعلية.
        $monthlyMaintenanceCost = Maintenance::whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where('status', 'completed')
            ->sum('total_cost');

        // مصروفات الشهر (يدوية وتلقائية) من جدول المصروفات.
        $monthlyExpenses = (float) Expense::whereBetween('expense_date', [
            now()->startOfMonth(),
            now()
        ])->sum('amount');


        // إجمالي أرصدة الموردين = كامل قيمة الفواتير − كامل المدفوعات.
        // رياضيًا يساوي Σ(رصيد كل مورد) لكن باستعلامين فقط بدلًا من N+1.
        $supplierBalancesTotal = (float) SupplierInvoice::sum('amount')
            - (float) SupplierPayment::sum('amount');

        // قطع الغيار منخفضة المخزون: withSum يجلب مجموع الكميات في استعلام
        // تجميعي واحد، والفلتر يعيد استخدام is_low_stock (المتوفر ≤ الحد الأدنى).
        $lowStockParts = SparePart::query()
            ->withSum('transactions', 'quantity')
            ->get()
            ->filter(fn(SparePart $part) => $part->is_low_stock);

        $lowStockCount = $lowStockParts->count();

        // أعلى 5 مركبات استهلاكًا للوقود هذا الشهر (باللترات).
        $topFuelVehicles = FuelLog::whereMonth('filled_at', now()->month)
            ->whereYear('filled_at', now()->year)
            ->select('vehicle_id', DB::raw('SUM(COALESCE(liters, 0)) as total_liters'))
            ->with('vehicle')
            ->groupBy('vehicle_id')
            ->orderByDesc('total_liters')
            ->limit(5)
            ->get();

        // أعلى 5 مركبات تكلفةً في الصيانة — الأوامر المستكملة فقط.
        $topMaintenanceVehicles = Maintenance::where('status', 'completed')
            ->select('vehicle_id', DB::raw('SUM(COALESCE(total_cost, 0)) as total_cost'))
            ->with('vehicle')
            ->groupBy('vehicle_id')
            ->orderByDesc('total_cost')
            ->limit(5)
            ->get();

        // ---------------------------------------------------------------
        // بيانات مخطط استهلاك الوقود — آخر 6 أشهر باللترات.
        // (مخطط Chart.js بالأسفل، لا يُلمس هذا المخرج).
        // ---------------------------------------------------------------
        $monthNames = [
            '01' => 'يناير',
            '02' => 'فبراير',
            '03' => 'مارس',
            '04' => 'أبريل',
            '05' => 'مايو',
            '06' => 'يونيو',
            '07' => 'يوليو',
            '08' => 'أغسطس',
            '09' => 'سبتمبر',
            '10' => 'أكتوبر',
            '11' => 'نوفمبر',
            '12' => 'ديسمبر',
        ];

        $monthlyFuelConsumption = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthKey = $date->format('m');
            $monthLabel = $monthNames[$monthKey] ?? $date->format('M');
            $liters = FuelLog::whereMonth('filled_at', $date->month)
                ->whereYear('filled_at', $date->year)
                ->sum('liters');
            $monthlyFuelConsumption[$monthLabel] = (float) $liters;
        }

        return view('dashboard', [
            'totalVehicles' => $totalVehicles,
            'activeVehicles' => $activeVehicles,
            'maintenanceVehicles' => $maintenanceVehicles,
            'stoppedVehicles' => $stoppedVehicles,
            'licensesExpiringSoon' => $licensesExpiringSoon,
            'insurancesExpiringSoon' => $insurancesExpiringSoon,
            'dueOilChanges' => $dueOilChanges,
            'fleetFuelCost' => $fleetFuelCost,
            'monthlyMaintenanceCost' => $monthlyMaintenanceCost,
            'topFuelVehicles' => $topFuelVehicles,
            'topMaintenanceVehicles' => $topMaintenanceVehicles,
            'vehicleStatusData' => $vehicleStatusData,
            'monthlyFuelConsumption' => $monthlyFuelConsumption,
            'expiringPolicies' => $expiringPolicies,
            'expiringLicenses' => $expiringLicenses,
            'dueFilterChanges' => $dueFilterChanges,
            'oilChangesDue' => $oilChangesDue,
            'filterChangesDue' => $filterChangesDue,
            'monthlyExpenses' => $monthlyExpenses,
            'lowStockParts' => $lowStockParts,
            'lowStockCount' => $lowStockCount,
            'supplierBalancesTotal' => $supplierBalancesTotal,
        ]);
    }
}
