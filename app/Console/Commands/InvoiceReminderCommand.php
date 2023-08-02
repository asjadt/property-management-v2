<?php

namespace App\Console\Commands;

use App\Mail\SendInvoiceReminderEmail;
use App\Models\Invoice;
use App\Models\InvoiceReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class InvoiceReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'due_reminder:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder for due invoices';

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
        Log::info('Task started.');
         $invoice_reminders = InvoiceReminder::whereDate(
            "reminder_date", today()
        )
        ->where([
            "send_reminder" => TRUE
        ])
        ->get()
        ;

        foreach($invoice_reminders as $invoice_reminder) {
            if($invoice_reminder->send_reminder == 1) {
                $recipients = [];
                if($invoice_reminder->invoice->tenant) {
                    array_push($recipients, $invoice_reminder->invoice->tenant->email);
                }
                if($invoice_reminder->invoice->landlord) {
                    array_push($recipients, $invoice_reminder->invoice->landlord->email);
                }

                Mail::to($recipients)
                ->send(new SendInvoiceReminderEmail($invoice_reminder->invoice));
                $invoice_reminder->reminder_status = "sent";
                $invoice_reminder->save();

            }


        }




        Invoice::whereNotIn("status",[
         "draft","overdue"
        ])
        ->whereDate('due_date', '<=', today())
        ->update([
            "status" => "overdue"
        ]);



        Log::info('Task executed.');
    }
}
