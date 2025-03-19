<?php

namespace App\Console\Commands;

use App\Mail\DocumentAlertMail;
use App\Mail\OverallMaintainanceAlertMail;
use App\Models\Business;
use App\Models\DocumentType;
use App\Models\Property;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class PropertyAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'property_alerts:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To Send property Alert';

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

      $businesses = Business::where("type", "property_dealer")
      ->where("send_email_alert",1)
      ->get();

        foreach($businesses as $business) {

            $document_report = $this->getDocumentReport($business->owner_id);
            $overall_maintainance_report = $this->getOverallMaintainanceReport($business->owner_id);
    // Send document alert email
    Mail::to($business->owner->email)->send(new DocumentAlertMail($document_report));

    // Send maintenance alert email
    Mail::to($business->owner->email)->send(new OverallMaintainanceAlertMail($overall_maintainance_report));

        }

        return 0;
    }

    public function getDocumentReport($created_by)
    {

       $document_types = DocumentType::where([
           "created_by" => $created_by
       ])->get();

       $document_report = [];
       foreach ($document_types as $document_type) {
           $base_documents_query = Property::where("properties.created_by", $created_by)

           ->whereHas("latest_documents", function ($subQuery) use($document_type) {

           $subQuery->where("property_documents.document_type_id", $document_type->id);

               });

           // Count documents for different expiration periods
           $document_report[$document_type->name] = [
               'total_data' => (clone $base_documents_query)->count(),
               'total_expired' => (clone $base_documents_query)
               ->whereHas("latest_documents", function ($subQuery) {
                   $subQuery->whereDate('property_documents.gas_end_date', "<", Carbon::today());
               })
               ->count(),

               'today_expiry' => (clone $base_documents_query)

               ->whereHas("latest_documents", function ($subQuery)  {
                   $subQuery->whereDate('property_documents.gas_end_date', Carbon::today());
               })

               ->count(),

               'expires_in_15_days' => (clone $base_documents_query)
               ->whereHas("latest_documents", function ($subQuery)  {
                   $subQuery->whereDate('property_documents.gas_end_date', ">", Carbon::today())
                   ->whereDate('property_documents.gas_end_date', "<=", Carbon::today()->addDays(15));
               })
                   ->count(),

               'expires_in_30_days' => (clone $base_documents_query)

               ->whereHas("latest_documents", function ($subQuery)  {
                   $subQuery ->whereDate('property_documents.gas_end_date', ">", Carbon::today())
                   ->whereDate('property_documents.gas_end_date', "<=", Carbon::today()->addDays(30));
               })
                   ->count(),

           ];
       }

       return $document_report;

    }

    public function getOverallMaintainanceReport($created_by)
    {


            $base_maintance_query = Property::
            where("properties.created_by", $created_by);

            // Count documents for different expiration periods
            $maintainance_report = [

                'total_data' => (clone $base_maintance_query)->count(),
                'total_expired' => (clone $base_maintance_query)
                ->whereHas("latest_inspection", function ($subQuery) {
                   $subQuery->whereDate('tenant_inspections.next_inspection_date', '<', Carbon::today());
               })

                ->count(),
                'today_expiry' => (clone $base_maintance_query)
                ->whereHas("latest_inspection", function ($subQuery) {
                   $subQuery->whereDate('tenant_inspections.next_inspection_date', Carbon::today());
               })
              ->count(),

               'expires_in_15_days' => (clone $base_maintance_query)
               ->whereHas("latest_inspection", function ($subQuery) {
                   $subQuery->whereDate('tenant_inspections.next_inspection_date', ">", Carbon::today())
                   ->whereDate('tenant_inspections.next_inspection_date', "<=", Carbon::today()->addDays(15));
               })

                    ->count(),

               'expires_in_30_days' => (clone $base_maintance_query)
               ->whereHas("latest_inspection", function ($subQuery) {
                   $subQuery->whereDate('tenant_inspections.next_inspection_date', ">", Carbon::today())
                   ->whereDate('tenant_inspections.next_inspection_date', "<=", Carbon::today()->addDays(30));
               })

                    ->count(),





            ];

        return $maintainance_report;

    }

}
