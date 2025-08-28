<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AssetMaintenance;
use App\Models\Asset;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Notifications\AssetMaintenanceDue;

class GenerateMaintenanceSchedules extends Command
{
    protected $signature = 'assets:generate-maintenance-schedules';
    protected $description = 'Generate next maintenance schedules for recurring maintenance and send notifications';

    public function handle(): int
    {
        $this->info('Starting maintenance schedule generation...');
        
        $scheduled = 0;
        $notifications = 0;

        // Get all recurring maintenance records that need next scheduling
        $recurringMaintenance = AssetMaintenance::where('is_recurring', true)
            ->where('auto_schedule', true)
            ->where(function ($query) {
                $query->whereNull('next_scheduled_date')
                      ->orWhere('next_scheduled_date', '<=', now());
            })
            ->get();

        foreach ($recurringMaintenance as $maintenance) {
            // Calculate next scheduled date
            $lastDate = $maintenance->scheduled_date;
            if ($maintenance->next_scheduled_date) {
                $lastDate = $maintenance->next_scheduled_date;
            }

            $nextDate = Carbon::parse($lastDate)->addMonths($maintenance->recurrence_interval);
            
            // Update the next scheduled date
            $maintenance->update(['next_scheduled_date' => $nextDate]);
            
            // Create a new maintenance record for the next cycle
            $newMaintenance = AssetMaintenance::create([
                'asset_id' => $maintenance->asset_id,
                'maintenance_type' => $maintenance->maintenance_type,
                'description' => $maintenance->description,
                'scheduled_date' => $nextDate,
                'cost' => $maintenance->cost,
                'performed_by' => $maintenance->performed_by,
                'notes' => $maintenance->notes,
                'is_recurring' => true,
                'recurrence_interval' => $maintenance->recurrence_interval,
                'auto_schedule' => true,
                'notification_recipients' => $maintenance->notification_recipients,
                'reminder_days_before' => $maintenance->reminder_days_before,
                'status' => AssetMaintenance::STATUS_SCHEDULED,
                'metadata' => [
                    'created_by' => $maintenance->performed_by,
                    'created_at' => now()->toISOString(),
                    'parent_maintenance_id' => $maintenance->id,
                ],
            ]);

            $scheduled++;

            // Send notifications to recipients
            if (!empty($maintenance->notification_recipients)) {
                foreach ($maintenance->notification_recipients as $email) {
                    $this->sendMaintenanceNotification($email, $newMaintenance);
                    $notifications++;
                }
            }
        }

        // Send reminders for upcoming maintenance (1 month before)
        $this->sendMaintenanceReminders();

        $this->info("Scheduled {$scheduled} new maintenance records");
        $this->info("Sent {$notifications} notifications");
        
        return 0;
    }

    private function sendMaintenanceNotification($email, $maintenance)
    {
        try {
            Mail::raw("New maintenance scheduled for asset: {$maintenance->asset->asset_number}\n\n" .
                     "Type: {$maintenance->maintenance_type}\n" .
                     "Description: {$maintenance->description}\n" .
                     "Scheduled Date: {$maintenance->scheduled_date->format('Y-m-d')}\n" .
                     "Asset: {$maintenance->asset->asset_number}\n\n" .
                     "Please review and take necessary action.", function ($message) use ($email, $maintenance) {
                $message->to($email)
                        ->subject("New Maintenance Scheduled: {$maintenance->asset->asset_number}");
            });
        } catch (\Exception $e) {
            $this->error("Failed to send notification to {$email}: " . $e->getMessage());
        }
    }

    private function sendMaintenanceReminders()
    {
        $maintenanceDueSoon = AssetMaintenance::where('status', AssetMaintenance::STATUS_SCHEDULED)
            ->where('scheduled_date', '<=', now()->addDays(30))
            ->where('scheduled_date', '>', now())
            ->get();

        foreach ($maintenanceDueSoon as $maintenance) {
            if (!empty($maintenance->notification_recipients)) {
                foreach ($maintenance->notification_recipients as $email) {
                    $this->sendReminderEmail($email, $maintenance);
                }
            }
        }
    }

    private function sendReminderEmail($email, $maintenance)
    {
        try {
            $daysUntil = now()->diffInDays($maintenance->scheduled_date);
            
            Mail::raw("Maintenance Reminder for asset: {$maintenance->asset->asset_number}\n\n" .
                     "Type: {$maintenance->maintenance_type}\n" .
                     "Description: {$maintenance->description}\n" .
                     "Scheduled Date: {$maintenance->scheduled_date->format('Y-m-d')}\n" .
                     "Days Until Due: {$daysUntil}\n\n" .
                     "Please ensure maintenance is completed on time.", function ($message) use ($email, $maintenance) {
                $message->to($email)
                        ->subject("Maintenance Reminder: {$maintenance->asset->asset_number} - Due in {$daysUntil} days");
            });
        } catch (\Exception $e) {
            $this->error("Failed to send reminder to {$email}: " . $e->getMessage());
        }
    }
}

