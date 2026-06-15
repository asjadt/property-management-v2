<?php

namespace App\Http\Middleware;

use App\Models\ErrorLog;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Session;

class ResponseMiddleware
{


    public function handle($request, Closure $next)
    {

     // Define your API project's base URL
     $apiBaseUrl = config('app.url'); // This gets the base URL from the app configuration




        $response = $next($request);



        if ($response->headers->get('content-type') === 'application/json') {
            Session::flush();
            $content = $response->getContent();
            $convertedContent = $this->convertDatesInJson($content);
            $response->setContent($convertedContent);


            if ((($response->getStatusCode() >= 500 && $response->getStatusCode() < 600))) {
                $errorLog = [
                    "api_url" => $request->fullUrl(),
                    "fields" => json_encode(request()->all()),
                    "token" => request()->bearerToken()?request()->bearerToken():"",
                    "user" => auth()->user() ? json_encode(auth()->user()) : "",
                    "user_id" => auth()->user() ?auth()->user()->id:"",
                    "status_code" => $response->getStatusCode(),
                    // "ip_address" => request()->header('X-Forwarded-For'),
                    "ip_address" => request()->ip(),

                    "request_method" => $request->method(),
                    "message" =>  $response->getContent(),
                ];

                  $error =   ErrorLog::create($errorLog);
                    // $errorMessage = "Error ID: ".$error->id." - Status: ".$error->status_code." - Operation Failed, something is wrong! - Please call to the customer care.";
                    $errorMessage = "We encountered an issue while processing your request and apologize for any inconvenience this may have caused. Please contact customer support and provide the Error ID: " . $error->id . " for assistance.";
                    $response->setContent(json_encode(['message' => $errorMessage]));

            }
            else if(($response->getStatusCode() >= 300 && $response->getStatusCode() < 500)) {
                $errorLog = [
                    "api_url" => $request->fullUrl(),
                    "fields" => json_encode(request()->all()),
                    "token" => request()->bearerToken()?request()->bearerToken():"",
                    "user" => auth()->user() ? json_encode(auth()->user()) : "",
                    "user_id" => auth()->user() ?auth()->user()->id:"",
                    "status_code" => $response->getStatusCode(),
                    "ip_address" => request()->ip(),
                    "request_method" => $request->method(),
                    "message" =>  $response->getContent(),
                ];

                  $error =   ErrorLog::create($errorLog);

                  $responseData = json_decode($response->getContent(), true);
                  if (isset($responseData['message'])) {
                      $responseData['message'] = "Error ID: ".$error->id." - Status: ".$error->status_code." -  ". $responseData['message'];
                  }
                  $response->setContent(json_encode($responseData));

            }

        }

        return $response;
    }

 private function convertDatesInJson($json)
{
    $data = json_decode($json, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
        array_walk_recursive($data, function (&$value, $key) {
            if (!is_string($value)) {
                return;
            }

            // Detect format
            if (Carbon::hasFormat($value, 'Y-m-d H:i:s')) {
                // Input has full datetime, keep it
                $value = Carbon::parse($value)->format('d-m-Y H:i:s');
            } elseif (Carbon::hasFormat($value, 'Y-m-d')) {
                // Input is date only
                $value = Carbon::parse($value)->format('d-m-Y');
            } elseif (Carbon::hasFormat($value, 'Y-m-d\TH:i:s.u\Z') || Carbon::hasFormat($value, 'Y-m-d\TH:i:s')) {
                // Input is ISO datetime
                $value = Carbon::parse($value)->format('d-m-Y H:i:s');
            }
            // Otherwise, leave the value as-is
        });

        return json_encode($data);
    }

    return $json;
}

}
