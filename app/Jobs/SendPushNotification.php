<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $bookingId;
    public $vehicleTypeId;
    public $city;
    public $note;

    public function __construct($bookingId, $vehicleTypeId, $city, $note = "offer request")
    {
        $this->bookingId = (int) $bookingId;
        $this->vehicleTypeId = $vehicleTypeId;
        $this->city = $city;
        $this->note = $note;
    }

    public function handle(): void
    {
        try {
            // FCM legacy HTTP API has been retired by Google and no Firebase
            // server key is configured for this installation. Log the intent
            // until the WhatsApp notification channel is wired up.
            Log::info("pushnotification.deferred", [
                "booking_id" => $this->bookingId,
                "vehicle_type" => $this->vehicleTypeId,
                "city" => $this->city,
                "note" => $this->note,
            ]);
        } catch (\Throwable $e) {
            Log::error("pushnotification.failed", [
                "booking_id" => $this->bookingId,
                "error" => $e->getMessage(),
            ]);
        }
    }
}
