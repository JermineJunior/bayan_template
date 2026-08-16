<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notification;

class FleetAlertNotification extends Notification
{
    use Queueable;
    public function __construct(
        public string $alertType,   // matches a key in config('fleet_alerts'), e.g. 'oil_due'
        public string $message,     // human-readable, ready to display as-is
        public Model $related,      // the record this alert is about (VehicleOilChange, InsurancePolicy, Driver...)
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'alert_type' => $this->alertType,
            'message' => $this->message,
            'related_type' => $this->related::class,
            'related_id' => $this->related->id,
        ];
    }
}
