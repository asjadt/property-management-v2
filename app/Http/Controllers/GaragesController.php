<?php

namespace App\Http\Controllers;

use App\Http\Requests\AuthRegisterGarageRequest;
use App\Http\Requests\GarageUpdateRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\MultipleImageUploadRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\GarageUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Garage;
use App\Models\GarageAutomobileMake;
use App\Models\GarageGallery;
use App\Models\GarageService;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class GaragesController extends Controller
{
    use ErrorUtil,GarageUtil,UserActivityUtil;



  /**
        *
     * @OA\Post(
     *      path="/v1.0/garage-image-multiple",
     *      operationId="createGarageImageMultiple",
     *      tags={"garage_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *      summary="This method is to store garage gallery",
     *      description="This method is to store garage gallery",
     *
   *  @OA\RequestBody(
        *   * @OA\MediaType(
*     mediaType="multipart/form-data",
*     @OA\Schema(
*         required={"images[]"},
*         @OA\Property(
*             description="array of images to upload",
*             property="images[]",
*             type="array",
*             @OA\Items(
*                 type="file"
*             ),
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

    public function createGarageImageMultiple(MultipleImageUploadRequest $request)
    {
        try{
            $this->storeActivity($request,"");

            $insertableData = $request->validated();

            $location =  config("setup-config.garage_gallery_location");

            $images = [];
            if(!empty($insertableData["images"])) {
                foreach($insertableData["images"] as $image){
                    $new_file_name = time() . '_' . str_replace(' ', '_', $image->getClientOriginalName());
                    $image->move(public_path($location), $new_file_name);

                    array_push($images,("/".$location."/".$new_file_name));


                    // GarageGallery::create([
                    //     "image" => ("/".$location."/".$new_file_name),
                    //     "garage_id" => $garage_id
                    // ]);

                }
            }


            return response()->json(["images" => $images], 201);


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }

     /**
        *
     * @OA\Post(
     *      path="/v1.0/auth/register-with-garage",
     *      operationId="registerUserWithGarage",
     *      tags={"garage_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store user with garage",
     *      description="This method is to store user with garage",
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
     * *  "country":"Bangladesh",
     * *  "country":"Bangladesh",
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     *  "postcode":"Dinajpur",
     *     *  "lat":"12",
     *     *  "long":"12",
     *
     * }),
     *
     *  @OA\Property(property="garage", type="string", format="array",example={
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
     *
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     *  * "currency":"BDT",
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
    public function registerUserWithGarage(AuthRegisterGarageRequest $request) {

        try{
            $this->storeActivity($request,"");
     return  DB::transaction(function ()use (&$request) {

        if(!$request->user()->hasPermissionTo('garage_create')){
            return response()->json([
               "message" => "You can not perform this action"
            ],401);
       }
        $insertableData = $request->validated();

   // user info starts ##############
    $insertableData['user']['password'] = Hash::make($insertableData['user']['password']);
    $insertableData['user']['remember_token'] = Str::random(10);
    $insertableData['user']['is_active'] = true;
    $insertableData['user']['created_by'] = $request->user()->id;
    $user =  User::create($insertableData['user']);
    $user->assignRole('garage_owner');
   // end user info ##############


  //  garage info ##############
        $insertableData['garage']['status'] = "pending";
        $insertableData['garage']['owner_id'] = $user->id;
        $insertableData['garage']['created_by'] = $request->user()->id;
        $garage =  Garage::create($insertableData['garage']);

        if(!empty($insertableData["images"])) {
            foreach($insertableData["images"] as $garage_images){
                GarageGallery::create([
                    "image" => $garage_images,
                    "garage_id" =>$garage->id,
                ]);
            }
        }


  // end garage info ##############

  // create services
     $serviceUpdate = $this->createGarageServices($insertableData['service'],$garage->id);

     if(!$serviceUpdate["success"]){
        throw new Exception($serviceUpdate["message"]);
     }

     $this->storeQuestion($garage->id);


        return response([
            "user" => $user,
            "garage" => $garage
        ], 201);
        });
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }



     /**
        *
     * @OA\Put(
     *      path="/v1.0/garages",
     *      operationId="updateGarage",
     *      tags={"garage_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update user with garage",
     *      description="This method is to update user with garage",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"user","garage","service"},
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
     *  "address_line_1":"Dhaka",
     *  "address_line_2":"Dinajpur",
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     *  "postcode":"Dinajpur",
     * "lat":"12",
     * "long":"12",
     *
     * }),
     *
     *  @OA\Property(property="garage", type="string", format="array",example={
     *   *  * "id":1,
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
     *
     *  "country":"Bangladesh",
     *  "city":"Dhaka",
     *  "postcode":"Dinajpur",
     *
     *  "logo":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
     *      *  *  "image":"https://images.unsplash.com/photo-1671410714831-969877d103b1?ixlib=rb-4.0.3&ixid=MnwxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8&auto=format&fit=crop&w=387&q=80",
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
    public function updateGarage(GarageUpdateRequest $request) {

        try{
            $this->storeActivity($request,"");
     return  DB::transaction(function ()use (&$request) {
        if(!$request->user()->hasPermissionTo('garage_update')){
            return response()->json([
               "message" => "You can not perform this action"
            ],401);
       }
       if (!$this->garageOwnerCheck($request["garage"]["id"])) {
        return response()->json([
            "message" => "you are not the owner of the garage or the requested garage does not exist."
        ], 401);
    }

       $updatableData = $request->validated();
    //    user email check
       $userPrev = User::where([
        "id" => $updatableData["user"]["id"]
       ]);
       if(!$request->user()->hasRole('superadmin')) {
        $userPrev =  $garageQuery = $userPrev->where(function ($query) {
            $query->where('created_by', auth()->user()->id)
                  ->orWhere('id', auth()->user()->id);
        });
    }
    $userPrev = $userPrev->first();
     if(!$userPrev) {
            return response()->json([
               "message" => "no user found with this id"
            ],404);
     }


   if($userPrev->email !== $updatableData['user']['email']) {
        if(User::where(["email" => $updatableData['user']['email']])->exists()) {
              return response()->json([
                 "message" => "The given data was invalid.",
                 "errors" => ["user.password"=>["email already taken"]]
              ],422);
        }
    }
    // user email check
     // garage email check + authorization check
     $garagePrev = Garage::where([
        "id" => $updatableData["garage"]["id"]
     ]);

    $garagePrev = $garagePrev->first();
    if(!$garagePrev) {
        return response()->json([
           "message" => "no garage found with this id"
        ],404);
      }

   if($garagePrev->email !== $updatableData['garage']['email']) {
        if(Garage::where(["email" => $updatableData['garage']['email']])->exists()) {
              return response()->json([
                 "message" => "The given data was invalid.",
                 "errors" => ["garage.password"=>["email already taken"]]
              ],422);
        }
    }
    // garage email check + authorization check



        if(!empty($updatableData['user']['password'])) {
            $updatableData['user']['password'] = Hash::make($updatableData['user']['password']);
        } else {
            unset($updatableData['user']['password']);
        }
        $updatableData['user']['is_active'] = true;
        $updatableData['user']['remember_token'] = Str::random(10);
        $user  =  tap(User::where([
            "id" => $updatableData['user']["id"]
            ]))->update(collect($updatableData['user'])->only([
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

        $user->syncRoles(["garage_owner"]);



  //  garage info ##############
        // $updatableData['garage']['status'] = "pending";

        $garage  =  tap(Garage::where([
            "id" => $updatableData['garage']["id"]
            ]))->update(collect($updatableData['garage'])->only([
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
                "postcode",
                "logo",
                "image",
                "status",
                // "is_active",
                "is_mobile_garage",
                "wifi_available",
                "labour_rate",

        ])->toArray()
        )
            // ->with("somthing")

            ->first();
            if(!$garage) {
                return response()->json([
                    "massage" => "no garage found"
                ],404);

            }
            if(!empty($updatableData["images"])) {
                foreach($updatableData["images"] as $garage_images){
                    GarageGallery::create([
                        "image" => $garage_images,
                        "garage_id" =>$garage->id,
                    ]);
                }
            }


  // end garage info ##############

  GarageService::where([
    "garage_id" => $garage->id
  ])
  ->delete();
  GarageAutomobileMake::where([
    "garage_id" => $garage->id
    ])
    ->delete();

  // create services
  $this->createGarageServices($updatableData['service'],$garage->id);


        return response([
            "user" => $user,
            "garage" => $garage
        ], 201);
        });
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }



    /**
        *
     * @OA\Get(
     *      path="/v1.0/garages/{perPage}",
     *      operationId="getGarages",
     *      tags={"garage_management"},
     * *  @OA\Parameter(
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
* name="search_key",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),
     * *  @OA\Parameter(
* name="country_code",
* in="query",
* description="country_code",
* required=true,
* example="country_code"
* ),
    * *  @OA\Parameter(
* name="address",
* in="query",
* description="address",
* required=true,
* example="address"
* ),
     * *  @OA\Parameter(
* name="city",
* in="query",
* description="city",
* required=true,
* example="city"
* ),
    * *  @OA\Parameter(
* name="start_lat",
* in="query",
* description="start_lat",
* required=true,
* example="3"
* ),
     * *  @OA\Parameter(
* name="end_lat",
* in="query",
* description="end_lat",
* required=true,
* example="2"
* ),
     * *  @OA\Parameter(
* name="start_long",
* in="query",
* description="start_long",
* required=true,
* example="1"
* ),
     * *  @OA\Parameter(
* name="end_long",
* in="query",
* description="end_long",
* required=true,
* example="4"
* ),
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
     *      summary="This method is to get garages",
     *      description="This method is to get garages",
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

    public function getGarages($perPage,Request $request) {

        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('garage_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }

            $garagesQuery = Garage::with(
                "owner",
                "garageAutomobileMakes.garageAutomobileModels",
                "garageServices.garageSubServices.garage_sub_service_prices"
            );


            if(!$request->user()->hasRole('superadmin')) {
                $garagesQuery = $garagesQuery->where(function ($query) use ($request) {
                    $query->where('created_by', $request->user()->id)
                          ->orWhere('owner_id', $request->user()->id);
                });
            }

            if(!empty($request->search_key)) {
                $garagesQuery = $garagesQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("name", "like", "%" . $term . "%");
                    $query->orWhere("phone", "like", "%" . $term . "%");
                    $query->orWhere("email", "like", "%" . $term . "%");
                    $query->orWhere("city", "like", "%" . $term . "%");
                    $query->orWhere("postcode", "like", "%" . $term . "%");
                });

            }


            if (!empty($request->start_date)) {
                $garagesQuery = $garagesQuery->where('created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $garagesQuery = $garagesQuery->where('created_at', "<=", $request->end_date);
            }

            if (!empty($request->start_lat)) {
                $garagesQuery = $garagesQuery->where('lat', ">=", $request->start_lat);
            }
            if (!empty($request->end_lat)) {
                $garagesQuery = $garagesQuery->where('lat', "<=", $request->end_lat);
            }
            if (!empty($request->start_long)) {
                $garagesQuery = $garagesQuery->where('long', ">=", $request->start_long);
            }
            if (!empty($request->end_long)) {
                $garagesQuery = $garagesQuery->where('long', "<=", $request->end_long);
            }

            if (!empty($request->address)) {
                $garagesQuery = $garagesQuery->where(function ($query) use ($request) {
                    $term = $request->address;
                    $query->where("country", "like", "%" . $term . "%");
                    $query->orWhere("city", "like", "%" . $term . "%");


                });
            }
            if (!empty($request->country_code)) {
                $garagesQuery =   $garagesQuery->orWhere("country", "like", "%" . $request->country_code . "%");

            }
            if (!empty($request->city)) {
                $garagesQuery =   $garagesQuery->orWhere("city", "like", "%" . $request->city . "%");

            }


            $garages = $garagesQuery->orderByDesc("id")->paginate($perPage);
            return response()->json($garages, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }

     /**
        *
     * @OA\Get(
     *      path="/v1.0/garages/single/{id}",
     *      operationId="getGarageById",
     *      tags={"garage_management"},
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
     *      summary="This method is to get garage by id",
     *      description="This method is to get garage by id",
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

    public function getGarageById($id,Request $request) {

        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('garage_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
           if (!$this->garageOwnerCheck($id)) {
            return response()->json([
                "message" => "you are not the owner of the garage or the requested garage does not exist."
            ], 401);
        }

            $garage = Garage::with(
                "owner",
                "garageAutomobileMakes.automobileMake",
                "garageAutomobileMakes.garageAutomobileModels.automobileModel",
                "garageServices.service",
                "garageServices.garageSubServices.garage_sub_service_prices",
                "garageServices.garageSubServices.subService",
                "garage_times",
                "garageGalleries",
                "garage_packages",
                "garage_affiliations.affiliation"
            )->where([
                "id" => $id
            ])
            ->first();
       $garage_automobile_make_ids =  GarageAutomobileMake::where(["garage_id"=>$garage->id])->pluck("automobile_make_id");
        $garage_service_ids =   GarageService::where(["garage_id"=>$garage->id])->pluck("service_id");

        $data["garage"] = $garage;
        $data["garage_automobile_make_ids"] = $garage_automobile_make_ids;
        $data["garage_service_ids"] = $garage_service_ids;
        return response()->json($data, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }

/**
        *
     * @OA\Delete(
     *      path="/v1.0/garages/{id}",
     *      operationId="deleteGarageById",
     *      tags={"garage_management"},
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
     *      summary="This method is to delete garage by id",
     *      description="This method is to delete garage by id",
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

    public function deleteGarageById($id,Request $request) {

        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('garage_delete')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }

           $garagesQuery =   Garage::where([
            "id" => $id
           ]);
           if(!$request->user()->hasRole('superadmin')) {
            $garagesQuery =    $garagesQuery->where([
                "created_by" =>$request->user()->id
            ]);
        }

        $garage = $garagesQuery->first();

        $garage->delete();



            return response()->json(["ok" => true], 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }



    }




   /**
        *
     * @OA\Get(
     *      path="/v1.0/available-countries",
     *      operationId="getAvailableCountries",
     *      tags={"basics"},
    *       security={
     *           {"bearerAuth": {}}
     *       },

     * *  @OA\Parameter(
* name="search_key",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),

     *      summary="This method is to get available country list",
     *      description="This method is to get available country list",
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

    public function getAvailableCountries(Request $request) {
        try{
            $this->storeActivity($request,"");

            $countryQuery = new Garage();

            if(!empty($request->search_key)) {
                $automobilesQuery = $countryQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("country", "like", "%" . $term . "%");
                });

            }



            $countries = $countryQuery
            ->distinct("country")
            ->orderByDesc("country")
            ->select("id","country")
            ->get();
            return response()->json($countries, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }



    /**
        *
     * @OA\Get(
     *      path="/v1.0/available-cities/{country_code}",
     *      operationId="getAvailableCities",
     *      tags={"basics"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     * *  @OA\Parameter(
* name="country_code",
* in="path",
* description="country_code",
* required=true,
* example="country_code"
* ),


     * *  @OA\Parameter(
* name="search_key",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),

     *      summary="This method is to get available city list",
     *      description="This method is to get available city list",
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

    public function getAvailableCities($country_code,Request $request) {
        try{

            $this->storeActivity($request,"");
            $countryQuery =  Garage::where("country",$country_code);

            if(!empty($request->search_key)) {
                $automobilesQuery = $countryQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("city", "like", "%" . $term . "%");
                });

            }



            $countries = $countryQuery
            ->distinct("city")
            ->orderByDesc("city")
            ->select("id","city")
            ->get();
            return response()->json($countries, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }


    /**
        *
     * @OA\Get(
     *      path="/v1.0/garages/by-garage-owner/all",
     *      operationId="getAllGaragesByGarageOwner",
     *      tags={"garage_management"},

    *       security={
     *           {"bearerAuth": {}}
     *       },

     *      summary="This method is to get garages",
     *      description="This method is to get garages",
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

    public function getAllGaragesByGarageOwner(Request $request) {

        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasRole('garage_owner')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }

            $garagesQuery = Garage::where([
                "owner_id" => $request->user()->id
            ]);



            $garages = $garagesQuery->orderByDesc("id")->get();
            return response()->json($garages, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }

    }


}
