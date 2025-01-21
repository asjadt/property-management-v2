<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\Property;
use Illuminate\Console\Command;

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

            $properties = Property::where("created_by",$business->owner_id)
            ->get();

            foreach($properties as $property) {
                

            }


        }

        return 0;
    }
}
