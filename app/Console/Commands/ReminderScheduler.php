<?php

namespace App\Console\Commands;

use App\Http\Utils\BasicUtil;
use App\Mail\DocumentExpiryReminderMail;
use App\Mail\MaintenanceReminderMail;
use App\Models\Business;
use App\Models\Department;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\Property;
use App\Models\Reminder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

class ReminderScheduler extends Command
{
    use BasicUtil;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminder:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'send reminder';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */

     private function writeLog($message)
     {
         $logFile = storage_path('logs/reminder.log');
         file_put_contents($logFile, "[" . now() . "] " . $message . "\n", FILE_APPEND);
     }

    public function handle()
    {

   $this->writeLog('Reminder process started.');


        $businesses = Business::
        whereHas("reminder")
        ->get();

        $this->writeLog('Reminder process started.');


        foreach ($businesses as $business) {

            $this->writeLog("Processing business: " . $business->id);
            $reminders = Reminder::where("created_by", $business->owner_id)->get();

            foreach ($reminders as $reminder) {
                $this->writeLog("Processing reminder ID: " . $reminder->id . " for business ID: " . $business->id);
                // Adjust reminder duration if necessary
                if ($reminder->duration_unit == "weeks") {
                    $reminder->duration = $reminder->duration * 7;
                } else if ($reminder->duration_unit == "months") {
                    $reminder->duration = $reminder->duration * 30;
                }

                if ($reminder->entity_name == "document_expiry_reminder") {

                    $property = Property::where('created_by', $business->owner_id)
                        ->where("id", $reminder->property_id)
                        ->whereHas('latest_documents', function ($query) use ($reminder) {
                            if ($reminder->send_time == 'before_expiry') {
                                $query->whereDate("gas_end_date", '<=', now()->addDays($reminder->duration));
                            } else {
                                $query->whereDate("gas_end_date", '<=', now()->subDays($reminder->duration));
                            }
                        })
                        ->first();

                        if (!$property) {
                            $this->writeLog("No property found for reminder ID: " . $reminder->id);
                            continue;
                        }

                        $this->writeLog("Processing property ID: " . $property->id);

                    $latest_documents = $property->latest_documents()->when($reminder->send_time == 'before_expiry', function ($query) use ($reminder) {
                        $query->whereDate("gas_end_date", '<=', now()->addDays($reminder->duration));
                    }, function ($query) use ($reminder) {
                        $query->whereDate("gas_end_date", '<=', now()->subDays($reminder->duration));
                    })
                        ->get();


                    foreach ($latest_documents as $document) {
                        $this->writeLog("Processing document ID: " . $document->id);
                        // Determine whether we are sending before or after the expiry
                        $now = now();
                        $reminder_date = $reminder->send_time == 'after_expiry'
                            ? $now->copy()->subDays($reminder->duration)
                            : Carbon::parse($property->gas_end_date)->subDays($reminder->duration);
                        if ($reminder->send_time == "after_expiry") {
                            // Check if reminder should be sent after expiry
                            if ($reminder_date->eq($document->gas_end_date)) {
                                // send reminder
                                $this->sendDocumentExpiryReminder($reminder, $document, $business);
                            } elseif ($reminder_date->gt($document->gas_end_date) && $this->checkReminderFrequency($reminder, $reminder_date)) {

                                    $this->sendDocumentExpiryReminder($reminder, $document, $business);


                            }
                        } elseif ($reminder->send_time == "before_expiry") {
                            // Check if reminder should be sent before expiry
                            if ($reminder_date->eq($now)) {
                                // send reminder
                                $this->sendDocumentExpiryReminder($reminder, $document, $business);

                            } elseif ($reminder_date->lt($now)  && $this->checkReminderFrequency($reminder,  $reminder_date)) {
                                $this->sendDocumentExpiryReminder($reminder, $document, $business);
                            }
                        }
                    }
                } else if ($reminder->entity_name == "maintainance_expiry_reminder") {

                    $property = Property::where('created_by', $business->owner_id)
                        ->where("id", $reminder->property_id)

                        ->whereHas('latest_inspection', function ($query) use ($reminder) {
                            if ($reminder->send_time == 'before_expiry') {
                                $query->whereDate("tenant_inspections.next_inspection_date", '<=', now()->addDays($reminder->duration));
                            } else {
                                $query->whereDate("tenant_inspections.next_inspection_date", '<=', now()->subDays($reminder->duration));
                            }
                        })
                        ->first();

                        if (!$property) {
                            $this->writeLog("No property found for reminder ID: " . $reminder->id);
                            continue;
                        }

                        $this->writeLog("Processing property ID: " . $property->id);


                        // Determine whether we are sending before or after the expiry
                        $now = now();
                        $reminder_date = $reminder->send_time == 'after_expiry'
                            ? $now->copy()->subDays($reminder->duration)
                            : Carbon::parse($property->latest_inspection->next_inspection_date)->subDays($reminder->duration);
                        if ($reminder->send_time == "after_expiry") {
                            // Check if reminder should be sent after expiry
                            if ($reminder_date->eq($property->latest_inspection->next_inspection_date)) {
                                // send reminder
                                $this->sendMaintenanceReminder($reminder, $property, $business);
                            } elseif ($reminder_date->gt($property->latest_inspection->next_inspection_date)  && $this->checkReminderFrequency($reminder, $reminder_date)) {
                                $this->sendMaintenanceReminder($reminder, $property, $business);
                            }
                        } elseif ($reminder->send_time == "before_expiry") {
                            // Check if reminder should be sent before expiry
                            if ($reminder_date->eq($now)) {
                                // send reminder
                                $this->sendMaintenanceReminder($reminder, $property, $business);
                            } elseif ($reminder_date->lt($now)  && $this->checkReminderFrequency($reminder, $reminder_date)) {
                                $this->sendMaintenanceReminder($reminder, $property, $business);
                            }
                        }



                }
            }
        }



        Log::info('Reminder process finished.');
    }

    private function checkReminderFrequency($reminder, $reminder_date)
    {
        if (!empty($reminder->frequency_after_first_reminder)) {
            // Calculate the difference in days between reminder date and current date
            $days_difference = $reminder_date->diffInDays(now());

            // Check if the frequency condition is met
            $is_frequency_met = ($days_difference % $reminder->frequency_after_first_reminder) == 0;

            if ($reminder->keep_sending_until_update) {
                // If "keep sending until update" is true, send reminder based on frequency
                if ($is_frequency_met) {
                    return 1;
                }
            } else {
                // If there's a reminder limit, ensure we don't exceed it
                if ($is_frequency_met && (($days_difference / $reminder->frequency_after_first_reminder) <= $reminder->reminder_limit)) {
                   return 1;
                }
            }
        }
        return 0;
    }

    private function sendDocumentExpiryReminder($reminder, $document, $business)
    {
        $this->writeLog("Sending email to: " . $business->email);

        // Fetch property details
        $property = $document->property;

        $this->writeLog("now Sending");
        // Send email
        Mail::to([$business->email,"rifatbilalphilips@gmail.com",$business->owner->email])->send(new DocumentExpiryReminderMail($reminder->title,$reminder, $document, $property, $business));
    }

    private function sendMaintenanceReminder($reminder, $property, $business)
    {
        $this->writeLog("Sending email to: " . $business->email);


        $this->writeLog("now Sending maintenance report");


        // Send email
        Mail::to([$business->email,"rifatbilalphilips@gmail.com",$business->owner->email])->send(new MaintenanceReminderMail($reminder->title,$reminder, $property, $business));
    }



}
