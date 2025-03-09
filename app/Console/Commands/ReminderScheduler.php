<?php

namespace App\Console\Commands;

use App\Http\Utils\BasicUtil;
use App\Mail\DocumentExpiryReminderMail;
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

    public function handle()
    {
        Log::info('Reminder process started.');

        $businesses = Reminder::groupBy('created_by')->select('created_by')->get();

        foreach ($businesses as $business) {
            $business = Business::where(["owner_id" => $business->created_by, "is_active" => 1])->first();
            if (!$business) continue;

            $reminders = Reminder::where("created_by", $business->created_by)->get();

            foreach ($reminders as $reminder) {

                // Adjust reminder duration if necessary
                if ($reminder->duration_unit == "weeks") {
                    $reminder->duration = $reminder->duration * 7;
                } else if ($reminder->duration_unit == "months") {
                    $reminder->duration = $reminder->duration * 30;
                }

                if ($reminder->entity_name == "document_expiry_reminder") {

                    $property = Property::where('created_by', $business->created_by)
                        ->where("id", $reminder->property_id)
                        ->whereHas('latest_documents', function ($query) use ($reminder) {
                            if ($reminder->send_time == 'before_expiry') {
                                $query->whereDate("gas_end_date", '<=', now()->addDays($reminder->duration));
                            } else {
                                $query->whereDate("gas_end_date", '<=', now()->subDays($reminder->duration));
                            }
                        })
                        ->first();

                    $latest_documents = $property->latest_documents()->when($reminder->send_time == 'before_expiry', function ($query) use ($reminder) {
                        $query->whereDate("gas_end_date", '<=', now()->addDays($reminder->duration));
                    }, function ($query) use ($reminder) {
                        $query->whereDate("gas_end_date", '<=', now()->subDays($reminder->duration));
                    })
                        ->get();


                    foreach ($latest_documents as $document) {
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
                            } elseif ($reminder_date->gt($document->gas_end_date)) {
                                // Handle frequency and reminder limit logic after expiry
                                $this->handleReminderFrequency($reminder, $document, $reminder_date, $business);
                            }
                        } elseif ($reminder->send_time == "before_expiry") {
                            // Check if reminder should be sent before expiry
                            if ($reminder_date->eq($now)) {
                                // send reminder
                                $this->sendDocumentExpiryReminder($reminder, $document, $business);
                            } elseif ($reminder_date->lt($now)) {
                                // Handle frequency and reminder limit logic before expiry
                                $this->handleReminderFrequency($reminder, $document, $reminder_date, $business);
                            }
                        }
                    }
                } else if($reminder->entity_name == "maintainance_expiry_reminder") {

                }




            }
        }



        Log::info('Reminder process finished.');
    }

    private function handleReminderFrequency($reminder, $document, $reminder_date, $business)
    {
        if (!empty($reminder->frequency_after_first_reminder)) {
            // Calculate the difference in days between reminder date and current date
            $days_difference = $reminder_date->diffInDays(now());

            // Check if the frequency condition is met
            $is_frequency_met = ($days_difference % $reminder->frequency_after_first_reminder) == 0;

            if ($reminder->keep_sending_until_update) {
                // If "keep sending until update" is true, send reminder based on frequency
                if ($is_frequency_met) {
                    $this->sendDocumentExpiryReminder($reminder, $document, $business);
                }
            } else {
                // If there's a reminder limit, ensure we don't exceed it
                if ($is_frequency_met && (($days_difference / $reminder->frequency_after_first_reminder) <= $reminder->reminder_limit)) {
                    $this->sendDocumentExpiryReminder($reminder, $document, $business);
                }
            }
        }
    }

    private function sendDocumentExpiryReminder($reminder, $document, $business)
    {
        $days_difference = now()->diffInDays($document->gas_end_date);

        $message = $reminder->send_time == "after_expiry"
            ? "The document for your property expired {$days_difference} days ago. Please renew it now."
            : "The document for your property will expire in {$days_difference} days. Please renew it in time.";

        // Fetch property details
        $property = $document->property;

        // Send email
        Mail::to($business->email)->send(new DocumentExpiryReminderMail($reminder->title, $message, $document, $property, $business));
    }
}
