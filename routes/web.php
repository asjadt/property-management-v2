<?php

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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/error-log', [SetUpController::class, "getErrorLogs"])->name("error-log");

Route::get('/activity-log', [SetUpController::class, "getActivityLogs"])->name("activity-log");


Route::get('/setup', [SetUpController::class, "setUp"])->name("setup");
Route::get('/setup2', [SetUpController::class, "setUp2"])->name("setup2");

Route::get('/backup', [SetUpController::class, "backup"])->name("backup");


Route::get('/roleRefresh', [SetUpController::class, "roleRefresh"])->name("roleRefresh");

Route::get('/swagger-refresh', [SetUpController::class, "swaggerRefresh"]);


Route::get('/automobile-refresh', [SetUpController::class, "automobileRefresh"]);

Route::get("/swagger-login",[SwaggerLoginController::class,"login"])->name("login.view");
Route::post("/swagger-login",[SwaggerLoginController::class,"passUser"]);

Route::get("/migrate",function(Request $request) {
    Artisan::call('migrate');
    return "migrated";
});
Route::get("/custom-command",function(Request $request) {
    Artisan::call('config:clear');
    return "done";
});





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


