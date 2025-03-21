<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRegisterBusinessRequest;
use App\Http\Requests\BusinessDefaultsUpdateRequest;
use App\Http\Requests\BusinessUpdateRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\UserCreateRequest;
use App\Http\Requests\UserToggleRequest;
use App\Http\Requests\UserUpdateRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\BillItem;
use App\Models\Business;
use App\Models\BusinessDefault;
use App\Models\SaleItem;
use App\Models\User;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

// eeeeee
class UserManagementController extends Controller
{
    use ErrorUtil,UserActivityUtil, BasicUtil;


     /**
        *
     * @OA\Post(
     *      path="/v1.0/business-image",
     *      operationId="createBusinessImage",
     *      tags={"user_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store business image ",
     *      description="This method is to store business image",
     *
   *  @OA\RequestBody(
        *   * @OA\MediaType(
*     mediaType="multipart/form-data",
*     @OA\Schema(
*         required={"image"},
*         @OA\Property(
*             description="image to upload",
*             property="image",
*             type="file",
*             collectionFormat="multi",
*         )
*     )
* )



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

     public function createBusinessImage(ImageUploadRequest $request)
     {
         try{
             $this->storeActivity($request,"");
             // if(!$request->user()->hasPermissionTo('garage_create')){
             //      return response()->json([
             //         "message" => "You can not perform this action"
             //      ],401);
             // }

             $request_data = $request->validated();

             $location = config("setup-config.business_image_location");

             // Generate a new file name with PNG extension
             $new_file_name = time() . '_' . str_replace(' ', '_', pathinfo($request_data["image"]->getClientOriginalName(), PATHINFO_FILENAME)) . '.png';

             // Move the file to the specified location with the new name and PNG extension
             $request_data["image"]->move(public_path($location), $new_file_name);


             return response()->json(["image" => $new_file_name,"location" => $location,"full_location"=>("/".$location."/".$new_file_name)], 200);


         } catch(Exception $e){
             error_log($e->getMessage());
         return $this->sendError($e,500,$request);
         }
     }


       /**
        *
     * @OA\Post(
     *      path="/v1.0/user-image",
     *      operationId="createUserImage",
     *      tags={"user_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store user image ",
     *      description="This method is to store user image",
     *
   *  @OA\RequestBody(
        *   * @OA\MediaType(
*     mediaType="multipart/form-data",
*     @OA\Schema(
*         required={"image"},
*         @OA\Property(
*             description="image to upload",
*             property="image",
*             type="file",
*             collectionFormat="multi",
*         )
*     )
* )



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

    public function createUserImage(ImageUploadRequest $request)
    {
        try{
            $this->storeActivity($request,"");
            // if(!$request->user()->hasPermissionTo('user_create')){
            //      return response()->json([
            //         "message" => "You can not perform this action"
            //      ],401);
            // }

            $request_data = $request->validated();

            $location =  config("setup-config.user_image_location");

            $new_file_name = time() . '_' . str_replace(' ', '_', $request_data["image"]->getClientOriginalName());

            $request_data["image"]->move(public_path($location), $new_file_name);


            return response()->json(["image" => $new_file_name,"location" => $location,"full_location"=>("/".$location."/".$new_file_name)], 200);


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }





    /**
        *
     * @OA\Post(
     *      path="/v1.0/users",
     *      operationId="createUser",
     *      tags={"user_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store user",
     *      description="This method is to store user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"first_Name","last_Name","email","password","password_confirmation","phone","address_line_1","address_line_2","country","city","postcode","role"},
     *             @OA\Property(property="first_Name", type="string", format="string",example="Rifat"),
     *            @OA\Property(property="last_Name", type="string", format="string",example="Al"),
     *            @OA\Property(property="email", type="string", format="string",example="rifatalashwad0@gmail.com"),

     * *  @OA\Property(property="password", type="string", format="boolean",example="12345678"),
     *  * *  @OA\Property(property="password_confirmation", type="string", format="boolean",example="12345678"),
     *  * *  @OA\Property(property="phone", type="string", format="boolean",example="01771034383"),
     *  * *  @OA\Property(property="address_line_1", type="string", format="boolean",example="dhaka"),
     *  * *  @OA\Property(property="address_line_2", type="string", format="boolean",example="dinajpur"),
     *  * *  @OA\Property(property="country", type="string", format="boolean",example="Bangladesh"),
     *  * *  @OA\Property(property="city", type="string", format="boolean",example="Dhaka"),
     *  * *  @OA\Property(property="postcode", type="string", format="boolean",example="1207"),
     *     *  * *  @OA\Property(property="lat", type="string", format="boolean",example="1207"),
     *     *  * *  @OA\Property(property="long", type="string", format="boolean",example="1207"),
     *  *  * *  @OA\Property(property="role", type="string", format="boolean",example="customer"),
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

    public function createUser(UserCreateRequest $request)
    {

        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('user_create')){
                 return response()->json([
                    "message" => "You can not perform this action"
                 ],401);
            }

            $request_data = $request->validated();

            $request_data['password'] = Hash::make($request['password']);
            $request_data['is_active'] = true;
            $request_data['remember_token'] = Str::random(10);
            $user =  User::create($request_data);

            $user->assignRole($request_data['role']);

            // $user->token = $user->createToken('Laravel Password Grant Client')->accessToken;


            $user->roles = $user->roles->pluck('name');

            // $user->permissions  = $user->getAllPermissions()->pluck('name');
            // error_log("cccccc");
            // $data["user"] = $user;
            // $data["permissions"]  = $user->getAllPermissions()->pluck('name');
            // $data["roles"] = $user->roles->pluck('name');
            // $data["token"] = $token;
            return response($user, 201);
        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }
  /**
        *
     * @OA\Post(
     *      path="/v1.0/auth/register-with-business",
     *      operationId="registerUserWithBusiness",
     *      tags={"user_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store user with business",
     *      description="This method is to store user with business",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user","business"},
     *             @OA\Property(property="user", type="string", format="array",example={
     * "first_Name":"Rifat",
     * "last_Name":"Al-Ashwad",
     * "email":"rifatalashwad@gmail.com",
     *  "password":"12345678",
     *  "password_confirmation":"12345678",
     *  "phone":"01771034383",
     *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *
     *
     * }),
     *
     *  @OA\Property(property="business", type="string", format="array",example={
     * "name":"ABCD business",
     * "about":"Best business in Dhaka",
     * "web_page":"https://www.facebook.com/",
     *  "phone":"01771034383",
     *  "email":"rifatalashwad@gmail.com",
     *  "phone":"01771034383",
     *  "additional_information":"No Additional Information",
     *  "address_line_1":"Dhaka",
     *  "address_line_2":"Dinajpur",
     *    * *  "lat":"23.704263332849386",
     *    * *  "long":"90.44707059805279",
     *
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     *  * "currency":"BDT",
     *  "postcode":"Dinajpur",
     * "invoice_title":"invoice_title",
     * "footer_text":"footer_text",
     * "is_reference_manual":"1",
     * * "receipt_footer":"t srt stgh st h",
     *
     * "account_name":"thdht rth s",
     * "account_number":"fdghdgh",
     * "send_email_alert":"1",
     *
     * "sort_code":"sort_coderthdrfth",
     *
     * "pin":"1234",
     * "type":"other",
     *
     *  "logo":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",

     *  *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *
     * }),
     *
     *
     *   *
   *     *  * *  @OA\Property(property="bill_items", type="string", format="array",example={
   *{"bill_item_id":"1"},
    *{"bill_item_id":"2"},
   * }),
   *
   *     *  * *  @OA\Property(property="sale_items", type="string", format="array",example={
   *{"sale_id":"1","item":"item","description":"description","amount":"10.1"},
    *{"sale_id":"2","item":"item","description":"description","amount":"10.1"},
   * })
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
    public function registerUserWithBusiness(AuthRegisterBusinessRequest $request) {

        try{
            $this->storeActivity($request,"");
     return  DB::transaction(function ()use (&$request) {

        if(!$request->user()->hasPermissionTo('user_create')){
            return response()->json([
               "message" => "You can not perform this action"
            ],401);
       }
        $request_data = $request->validated();

   // user info starts ##############
    $request_data['user']['password'] = Hash::make($request_data['user']['password']);
    $request_data['user']['remember_token'] = Str::random(10);
    $request_data['user']['is_active'] = true;
    $request_data['user']['created_by'] = $request->user()->id;

    $request_data['user']['address_line_1'] = $request_data['business']['address_line_1'];
    $request_data['user']['address_line_2'] = $request_data['business']['address_line_2'];
    $request_data['user']['country'] = $request_data['business']['country'];
    $request_data['user']['city'] = $request_data['business']['city'];
    $request_data['user']['postcode'] = $request_data['business']['postcode'];
    $request_data['user']['lat'] = $request_data['business']['lat'];
    $request_data['user']['long'] = $request_data['business']['long'];





    $user =  User::create($request_data['user']);
    $user->email_verified_at = now();
    $user->save();
    $user->assignRole('user');
   // end user info ##############


  //  business info ##############


        $request_data['business']['status'] = "pending";
        $request_data['business']['owner_id'] = $user->id;
        $request_data['business']['created_by'] = $request->user()->id;
        $business =  Business::create($request_data['business']);


        foreach($request_data['bill_items'] as $request_bill_item) {
            BusinessDefault::create([
                'entity_type' => "bill_item",
                'entity_id' => $request_bill_item["bill_item_id"],
                'business_owner_id' => $user->id
            ]);
        }

        foreach($request_data['sale_items'] as $request_sale_item) {

            if(empty($request_sale_item["sale_id"])) {
                $sale_item =  SaleItem::create([
                    'name' => $request_sale_item["item"],
                    'description' => $request_sale_item["description"],
                    'price' => $request_sale_item["amount"],
                    'created_by' => $user->id
                    ]);
                    $sale_item->generated_id = Str::random(4) . $sale_item->id . Str::random(4);
                    $sale_item->save();
                    $request_sale_item["sale_id"] = $sale_item->id;
            } else {
                SaleItem::where([
                    "id" => $request_sale_item["sale_id"],
                    'created_by' => $user->id
                    ])
                    ->update([
                        'name' => $request_sale_item["item"],
                        'description' => $request_sale_item["description"],
                        'price' => $request_sale_item["amount"],
                        ]);
            }





            BusinessDefault::create([
                'entity_type' => "sale_item",
                'entity_id' => $request_sale_item["sale_id"],
                'business_owner_id' => $user->id
            ]);
        }

        // foreach($request_data['business_defaults'] as $business_default) {
        //     $business_default["business_owner_id"]  = $user->id;
        //     BusinessDefault::create($business_default);

        // }







  // end business info ##############




        return response([
            "user" => $user,
            "business" => $business
        ], 201);
        });
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }

/**
        *
     * @OA\Put(
     *      path="/v1.0/auth/update-user-with-business",
     *      operationId="updateBusiness",
     *      tags={"user_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update user with business",
     *      description="This method is to update user with business",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user","business"},
     *             @OA\Property(property="user", type="string", format="array",example={
     *  * "id":1,
     * "first_Name":"Rifat",
     * "last_Name":"Al-Ashwad",
     * "email":"rifatalashwad@gmail.com",
     *  "password":"12345678",
     *  "password_confirmation":"12345678",
     *  "phone":"01771034383",
     *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *
     *
     * }),
     *
     *  @OA\Property(property="business", type="string", format="array",example={
     *   *  * "id":1,
     * "name":"ABCD business",
     * "about":"Best business in Dhaka",
     * "web_page":"https://www.facebook.com/",
     *  "phone":"01771034383",
     *  "email":"rifatalashwad@gmail.com",
     *  "phone":"01771034383",
     *  "additional_information":"No Additional Information",
     *  "address_line_1":"Dhaka",
     *  "address_line_2":"Dinajpur",
     *    * *  "lat":"23.704263332849386",
     *    * *  "long":"90.44707059805279",
     *
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     *  "postcode":"Dinajpur",
     *   "invoice_title":"invoice_title",
     *  "footer_text":"footer_text",
     * "is_reference_manual":"1",
     *  * "receipt_footer":"receipt_footer",
     *
     *   * "account_name":"thdht rth s",
     * "account_number":"fdghdgh",
     * "send_email_alert":"1",
     *
     * "sort_code":"sort_coderthdrfth",
     * "pin":"1234",
     *      *    * "type":"other",
     *
     *  "logo":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *      *  *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",

     *
     * }),
     *
     *  *     *  * *  @OA\Property(property="bill_items", type="string", format="array",example={
   *{"bill_item_id":"1"},
    *{"bill_item_id":"2"},
   * }),
   *
   *     *  * *  @OA\Property(property="sale_items", type="string", format="array",example={
   *{"sale_id":"1","item":"item","description":"description","amount":"10.1"},
    *{"sale_id":"2","item":"item","description":"description","amount":"10.1"},
   * })

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
    public function updateBusiness(BusinessUpdateRequest $request) {

        try{
            $this->storeActivity($request,"");
     return  DB::transaction(function ()use (&$request) {
    //     if(!$request->user()->hasPermissionTo('user_update')){
    //         return response()->json([
    //            "message" => "You can not perform this action"
    //         ],401);
    //    }



       $request_data = $request->validated();
    //    user email check
       $userPrev = User::where([
        "id" => $request_data["user"]["id"]
       ]);



       if(!$request->user()->hasRole('superadmin')) {
        $userPrev =  $userPrev->where(function ($query) {
            $query->where('id', auth()->user()->id);

        });
    }
    $userPrev = $userPrev->first();
     if(!$userPrev) {
            return response()->json([
               "message" => "no user found with this id"
            ],404);
     }








        if(!empty($request_data['user']['password'])) {
            $request_data['user']['password'] = Hash::make($request_data['user']['password']);
        } else {
            unset($request_data['user']['password']);
        }
        $request_data['user']['is_active'] = true;
        $request_data['user']['remember_token'] = Str::random(10);
        $request_data['user']['address_line_1'] = $request_data['business']['address_line_1'];
    $request_data['user']['address_line_2'] = $request_data['business']['address_line_2'];
    $request_data['user']['country'] = $request_data['business']['country'];
    $request_data['user']['city'] = $request_data['business']['city'];
    $request_data['user']['postcode'] = $request_data['business']['postcode'];
    $request_data['user']['lat'] = $request_data['business']['lat'];
    $request_data['user']['long'] = $request_data['business']['long'];
        $user  =  tap(User::where([
            "id" => $request_data['user']["id"]
            ]))->update(collect($request_data['user'])->only([
            'first_Name',
            'last_Name',
            'phone',
            'image',
            'address_line_1',
            'address_line_2',
            'country',
            'city',
            'postcode',
            'email',
            'password',
            "lat",
            "long",
        ])->toArray()
        )
            // ->with("somthing")

            ->first();
            if(!$user) {
                return response()->json([
                    "message" => "no user found"
                    ],404);

        }

        $user->syncRoles(["user"]);






        $business = Business::where([
            "id" => $request_data['business']["id"],
            "owner_id" => $user->id
        ])->first();

        if (!$business) {
            return response()->json([
                "message" => "No business found"
            ], 404);
        }
        if ($business->name != $request_data['business']["name"]) {
            $this->renameOrCreateFolder(str_replace(' ', '_', $business->name), str_replace(' ', '_', $request_data['business']["name"]));
        }
        $fieldsToUpdate = collect($request_data['business'])->only([
            "name",
            "about",
            "web_page",
            "phone",
            "email",
            "additional_information",
            "address_line_1",
            "address_line_2",
            "lat",
            "long",
            "country",
            "city",
            "currency",
            "postcode",
            "logo",
            "image",
            "status",
            "invoice_title",
            "footer_text",
            "is_reference_manual",
            "receipt_footer",
            "account_name",
            "account_number",
            "send_email_alert",
            "sort_code",
            "pin",
            "type"
        ])->toArray();

        $business->fill($fieldsToUpdate);
        $business->save();




            // BusinessDefault::where([
            //     'entity_type' => "bill_item",
            //     'business_owner_id' => $user->id
            // ])
            // ->delete();
            // foreach($request_data['bill_items'] as $request_bill_item) {

            //     BusinessDefault::create([
            //         'entity_type' => "bill_item",
            //         'entity_id' => $request_bill_item["bill_item_id"],
            //         'business_owner_id' => $user->id
            //     ]);
            // }
            // BusinessDefault::where([
            //     'entity_type' => "sale_item",
            //     'business_owner_id' => $user->id
            // ])
            // ->delete();
            // foreach($request_data['sale_items'] as $request_sale_item) {

            //     if(empty($request_sale_item["sale_id"])) {
            //         $sale_item =  SaleItem::create([
            //             'name' => $request_sale_item["item"],
            //             'description' => $request_sale_item["description"],
            //             'price' => $request_sale_item["amount"],
            //             'created_by' => $user->id
            //             ]);
            //             $request_sale_item["sale_id"] = $sale_item->id;
            //     } else {
            //         SaleItem::where([
            //             "id" => $request_sale_item["sale_id"],
            //             'created_by' => $user->id
            //             ])
            //             ->update([
            //                 'name' => $request_sale_item["item"],
            //                 'description' => $request_sale_item["description"],
            //                 'price' => $request_sale_item["amount"],
            //                 ]);
            //     }




            //     BusinessDefault::create([
            //         'entity_type' => "sale_item",
            //         'entity_id' => $request_sale_item["sale_id"],
            //         'business_owner_id' => $user->id
            //     ]);
            // }




            $user->business = $user->business;

        return response($user, 201);
        });
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }

/**
        *
     * @OA\Put(
     *      path="/v1.0/auth/update-business-defaults",
     *      operationId="updateBusinessDefaults",
     *      tags={"user_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update business defaults",
     *      description="This method is to update business defaults",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user","business"},
     *             @OA\Property(property="business_id", type="number", format="number",example="1"),
     *
     *  *     *  * *  @OA\Property(property="bill_items", type="string", format="array",example={
   *{"bill_item_id":"1"},
    *{"bill_item_id":"2"},
   * }),
   *
   *     *  * *  @OA\Property(property="sale_items", type="string", format="array",example={
   *{"sale_id":"1","item":"item","description":"description","amount":"10.1"},
    *{"sale_id":"2","item":"item","description":"description","amount":"10.1"},
   * })

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
    public function updateBusinessDefaults(BusinessDefaultsUpdateRequest $request) {

        try{
            $this->storeActivity($request,"");
     return  DB::transaction(function ()use (&$request) {
        if(!$request->user()->hasPermissionTo('user_update')){
            return response()->json([
               "message" => "You can not perform this action"
            ],401);
       }



       $request_data = $request->validated();



        $business  =  (Business::where([
            "id" => $request_data['business_id'],
            ]))

            ->first();
            if(!$business) {
                return response()->json([
                    "massage" => "no business found"
                ],404);

            }

            BusinessDefault::where([
                'entity_type' => "bill_item",
                'business_owner_id' => $business->owner_id
            ])
            ->delete();
            foreach($request_data['bill_items'] as $request_bill_item) {

                BusinessDefault::create([
                    'entity_type' => "bill_item",
                    'entity_id' => $request_bill_item["bill_item_id"],
                    'business_owner_id' => $business->owner_id
                ]);
            }
            BusinessDefault::where([
                'entity_type' => "sale_item",
                'business_owner_id' => $business->owner_id
            ])
            ->delete();
            foreach($request_data['sale_items'] as $request_sale_item) {

                if(empty($request_sale_item["sale_id"])) {
                    $sale_item =  SaleItem::create([
                        'name' => $request_sale_item["item"],
                        'description' => $request_sale_item["description"],
                        'price' => $request_sale_item["amount"],
                        'created_by' => $business->owner_id
                        ]);
                        $sale_item->generated_id = Str::random(4) . $sale_item->id . Str::random(4);
                        $sale_item->save();
                        $request_sale_item["sale_id"] = $sale_item->id;
                } else {
                    SaleItem::where([
                        "id" => $request_sale_item["sale_id"],
                        'created_by' => $business->owner_id
                        ])
                        ->update([
                            'name' => $request_sale_item["item"],
                            'description' => $request_sale_item["description"],
                            'price' => $request_sale_item["amount"],
                            ]);
                }




                BusinessDefault::create([
                    'entity_type' => "sale_item",
                    'entity_id' => $request_sale_item["sale_id"],
                    'business_owner_id' => $business->owner_id
                ]);
            }





        return response($business, 201);
        });
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }
 /**
        *
     * @OA\Put(
     *      path="/v1.0/users",
     *      operationId="updateUser",
     *      tags={"user_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update user",
     *      description="This method is to update user",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","first_Name","last_Name","email","password","password_confirmation","phone","address_line_1","address_line_2","country","city","postcode","role"},
     *           @OA\Property(property="id", type="string", format="number",example="1"),
     *             @OA\Property(property="first_Name", type="string", format="string",example="Rifat"),
     *            @OA\Property(property="last_Name", type="string", format="string",example="How was this?"),
     *            @OA\Property(property="email", type="string", format="string",example="How was this?"),

     * *  @OA\Property(property="password", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="password_confirmation", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="phone", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="address_line_1", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="address_line_2", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="country", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="city", type="boolean", format="boolean",example="1"),
     *  * *  @OA\Property(property="postcode", type="boolean", format="boolean",example="1"),
     *     *     *  * *  @OA\Property(property="lat", type="string", format="boolean",example="1207"),
     *     *  * *  @OA\Property(property="long", type="string", format="boolean",example="1207"),
     *  *  * *  @OA\Property(property="role", type="boolean", format="boolean",example="customer"),
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

    public function updateUser(UserUpdateRequest $request)
    {

        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('user_update')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }

           $userQuery = User::where([
            "id" => $request["id"]
       ]);

            if($userQuery->first()->hasRole("superadmin") && $request["role"] != "superadmin"){
                return response()->json([
                   "message" => "You can not change the role of super admin"
                ],401);
           }



            $request_data = $request->validated();


            if(!empty($request_data['password'])) {
                $request_data['password'] = Hash::make($request_data['password']);
            } else {
                unset($request_data['password']);
            }
            $request_data['is_active'] = true;
            $request_data['remember_token'] = Str::random(10);
            $user  =  tap(User::where(["id" => $request_data["id"]]))->update(collect($request_data)->only([
                'first_Name' ,
                'last_Name',
                'password',
                'phone',
                'address_line_1',
                'address_line_2',
                'country',
                'city',
                'postcode',
                "lat",
                "long",
                "image"

            ])->toArray()
            )
                // ->with("somthing")

                ->first();

            $user->syncRoles([$request_data['role']]);




            $user->roles = $user->roles->pluck('name');


            return response($user, 201);
        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }
 /**
        *
     * @OA\Put(
     *      path="/v1.0/users/toggle-active",
     *      operationId="toggleActive",
     *      tags={"user_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to toggle user activity",
     *      description="This method is to toggle user activity",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","first_Name","last_Name","email","password","password_confirmation","phone","address_line_1","address_line_2","country","city","postcode","role"},
     *           @OA\Property(property="id", type="string", format="number",example="1"),
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

     public function toggleActive(UserToggleRequest $request)
     {

         try{
             $this->storeActivity($request,"");
             if(!$request->user()->hasPermissionTo('user_update')){
                 return response()->json([
                    "message" => "You can not perform this action"
                 ],401);
            }
            $request_data = $request->validated();


            $user = User::where([
                "id" => $request_data["id"]
            ])
            ->first();
            if (!$user) {
                return response()->json([
                    "message" => "no user found"
                ], 404);
            }
            if($user->hasRole("superadmin")){
                return response()->json([
                   "message" => "superadmin can not be deactivated"
                ],401);
           }

            $user->update([
                'is_active' => !$user->is_active
            ]);

            return response()->json(['message' => 'User status updated successfully'], 200);


         } catch(Exception $e){
             error_log($e->getMessage());
         return $this->sendError($e,500,$request);
         }
     }


   /**
        *
     * @OA\Get(
     *      path="/v1.0/users/{perPage}",
     *      operationId="getUsers",
     *      tags={"user_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="perPage",
     *         in="path",
     *         description="perPage",
     *         required=true,
     *  example="6"
     *      ),
     *      * *  @OA\Parameter(
* name="start_date",
* in="query",
* description="start_date",
* required=true,
* example="2019-06-29"
* ),
     * *  @OA\Parameter(
* name="end_date",
* in="query",
* description="end_date",
* required=true,
* example="2019-06-29"
* ),
 * *  @OA\Parameter(
* name="order_by",
* in="query",
* description="order_by",
* required=true,
* example="ASC"
* ),
     * *  @OA\Parameter(
* name="search_key",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),
     *      summary="This method is to get user",
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

    public function getUsers($perPage,Request $request) {
        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('user_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }

            $usersQuery = User::with("roles","business");
            // ->whereHas('roles', function ($query) {
            //     // return $query->where('name','!=', 'customer');
            // });

            if(!empty($request->search_key)) {
                $usersQuery = $usersQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("first_Name", "like", "%" . $term . "%");
                    $query->orWhere("last_Name", "like", "%" . $term . "%");
                    $query->orWhere("email", "like", "%" . $term . "%");
                    $query->orWhere("phone", "like", "%" . $term . "%");
                });

            }

            if (!empty($request->start_date)) {
                $usersQuery = $usersQuery->where('created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $usersQuery = $usersQuery->where('created_at', "<=", $request->end_date);
            }

            $users = $usersQuery->orderBy("id",$request->order_by)->paginate($perPage);
            return response()->json($users, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }
       /**
        *
     * @OA\Get(
     *      path="/v1.0/users/get-by-id/{id}",
     *      operationId="getUserById",
     *      tags={"user_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="6"
     *      ),

     *      summary="This method is to get user by id",
     *      description="This method is to get user by id",
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

    public function getUserById($id,Request $request) {
        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('user_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }

            $user = User::with("roles","business")
            ->where([
                "id" => $id
            ])
            ->first();
            if (!$user) {
                return response()->json([
                    "message" => "no user found"
                ], 404);
            }

            // ->whereHas('roles', function ($query) {
            //     // return $query->where('name','!=', 'customer');
            // });


            if(!empty($user->business)) {
                $user->business[0]->default_sale_items = SaleItem::leftJoin('business_defaults', function($join) use($user) {
                    $join->on('sale_items.id', '=', 'business_defaults.entity_id')
                         ->where('business_defaults.entity_type', '=', 'sale_item')
                         ->where('business_defaults.business_owner_id', '=', $user->id);
                })
                 ->whereNotNull('business_defaults.entity_type')
                 ->select(
                    "sale_items.*"
                 )->get();


                $user->business[0]->default_bill_items   =  BillItem::leftJoin('business_defaults', function($join) use($user) {
                    $join->on('bill_items.id', '=', 'business_defaults.entity_id')
                         ->where('business_defaults.entity_type', '=', 'bill_item')
                         ->where('business_defaults.business_owner_id', '=', $user->id);
                })
                ->whereNotNull('business_defaults.entity_type')
                ->select(
                   "bill_items.*"
                )->get();
            }


            return response()->json($user, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }
/**
        *
     * @OA\Delete(
     *      path="/v1.0/users/{id}",
     *      operationId="deleteUserById",
     *      tags={"user_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="This method is to delete user by id",
     *      description="This method is to delete user by id",
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

    public function deleteUserById($id,Request $request) {

        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('user_delete')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
           if (!Hash::check($request->header("password"), $request->user()->password)) {
            return response()->json([
                "message" => "Invalid password"
            ], 401);
        }
           $user = User::where([
            "id" => $id
       ])
       ->first();

       if (!$user) {
        return response()->json([
            "message" => "no user found"
        ], 404);
    }



           if($user->hasRole("superadmin")){
            return response()->json([
               "message" => "superadmin can not be deleted"
            ],401);
       }



       $business = $user->my_business;

       if(!empty($business)) {
        $folderName = str_replace(' ', '_', $business->name);
        $folderPath = public_path($folderName);

        // Delete associated folder if it exists
        if (File::exists($folderPath)) {
            if (File::deleteDirectory($folderPath)) {
                // Log or provide a success message for folder deletion
                Log::info("Folder {$folderName} successfully deleted.");
            } else {
                // Handle the case where the folder couldn't be deleted
                Log::warning("Failed to delete folder {$folderName}.");
            }
        }

       }



           $user
          ->forceDelete();

            return response()->json(["ok" => true], 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }
}
