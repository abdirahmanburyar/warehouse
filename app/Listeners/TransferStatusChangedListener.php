<?php

namespace App\Listeners;

use App\Events\TransferStatusChanged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Models\User;
use App\Notifications\TransferStatusUpdated;

class TransferStatusChangedListener implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TransferStatusChanged $event): void
    {
        // Log the transfer status change
        Log::info('Transfer status changed', [
            'transfer_id' => $event->transfer->id,
            'transfer_reference' => $event->transfer->transferID,
            'old_status' => $event->oldStatus,
            'new_status' => $event->newStatus,
            'changed_by' => $event->changedBy
        ]);

        // Here you can add additional logic based on the status change
        // For example, sending notifications to relevant users
        
        // Example: You could notify the warehouse manager when a transfer is approved
        if ($event->oldStatus === 'pending' && $event->newStatus === 'approved') {
            // Notify source warehouse manager
            if ($event->transfer->fromWarehouse && $event->transfer->fromWarehouse->manager_email) {
                Notification::route('mail', $event->transfer->fromWarehouse->manager_email)
                    ->notify(new TransferCreated($event->transfer));
            }
        }
        
        // Example: You could notify the destination when a transfer is dispatched
        if ($event->oldStatus === 'in_process' && $event->newStatus === 'dispatched') {
            // Notify destination
            if ($event->transfer->toWarehouse && $event->transfer->toWarehouse->manager_email) {
                Notification::route('mail', $event->transfer->toWarehouse->manager_email)
                    ->notify(new TransferCreated($event->transfer));
            } elseif ($event->transfer->toFacility && $event->transfer->toFacility->email) {
                Notification::route('mail', $event->transfer->toFacility->email)
                    ->notify(new TransferCreated($event->transfer));
            }
        }
    }
}
