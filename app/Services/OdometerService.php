<?php

namespace App\Services;

use App\Exceptions\InvalidOdometerReadingException;
use App\Models\OdometerLog;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Facades\DB;

class OdometerService
{
    /**
     * تسجيل قراءة عداد جديدة، وتحديث vehicles.current_odometer.
     * هذا هو المكان الوحيد اللي يُسمح فيه بتعديل current_odometer بكل النظام —
     * أي موديول ثاني (وقود، صيانة، زيوت...) يقرأ الحقل فقط، ما يكتب فيه.
     *
     * @throws InvalidOdometerReadingException
     */
    public function record(
        Vehicle $vehicle,
        float $reading,
        User $recordedBy,
        bool $isCorrection = false,
        ?string $note = null,
    ): OdometerLog {
        // التحقق من صحة القراءة الجديدة
        if (! $isCorrection && $reading < $vehicle->current_odometer) {
            throw new InvalidOdometerReadingException(
                "القراءة ({$reading}) أقل من آخر قراءة مسجّلة ({$vehicle->current_odometer}). "
                .'لو هذا تصحيح مقصود لخطأ سابق، استخدم خيار التصحيح.'
            );
        }
        // التحقق من وجود سبب للتصحيح إذا كانت القراءة أقل من الحالية
        if ($isCorrection && empty($note)) {
            throw new InvalidOdometerReadingException(
                'لازم تذكر سبب التصحيح عند إدخال قراءة أقل من القراءة الحالية.'
            );
        }

        if ($isCorrection && ! $recordedBy->can('odometer.correct')) {
            throw new InvalidOdometerReadingException(
                'ما عندك صلاحية تصحيح قراءة العداد. راجع مدير الأسطول أو الأدمن.'
            );
        }

        return DB::transaction(function () use ($vehicle, $reading, $recordedBy, $isCorrection, $note) {
            $log = OdometerLog::create([
                'vehicle_id' => $vehicle->id,
                'reading' => $reading,
                'recorded_at' => now(),
                'recorded_by' => $recordedBy->id,
                'is_correction' => $isCorrection,
                'note' => $note,
            ]);

            $vehicle->update(['current_odometer' => $reading]);

            return $log;
        });
    }
}
