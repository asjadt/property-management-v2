<?php

namespace App\Mail;

use App\Models\EmailTemplate;
use App\Models\EmailTemplateWrapper;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Log\Logger;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ForgetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */



    private $user;
    private $client_site;





    public function __construct($user=null,$client_site = "")
    {

        $this->user = $user;

        $this->client_site = $client_site;



    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email_content = EmailTemplate::where([
            "type" => "forget_password_mail",
            "is_active" => 1

        ])->first();


        $html_content = $email_content->template;

        $html_content =  str_replace("[FirstName]", $this->user->first_Name, $html_content );
        $html_content =  str_replace("[LastName]", $this->user->last_Name, $html_content );
        $html_content =  str_replace("[FullName]", ($this->user->first_Name. " " .$this->user->last_Name), $html_content );
        $html_content =  str_replace("[APPURL]", (env("APP_URL")), $html_content );

        $html_content =  str_replace("[AccountVerificationLink]", (env('APP_URL').'/activate/'.$this->user->email_verify_token), $html_content);
        $business = $this->user->business()->first();
        $logo = $business->logo;
        if($logo) {
            $html_content =  str_replace("[LOGO]", "<img
            width='60'
            src='"
            .
            (env("APP_URL")."/".$logo)
            .


            "'
            title='logo'
            alt='logo'
          />", $html_content);
        }else {
            $html_content =  str_replace("[LOGO]",$business->name, $html_content);
        }



        if($this->client_site == "client") {
               $front_end_url = env('FRONT_END_URL_CLIENT');
        } else if($this->client_site == "dashboard") {
               $front_end_url = env('FRONT_END_URL_DASHBOARD');
        }





        $html_content =  str_replace("[ForgotPasswordLink]", ($front_end_url.'/auth/forgot-password?token='.$this->user->resetPasswordToken), $html_content );



        // $email_template_wrapper = EmailTemplateWrapper::where([
        //     "id" => $email_content->wrapper_id
        // ])
        // ->first();


        // $html_final = json_decode($email_template_wrapper->template);
        // $html_final =  str_replace("[content]", $html_content, $html_final);

        Log::info('This is an informational message.');


        return $this->view('email.dynamic_mail',["html_content"=>$html_content])
        ->subject('Password Reset Request')
        ->withSwiftMessage(function ($message) use ($business) {
            $message->getHeaders()
                ->addTextHeader('X-My-App-Name', $business->name);
        });;

    }
}
