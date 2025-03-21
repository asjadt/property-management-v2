<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRegenerateTokenRequest;
use App\Http\Requests\AuthRegisterGarageRequest;
use App\Http\Requests\AuthRegisterRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\EmailVerifyTokenRequest;
use App\Http\Requests\ForgetPasswordRequest;
use App\Http\Requests\PasswordChangeRequest;
use App\Http\Requests\UserInfoUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\GarageUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\ForgetPasswordMail;
use App\Mail\VerifyMail;
use App\Models\AutomobileCategory;
use App\Models\AutomobileMake;
use App\Models\AutomobileModel;
use App\Models\Garage;
use App\Models\GarageAutomobileMake;
use App\Models\GarageAutomobileModel;
use App\Models\GarageGallery;
use App\Models\GarageService;
use App\Models\GarageSubService;
use App\Models\Service;
use App\Models\SubService;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    use ErrorUtil, GarageUtil, UserActivityUtil;
    /**
     *
     * @OA\Post(
     *      path="/v1.0/register",
     *      operationId="register",
     *      tags={"auth"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store user",
     *      description="This method is to store user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"first_Name","last_Name","email","password","password_confirmation","phone","address_line_1","address_line_2","country","city","postcode"},
     *             @OA\Property(property="first_Name", type="string", format="string",example="Rifat"),
     *            @OA\Property(property="last_Name", type="string", format="string",example="Al"),
     *            @OA\Property(property="email", type="string", format="string",example="rifat@g.c"),

     * *  @OA\Property(property="password", type="string", format="string",example="12345678"),
     *  * *  @OA\Property(property="password_confirmation", type="string", format="string",example="12345678"),
     *  * *  @OA\Property(property="phone", type="string", format="string",example="01771034383"),
     *  * *  @OA\Property(property="address_line_1", type="string", format="string",example="dhaka"),
     *  * *  @OA\Property(property="address_line_2", type="string", format="string",example="dinajpur"),
     *  * *  @OA\Property(property="country", type="string", format="string",example="bangladesh"),
     *  * *  @OA\Property(property="city", type="string", format="string",example="dhaka"),
     *  * *  @OA\Property(property="postcode", type="string", format="string",example="1207"),
     *      *  * *  @OA\Property(property="lat", type="string", format="string",example="1207"),
     *      *  * *  @OA\Property(property="long", type="string", format="string",example="1207"),
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function register(AuthRegisterRequest $request)
    {
        try {
            $this->storeActivity($request,"");
            $request_data = $request->validated();

            $request_data['password'] = Hash::make($request['password']);
            $request_data['remember_token'] = Str::random(10);
            $user =  User::create($request_data);

              // verify email starts
              $email_token = Str::random(30);
              $user->email_verify_token = $email_token;
              $user->email_verify_token_expires = Carbon::now()->subDays(-1);
              $user->save();


             $user->assignRole("customer");

            $user->token = $user->createToken('Laravel Password Grant Client')->accessToken;
            $user->permissions = $user->getAllPermissions()->pluck('name');
            $user->roles = $user->roles->pluck('name');


            if(env("SEND_EMAIL") == true) {
                Mail::to($user->email)->send(new VerifyMail($user));
            }

// verify email ends

            return response($user, 201);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }




    /**
     *
     * @OA\Post(
     *      path="/v1.0/login",
     *      operationId="login",
     *      tags={"auth"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to login user",
     *      description="This method is to login user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"email","password"},
     *            @OA\Property(property="email", type="string", format="string",example="asjadtariq@gmail.com"),

     * *  @OA\Property(property="password", type="string", format="string",example="12345678@We"),
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */
    public function login(Request $request)
    {


        try {
            $this->storeActivity($request,"");
            $loginData = $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);
            $user = User::where('email', $loginData['email'])->first();

            if ($user && $user->login_attempts >= 5) {
                $now = Carbon::now();
                $lastFailedAttempt = Carbon::parse($user->last_failed_login_attempt_at);
                $diffInMinutes = $now->diffInMinutes($lastFailedAttempt);

                if ($diffInMinutes < 15) {
                    return response(['message' => 'You have 5 failed attempts. Reset your password or wait for 15 minutes to access your account.'], 403);
                } else {
                    $user->login_attempts = 0;
                    $user->last_failed_login_attempt_at = null;
                    $user->save();
                }
            }


            if (!auth()->attempt($loginData)) {
                if ($user) {
                    $user->login_attempts++;
                    $user->last_failed_login_attempt_at = Carbon::now();
                    $user->save();

                    if ($user->login_attempts >= 5) {
                        $now = Carbon::now();
                        $lastFailedAttempt = Carbon::parse($user->last_failed_login_attempt_at);
                        $diffInMinutes = $now->diffInMinutes($lastFailedAttempt);

                        if ($diffInMinutes < 15) {
                            return response(['message' => 'You have 5 failed attempts. Reset your password or wait for 15 minutes to access your account.'], 403);
                        } else {
                            $user->login_attempts = 0;
                            $user->last_failed_login_attempt_at = null;
                            $user->save();
                        }
                    }
                }

                return response(['message' => 'Invalid Credentials'], 401);
            }

            $user = auth()->user();
            if(!$user->is_active) {
                return response(['message' => 'User not active'], 403);

            }
            $now = time(); // or your date as well
$user_created_date = strtotime($user->created_at);
$datediff = $now - $user_created_date;

            if(!$user->email_verified_at && (($datediff / (60 * 60 * 24))>1)){
                return response(['message' => 'please activate your email first'], 409);
            }


            $user->login_attempts = 0;
            $user->last_failed_login_attempt_at = null;


            $site_redirect_token = Str::random(30);
            $site_redirect_token_data["created_at"] = $now;
            $site_redirect_token_data["token"] = $site_redirect_token;
            $user->site_redirect_token = json_encode($site_redirect_token_data);
            $user->save();

            $user->redirect_token = $site_redirect_token;

            $user->token = auth()->user()->createToken('authToken')->accessToken;
            $user->permissions = $user->getAllPermissions()->pluck('name');
            $user->roles = $user->roles->pluck('name');

            $user->business =  $user->business;


            return response()->json($user, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }

  /**
     *
     * @OA\Post(
     *      path="/v1.0/token-regenerate",
     *      operationId="regenerateToken",
     *      tags={"auth"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to regenerate Token",
     *      description="This method is to regenerate Token",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user_id","site_redirect_token"},
     *            @OA\Property(property="user_id", type="number", format="number",example="1"),

     * *  @OA\Property(property="site_redirect_token", type="string", format="string",example="12345678"),
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */
    public function regenerateToken(AuthRegenerateTokenRequest $request)
    {

        try {
            $this->storeActivity($request,"");
            $request_data = $request->validated();
            $user = User::where([
                "id" => $request_data["user_id"],
            ])
            ->first();



            $site_redirect_token_db = (json_decode($user->site_redirect_token,true));

            if($site_redirect_token_db["token"] !== $request_data["site_redirect_token"]) {
               return response()
               ->json([
                  "message" => "invalid token"
               ],409);
            }

            $now = time(); // or your date as well

            $timediff = $now - $site_redirect_token_db["created_at"];

            if ($timediff > 20){
                return response(['message' => 'token expired'], 409);
            }



            $user->tokens()->delete();
            $user->token = $user->createToken('authToken')->accessToken;
            $user->permissions = $user->getAllPermissions()->pluck('name');
            $user->roles = $user->roles->pluck('name');
            $user->a = ($timediff);




            return response()->json(['data' => $user,   "ok" => true], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }
   /**
        *
     * @OA\Post(
     *      path="/forgetpassword",
     *      operationId="storeToken",
     *      tags={"auth"},

     *      summary="This method is to store token",
     *      description="This method is to store token",

     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"email"},
     *
     *             @OA\Property(property="email", type="string", format="string",* example="test@g.c"),
     *    *             @OA\Property(property="client_site", type="string", format="string",* example="client"),
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function storeToken(ForgetPasswordRequest $request) {

        try {
            $this->storeActivity($request,"");
            return DB::transaction(function () use (&$request) {
                $request_data = $request->validated();

            $user = User::where(["email" => $request_data["email"]])->first();
            if (!$user) {
                return response()->json(["message" => "no user found"], 404);
            }

            $token = Str::random(30);

            $user->resetPasswordToken = $token;
            $user->resetPasswordExpires = Carbon::now()->subDays(-1);
            $user->save();

              $result = Mail::to($request_data["email"])->send(new ForgetPasswordMail($user, $request_data["client_site"]));

            if (count(Mail::failures()) > 0) {
                // Handle failed recipients and log the error messages
                foreach (Mail::failures() as $emailFailure) {

                }
                throw new Exception("Failed to send email to:" . $emailFailure);
            }



            return response()->json([
                "message" => "please check email"
            ]);
            });




        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }

    }

      /**
        *
     * @OA\Post(
     *      path="/resend-email-verify-mail",
     *      operationId="resendEmailVerifyToken",
     *      tags={"auth"},

     *      summary="This method is to resend email verify mail",
     *      description="This method is to resend email verify mail",

     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"email"},
     *
     *             @OA\Property(property="email", type="string", format="string",* example="test@g.c"),
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */

    public function resendEmailVerifyToken(EmailVerifyTokenRequest $request) {

        try {
            $this->storeActivity($request,"");
            return DB::transaction(function () use (&$request) {
                $request_data = $request->validated();

            $user = User::where(["email" => $request_data["email"]])->first();
            if (!$user) {
                return response()->json(["message" => "no user found"], 404);
            }



            $email_token = Str::random(30);
            $user->email_verify_token = $email_token;
            $user->email_verify_token_expires = Carbon::now()->subDays(-1);
            if(env("SEND_EMAIL") == true) {
                Mail::to($user->email)->send(new VerifyMail($user));
            }

            $user->save();


            return response()->json([
                "message" => "please check email"
            ]);
            });




        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }

    }


/**
        *
     * @OA\Patch(
     *      path="/forgetpassword/reset/{token}",
     *      operationId="changePasswordByToken",
     *      tags={"auth"},
     *  @OA\Parameter(
* name="token",
* in="path",
* description="token",
* required=true,
* example="1"
* ),
     *      summary="This method is to change password",
     *      description="This method is to change password",

     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"password"},
     *
     *     @OA\Property(property="password", type="string", format="string",* example="aaaaaaaa"),

     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */





    public function changePasswordByToken($token, ChangePasswordRequest $request)
    {
        try {
            $this->storeActivity($request,"");
            return DB::transaction(function () use (&$request,&$token) {
                $request_data = $request->validated();
                $user = User::where([
                    "resetPasswordToken" => $token,
                ])
                    ->where("resetPasswordExpires", ">", now())
                    ->first();
                if (!$user) {
                    return response()->json([
                        "message" => "Invalid Token Or Token Expired"
                    ], 400);
                }

                $password = Hash::make($request_data["password"]);
                $user->password = $password;

                $user->login_attempts = 0;
                $user->last_failed_login_attempt_at = null;


                $user->save();


                return response()->json([
                    "message" => "password changed"
                ], 200);
            });




        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }

    }



    /**
     *
     * @OA\Post(
     *      path="/v1.0/auth/user-register-with-garage",
     *      operationId="registerUserWithGarageClient",
     *      tags={"auth"},
     *      summary="This method is to store user with garage client side",
     *      description="This method is to store user with garage client side",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user","garage","service"},
     *             @OA\Property(property="user", type="string", format="array",example={
     * "first_Name":"Rifat",
     * "last_Name":"Al-Ashwad",
     * "email":"rifatalashwad@gmail.com",
     *  "password":"12345678",
     *  "password_confirmation":"12345678",
     *  "phone":"01771034383",
     *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *
     *  "address_line_1":"Dhaka",
     *  "address_line_2":"Dinajpur",
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     *  "postcode":"Dinajpur",
     *  "lat":"10",
     *  "long":"Din10ajpur",
     *
     * }),
     *
     *
     *
     *   *  @OA\Property(property="garage", type="string", format="array",example={
     * "name":"ABCD Garage",
     * "about":"Best Garage in Dhaka",
     * "web_page":"https://www.facebook.com/",
     *  "phone":"01771034383",
     *  "email":"rifatalashwad@gmail.com",
     *  "phone":"01771034383",
     *  "additional_information":"No Additional Information",
     *  "address_line_1":"Dhaka",
     *  "address_line_2":"Dinajpur",
     *    * *  "lat":"23.704263332849386",
     *    * *  "long":"90.44707059805279",
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     * "currency":"BDT",
     *  "postcode":"Dinajpur",
     *
     *  "logo":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *  *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *  "images":{"/a","/b","/c"},
     *  "is_mobile_garage":true,
     *  "wifi_available":true,
     *  "labour_rate":500
     *
     * }),
     *
     *   *  @OA\Property(property="service", type="string", format="array",example={
     *{

     *"automobile_category_id":1,
     *"services":{
     *{
     *"id":1,
     *"checked":true,
     *  "sub_services":{{"id":1,"checked":true},{"id":2,"checked":false}}
     * }
     *},
     *"automobile_makes":{
     *{
     *"id":1,
     *"checked":true,
     *  "models":{{"id":1,"checked":true},{"id":2,"checked":false}}
     * }
     *}
     *

     *}

     * }),
     *
     *
     *
     *
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */
    public function registerUserWithGarageClient(AuthRegisterGarageRequest $request)
    {

        try {
            $this->storeActivity($request,"");
            return DB::transaction(function () use (&$request) {
                $request_data = $request->validated();
                // user info starts ##############
                $request_data['user']['password'] = Hash::make($request_data['user']['password']);
                $request_data['user']['remember_token'] = Str::random(10);
                $request_data['user']['is_active'] = true;
                $user =  User::create($request_data['user']);
                $user->assignRole('garage_owner');
                // end user info ##############



                //  garage info ##############
                $request_data['garage']['status'] = "pending";
                $request_data['garage']['owner_id'] = $user->id;
                $garage =  Garage::create($request_data['garage']);

                if(!empty($request_data["images"])) {
                    foreach($request_data["images"] as $garage_images){
                        GarageGallery::create([
                            "image" => $garage_images,
                            "garage_id" =>$garage->id,
                        ]);
                    }
                }


                // end garage info ##############

           // create services
             $serviceUpdate =  $this->createGarageServices($request_data['service'],$garage->id,true);
                if(!$serviceUpdate["success"]){
                    throw new Exception($serviceUpdate["message"]);
                 }

               // verify email starts
               $email_token = Str::random(30);
               $user->email_verify_token = $email_token;
               $user->email_verify_token_expires = Carbon::now()->subDays(-1);
               $user->save();

                if(env("SEND_EMAIL") == true) {
                    Mail::to($user->email)->send(new VerifyMail($user));
                }

// verify email ends



$user->token = $user->createToken('Laravel Password Grant Client')->accessToken;
$user->permissions = $user->getAllPermissions()->pluck('name');
$user->roles = $user->roles->pluck('name');

$this->storeQuestion($garage->id);

                return response([
                     "user" => $user,
                     "garage" => $garage,
                     "success" => true
                ], 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }




 /**
        *
     * @OA\Get(
     *      path="/v1.0/user",
     *      operationId="getUser",
     *      tags={"auth"},
    *       security={
     *           {"bearerAuth": {}}
     *       },


     *      summary="This method is to get  user ",
     *      description="This method is to get user",
     *

     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */


public function getUser (Request $request) {
    try{
        $this->storeActivity($request,"");
        $user = $request->user();
        $user->token = auth()->user()->createToken('authToken')->accessToken;
        $user->permissions = $user->getAllPermissions()->pluck('name');
        $user->roles = $user->roles->pluck('name');
        $user->business =  $user->business;


        return response()->json(
            $user,
            200
        );
    }catch(Exception $e) {
        return $this->sendError($e, 500,$request);
    }

}
 /**
        *
     * @OA\Get(
     *      path="/v1.0/user-with-business",
     *      operationId="getUserWithBusiness",
     *      tags={"auth"},
    *       security={
     *           {"bearerAuth": {}}
     *       },


     *      summary="This method is to get  user with business ",
     *      description="This method is to get user with business",
     *

     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */


     public function getUserWithBusiness (Request $request) {
        try{
            $this->storeActivity($request,"");
            $user = User::with("business")
            ->where([
                "id" => $request->user()->id
            ])
            ->first();




            return response()->json(
                $user,
                200
            );
        }catch(Exception $e) {
            return $this->sendError($e, 500,$request);
        }

    }



  /**
        *
     * @OA\Post(
     *      path="/auth/check/email",
     *      operationId="checkEmail",
     *      tags={"auth"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to check user",
     *      description="This method is to check user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"email"},
     *
     *             @OA\Property(property="email", type="string", format="string",example="test@g.c"),
     *
     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */


    public function checkEmail(Request $request) {
        try{
            $this->storeActivity($request,"");
            $user = User::where([
                "email" => $request->email
               ])->first();
               if($user) {
       return response()->json(["data" => true],200);
               }
               return response()->json(["data" => false],200);
        }catch(Exception $e) {
            return $this->sendError($e, 500,$request);
        }

 }






  /**
        *
     * @OA\Patch(
     *      path="/auth/changepassword",
     *      operationId="changePassword",
     *      tags={"auth"},
 *
     *      summary="This method is to change password",
     *      description="This method is to change password",
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"password","cpassword"},
     *
     *     @OA\Property(property="password", type="string", format="string",* example="aaaaaaaa"),
    *  * *  @OA\Property(property="password_confirmation", type="string", format="string",example="aaaaaaaa"),
     *     @OA\Property(property="current_password", type="string", format="string",* example="aaaaaaaa"),
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request"
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found"
     *   ),
     *@OA\JsonContent()
     *      )
     *     )
     */





    public function changePassword(PasswordChangeRequest $request)
    {
try{
    $this->storeActivity($request,"");
    $client_request = $request->validated();

    $user = $request->user();



    if (!Hash::check($client_request["current_password"],$user->password)) {
        return response()->json([
            "message" => "Invalid password"
        ], 400);
    }

    $password = Hash::make($client_request["password"]);
    $user->password = $password;



    $user->login_attempts = 0;
    $user->last_failed_login_attempt_at = null;
    $user->save();



    return response()->json([
        "message" => "password changed"
    ], 200);
}catch(Exception $e) {
    return $this->sendError($e,500,$request);
}

    }






 /**
        *
     * @OA\Put(
     *      path="/v1.0/update-user-info",
     *      operationId="updateUserInfo",
     *      tags={"auth"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update user by user",
     *      description="This method is to update user by user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"first_Name","last_Name","email","password","password_confirmation","phone","address_line_1","address_line_2","country","city","postcode"},
     *             @OA\Property(property="first_Name", type="string", format="string",example="tsa"),
     *            @OA\Property(property="last_Name", type="string", format="string",example="ts"),
     *            @OA\Property(property="email", type="string", format="string",example="admin@gmail.com"),

     * *  @OA\Property(property="password", type="boolean", format="boolean",example="12345678"),
     *  * *  @OA\Property(property="password_confirmation", type="string", format="string",example="12345678"),
     *  * *  @OA\Property(property="phone", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="address_line_1", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="address_line_2", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="country", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="city", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="postcode", type="string", format="string",example="1"),
     *  *  * *  @OA\Property(property="lat", type="string", format="string",example="1"),
     *  *  * *  @OA\Property(property="long", type="string", format="string",example="1"),

     *
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *       @OA\JsonContent(),
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     * @OA\JsonContent(),
     *      ),
     *        @OA\Response(
     *          response=422,
     *          description="Unprocesseble Content",
     *    @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *   @OA\JsonContent()
     * ),
     *  * @OA\Response(
     *      response=400,
     *      description="Bad Request",
     *   *@OA\JsonContent()
     *   ),
     * @OA\Response(
     *      response=404,
     *      description="not found",
     *   *@OA\JsonContent()
     *   )
     *      )
     *     )
     */

    public function updateUserInfo(UserInfoUpdateRequest $request)
    {

        try{
            $this->storeActivity($request,"");
            $request_data = $request->validated();


            if(!empty($request_data['password'])) {
                $request_data['password'] = Hash::make($request_data['password']);
            } else {
                unset($request_data['password']);
            }
            // $request_data['is_active'] = true;
            $request_data['remember_token'] = Str::random(10);
            $user  =  tap(User::where(["id" => $request->user()->id]))->update(collect($request_data)->only([
                'first_Name' ,
                'last_Name',
                'email',
                'password',
                'phone',
                'address_line_1',
                'address_line_2',
                'country',
                'city',
                'postcode',
                "lat",
                "long",
            ])->toArray()
            )
                // ->with("somthing")

                ->first();



            $user->roles = $user->roles->pluck('name');
            $user->business = $user->business;

            return response($user, 200);
        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }










}
