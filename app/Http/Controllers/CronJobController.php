<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

class CronJobController extends Controller
{
    use ErrorUtil, UserActivityUtil;
    private function runArtisanCommand(string $command)
    {
        $output = new BufferedOutput();
        Artisan::call($command, [], $output);

        return response()->json([
            'message' => "Cron `$command` executed.",
            'output' => $output->fetch()
        ]);
    }

    // RUN RENT DUE DATE
    // public function runRentDue(Request $request)
    // {
    //     Artisan::call('rent:generate-due');
    //     return response()->json(['message' => 'cron-job run']);
    // }
    public function runRentDue(Request $request)
    {
        return $this->runArtisanCommand('rent:generate-due');
    }
}
