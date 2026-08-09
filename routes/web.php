<?php

use App\Http\Controllers\DevAccessController;
use App\Http\Controllers\SetUpController;
use App\Http\Controllers\SwaggerLoginController;
use App\Mail\SendInvoiceReminderEmail;
use App\Models\EmailTemplate;
use App\Models\EmailTemplateWrapper;
use App\Models\InvoiceReminder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Developer Authentication Routes (Public)
Route::get('/dev/login', [DevAccessController::class, 'showLoginForm'])->name('dev.login');
Route::post('/dev/verify-password', [DevAccessController::class, 'verifyPassword'])->name('dev.verify_password');
Route::post('/dev/send-otp', [DevAccessController::class, 'sendOtp'])->name('dev.send_otp');
Route::post('/dev/verify-otp', [DevAccessController::class, 'verifyOtp'])->name('dev.verify_otp');
Route::get('/dev/clear-otp', [DevAccessController::class, 'clearOtp'])->name('dev.clear_otp');
Route::get('/dev/clear-password', [DevAccessController::class, 'clearPassword'])->name('dev.clear_password');

// Protected Developer Routes
Route::middleware([\App\Http\Middleware\DevAccessMiddleware::class])->group(function () {

    Route::post('/dev/logout', [DevAccessController::class, 'logout'])->name('dev.logout');

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/error-log', [SetUpController::class, "getErrorLogs"])->name("error-log");
    Route::get('/activity-log', [SetUpController::class, "getActivityLogs"])->name("activity-log");

    Route::get('/setup', [SetUpController::class, "setUp"])->name("setup");
    Route::get('/setup2', [SetUpController::class, "setUp2"])->name("setup2");

    Route::get('/backup', [SetUpController::class, "backup"])->name("backup");

    Route::get('/v1/db-operation', [SetUpController::class, "dbOperation1"]);

    Route::get('/roleRefresh', [SetUpController::class, "roleRefresh"])->name("roleRefresh");
    Route::get('/swagger-refresh', [SetUpController::class, "swaggerRefresh"]);
    Route::get('/automobile-refresh', [SetUpController::class, "automobileRefresh"]);
    Route::get('/property-type-option-refresh', [SetUpController::class, "propertyTypeOptionRefresh"]);

    Route::get("/swagger-login",[SwaggerLoginController::class,"login"])->name("login.view");
    Route::post("/swagger-login",[SwaggerLoginController::class,"passUser"]);

    Route::get('/migrate', [SetUpController::class, "migrate"]);
    Route::get('/migrate-activity', [SetUpController::class, "migrateActivity"]);
    Route::get('/optimize', function() {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return "Cache cleared successfully!";
    });

    Route::get('/passport-install',[SetUpController::class,"setupPassport"]);

    Route::get('/seed', [SetUpController::class, "seed"]);

    // =======================================================================
    // PRODUCTION SYNC
    // Convention: any production data changes must be documented here
    // with a date comment so we can track what ran and when.
    // =======================================================================

    // -----------------------------------------------------------------------
    // [2026-08-09] backfill users.business_id
    // Added business_id column to users table. This query links every
    // existing user to their business (owners via owner_id, sub-users via
    // created_by). Safe to re-run — uses WHERE business_id IS NULL.
    // -----------------------------------------------------------------------
    Route::get('/production-sync', function () {
        $log = [];

        // STEP 2A: Business owners — link user to the business they own
        $affected_a = \Illuminate\Support\Facades\DB::statement("
            UPDATE users
            INNER JOIN businesses ON businesses.owner_id = users.id
            SET users.business_id = businesses.id
            WHERE users.business_id IS NULL
              AND businesses.deleted_at IS NULL
        ");
        $log[] = 'Step 2A (owner backfill): done';

        // STEP 2B: Sub-users — inherit business from the user who created them
        $affected_b = \Illuminate\Support\Facades\DB::statement("
            UPDATE users
            INNER JOIN businesses ON businesses.owner_id = users.created_by
            SET users.business_id = businesses.id
            WHERE users.business_id IS NULL
              AND businesses.deleted_at IS NULL
        ");
        $log[] = 'Step 2B (sub-user backfill): done';

        // REPORT
        $total    = \Illuminate\Support\Facades\DB::table('users')->count();
        $with_biz = \Illuminate\Support\Facades\DB::table('users')->whereNotNull('business_id')->count();
        $without  = \Illuminate\Support\Facades\DB::table('users')->whereNull('business_id')->count();

        $log[] = "Total users: {$total}";
        $log[] = "With business_id: {$with_biz}";
        $log[] = "Without business_id (superadmin/orphan): {$without}";

        return response()->json([
            'success' => true,
            'message' => 'Production sync completed.',
            'log'     => $log,
        ]);
    })->name('production-sync');


    Route::get("/custom-command",function(Request $request) {
        Artisan::call('reminder:send');
        return "done";
    });

    Route::get("/test",function() {
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
           $recipients = ["drrifatalashwad0@gmail.com"];
           return response()->json($invoice_reminder->invoice);
           if($invoice_reminder->invoice->tenant) {
               array_push($recipients, $invoice_reminder->invoice->tenant->email);
           }
           if($invoice_reminder->invoice->landlord) {
               array_push($recipients, $invoice_reminder->invoice->landlord->email);
           }

           Mail::to($recipients)
           ->send(new SendInvoiceReminderEmail($invoice_reminder->invoice));
       }

              Log::info('Task executed.');

    });
});

// Public User Activation Route
Route::get("/activate/{token}",function(Request $request,$token) {
    $user = User::where([
        "email_verify_token" => $token,
    ])
        ->where("email_verify_token_expires", ">", now())
        ->first();
    if (!$user) {
        return response()->json([
            "message" => "Invalid Url Or Url Expired"
        ], 400);
    }

    $user->email_verified_at = now();
    $user->save();


    $email_content = EmailTemplate::where([
        "type" => "welcome_message",
        "is_active" => 1

    ])->first();


    $html_content = json_decode($email_content->template);
    $html_content =  str_replace("[FirstName]", $user->first_Name, $html_content );
    $html_content =  str_replace("[LastName]", $user->last_Name, $html_content );
    $html_content =  str_replace("[FullName]", ($user->first_Name. " " .$user->last_Name), $html_content );
    $html_content =  str_replace("[AccountVerificationLink]", (env('APP_URL').'/activate/'.$user->email_verify_token), $html_content);
    $html_content =  str_replace("[ForgotPasswordLink]", (env('FRONT_END_URL').'/fotget-password/'.$user->resetPasswordToken), $html_content );



    $email_template_wrapper = EmailTemplateWrapper::where([
        "id" => $email_content->wrapper_id
    ])
    ->first();


    $html_final = json_decode($email_template_wrapper->template);
    $html_final =  str_replace("[content]", $html_content, $html_final);


    return view("dynamic-welcome-message",["html_content" => $html_final]);
});
