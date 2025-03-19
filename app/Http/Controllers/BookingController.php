<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingConfirmRequest;
use App\Http\Requests\BookingStatusChangeRequest;
use App\Http\Requests\BookingStatusChangeRequestClient;
use App\Http\Requests\BookingUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\GarageUtil;
use App\Http\Utils\PriceUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\DynamicMail;
use App\Models\Booking;
use App\Models\BookingPackage;
use App\Models\BookingSubService;
use App\Models\GarageAutomobileMake;
use App\Models\GarageAutomobileModel;
use App\Models\GaragePackage;
use App\Models\GarageSubService;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    use ErrorUtil,GarageUtil,PriceUtil,UserActivityUtil;


       /**
        *
     * @OA\Put(
     *      path="/v1.0/bookings",
     *      operationId="updateBooking",
     *      tags={"booking_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update booking",
     *      description="This method is to update booking",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","garage_id","coupon_code","price","automobile_make_id","automobile_model_id","car_registration_no","car_registration_year","booking_sub_service_ids","booking_garage_package_ids","job_start_time","job_end_time"},
     * *    @OA\Property(property="id", type="number", format="number",example="1"),
     *  * *    @OA\Property(property="garage_id", type="number", format="number",example="1"),
     * *   *    @OA\Property(property="coupon_code", type="string", format="string",example="123456"),
     *     * *   *    @OA\Property(property="price", type="number", format="number",example="30"),
     *    @OA\Property(property="automobile_make_id", type="number", format="number",example="1"),
     *    @OA\Property(property="automobile_model_id", type="number", format="number",example="1"),

     * *    @OA\Property(property="car_registration_no", type="string", format="string",example="r-00011111"),
     * *     * * *    @OA\Property(property="car_registration_year", type="string", format="string",example="2019-06-29"),
     *  * *    @OA\Property(property="booking_sub_service_ids", type="string", format="array",example={1,2,3,4}),
     * *  * *    @OA\Property(property="booking_garage_package_ids", type="string", format="array",example={1,2,3,4}),
     *
     *
     *  *  * * *   *    @OA\Property(property="status", type="string", format="string",example="pending"),
     *
     *  * @OA\Property(property="job_start_date", type="string", format="string",example="2019-06-29"),
     *
     * * @OA\Property(property="job_start_time", type="string", format="string",example="08:10"),

     *  * *    @OA\Property(property="job_end_time", type="string", format="string",example="10:10"),
     *
     *
     *
     *     *  *   * *    @OA\Property(property="transmission", type="string", format="string",example="transmission"),
     *    *  *   * *    @OA\Property(property="fuel", type="string", format="string",example="Fuel"),
     *
     *
     *
     *
     *
     *         ),

     *
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

    public function updateBooking(BookingUpdateRequest $request)
    {
        try{
            $this->storeActivity($request,"");
   return  DB::transaction(function () use($request) {
    if(!$request->user()->hasPermissionTo('booking_update')){
        return response()->json([
           "message" => "You can not perform this action"
        ],401);
   }
    $request_data = $request->validated();
    if (!$this->garageOwnerCheck($request_data["garage_id"])) {
        return response()->json([
            "message" => "you are not the owner of the garage or the requested garage does not exist."
        ], 401);
    }


        $booking  =  tap(Booking::where([
            "id" => $request_data["id"],
            "garage_id" =>  $request_data["garage_id"]
            ]))->update(collect($request_data)->only([
            "automobile_make_id",
            "automobile_model_id",
            "car_registration_no",
            "car_registration_year",
            "status",
            "job_start_date",
             "job_start_time",
            "job_end_time",
            "fuel",
            "transmission",

        ])->toArray()
        )
            // ->with("somthing")

            ->first();
            if(!$booking){
                return response()->json([
            "message" => "booking not found"
                ], 404);
            }

            $garage_make = GarageAutomobileMake::where([
                "automobile_make_id" => $request_data["automobile_make_id"],
                "garage_id"=>$request_data["garage_id"]
            ])
                ->first();
            if (!$garage_make) {
                $error =  [
                    "message" => "The given data was invalid.",
                    "errors" => ["automobile_make_id"=>["This garage does not support this make"]]
             ];
                throw new Exception(json_encode($error),422);

            }
            $garage_model = GarageAutomobileModel::where([
                "automobile_model_id" => $request_data["automobile_model_id"],
                "garage_automobile_make_id" => $garage_make->id
            ])
                ->first();
            if (!$garage_model) {
                $error =  [
                    "message" => "The given data was invalid.",
                    "errors" => ["automobile_model_id"=>["This garage does not support this model"]]
             ];
                throw new Exception(json_encode($error),422);
            }



            BookingSubService::where([
               "booking_id" => $booking->id
            ])->delete();
            BookingPackage::where([
                "booking_id" => $booking->id
             ])->delete();

            $total_price = 0;
            foreach($request_data["booking_sub_service_ids"] as $index=>$sub_service_id) {
                $garage_sub_service =  GarageSubService::leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
                    ->where([
                        "garage_services.garage_id" => $booking->garage_id,
                        "garage_sub_services.sub_service_id" => $sub_service_id
                    ])
                    ->select(
                        "garage_sub_services.id",
                        "garage_sub_services.sub_service_id",
                        "garage_sub_services.garage_service_id"
                    )
                    ->first();

                    if(!$garage_sub_service ){
                        $error =  [
                            "message" => "The given data was invalid.",
                            "errors" => [("booking_sub_service_ids[".$index."]")=>["invalid service"]]
                     ];
                        throw new Exception(json_encode($error),422);
                    }

                    $price = $this->getPrice($sub_service_id,$garage_sub_service->id, $request_data["automobile_make_id"]);


                    $total_price += $price;
                    $booking->booking_sub_services()->create([
                        "sub_service_id" => $garage_sub_service->sub_service_id,
                        "price" => $price
                    ]);

                }
                foreach($request_data["booking_garage_package_ids"] as $index=>$garage_package_id) {
                    $garage_package =  GaragePackage::where([
                        "garage_id" => $booking->garage_id,
                         "id" => $garage_package_id
                    ])

                    ->first();

                if (!$garage_package) {
                    $error =  [
                        "message" => "The given data was invalid.",
                        "errors" => [("booking_garage_package_ids[".$index."]")=>["invalid package"]]
                 ];
                    throw new Exception(json_encode($error),422);
                }




                $total_price += $garage_package->price;

                $booking->booking_packages()->create([
                    "garage_package_id" => $garage_package->id,
                    "price" => $garage_package->price
                ]);

                    }

                $booking->price = (!empty($request_data["price"]?$request_data["price"]:$total_price));


                if(!empty($request_data["coupon_code"])){
                    $coupon_discount = $this->getDiscount(
                        $request_data["garage_id"],
                        $request_data["coupon_code"],
                        $booking->price
                    );

                    if($coupon_discount) {

                        $booking->coupon_discount_type = $coupon_discount["discount_type"];
                        $booking->coupon_discount_amount = $coupon_discount["discount_amount"];


                    }
                }

                $booking->save();
                $notification_template = NotificationTemplate::where([
                    "type" => "booking_updated_by_garage_owner"
                ])
                    ->first();
                Notification::create([
                    "sender_id" =>  $booking->garage->owner_id,
                    "receiver_id" => $booking->customer_id,
                    "customer_id" => $booking->customer_id,
                    "garage_id" => $booking->garage_id,
                    "booking_id" => $booking->id,
                    "notification_template_id" => $notification_template->id,
                    "status" => "unread",
                ]);
                if(env("SEND_EMAIL") == true) {
                    Mail::to($booking->customer->email)->send(new DynamicMail(
                    $booking,
                    "booking_updated_by_garage_owner"
                ));
                }

    return response($booking, 201);
});


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }

     /**
        *
     * @OA\Put(
     *      path="/v1.0/bookings/change-status",
     *      operationId="changeBookingStatus",
     *      tags={"booking_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to change booking status",
     *      description="This method is to change booking status",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","garage_id","status"},
     * *    @OA\Property(property="id", type="number", format="number",example="1"),
 * @OA\Property(property="garage_id", type="number", format="number",example="1"),
       * @OA\Property(property="status", type="string", format="string",example="pending"),

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

    public function changeBookingStatus(BookingStatusChangeRequest $request)
    {
        try{
            $this->storeActivity($request,"");
   return  DB::transaction(function () use($request) {
    if(!$request->user()->hasPermissionTo('booking_update')){
        return response()->json([
           "message" => "You can not perform this action"
        ],401);
   }
   $request_data = $request->validated();
   if (!$this->garageOwnerCheck($request_data["garage_id"])) {
    return response()->json([
        "message" => "you are not the owner of the garage or the requested garage does not exist."
    ], 401);
}


        $booking  =  tap(Booking::where([
            "id" => $request_data["id"],
            "garage_id" =>  $request_data["garage_id"]
        ]))->update(collect($request_data)->only([
            "status",
        ])->toArray()
        )
            // ->with("somthing")

            ->first();
            if(!$booking){
                return response()->json([
            "message" => "booking not found"
                ], 404);
            }

            $notification_template = NotificationTemplate::where([
                "type" => "booking_status_changed_by_garage_owner"
            ])
                ->first();
            Notification::create([
                "sender_id" =>  $booking->garage->owner_id,
                "receiver_id" => $booking->customer_id,
                "customer_id" => $booking->customer_id,
                "garage_id" => $booking->garage_id,
                "booking_id" => $booking->id,
                "notification_template_id" => $notification_template->id,
                "status" => "unread",
            ]);
            if(env("SEND_EMAIL") == true) {
                Mail::to($booking->customer->email)->send(new DynamicMail(
                $booking,
                "booking_status_changed_by_garage_owner"
            ));
            }
    return response($booking, 201);
});

        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }









    /**
        *
     * @OA\Put(
     *      path="/v1.0/bookings/confirm",
     *      operationId="confirmBooking",
     *      tags={"booking_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to confirm booking",
     *      description="This method is to confirm booking",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","garage_id","job_start_time","job_end_time"},
     * *    @OA\Property(property="id", type="number", format="number",example="1"),
 * @OA\Property(property="garage_id", type="number", format="number",example="1"),
 * *     * *   *    @OA\Property(property="price", type="number", format="number",example="30"),
     *  * @OA\Property(property="job_start_date", type="string", format="string",example="2019-06-29"),
     *
     * * @OA\Property(property="job_start_time", type="string", format="string",example="08:10"),

     *  * *    @OA\Property(property="job_end_time", type="string", format="string",example="10:10"),



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

    public function confirmBooking(BookingConfirmRequest $request)
    {
        try{
            $this->storeActivity($request,"");
   return  DB::transaction(function () use($request) {
    if(!$request->user()->hasPermissionTo('booking_update')){
        return response()->json([
           "message" => "You can not perform this action"
        ],401);
   }
   $request_data = $request->validated();
   if (!$this->garageOwnerCheck($request_data["garage_id"])) {
    return response()->json([
        "message" => "you are not the owner of the garage or the requested garage does not exist."
    ], 401);
}

    $request_data["status"] = "confirmed";
        $booking  =  tap(Booking::where([
            "id" => $request_data["id"],
            "garage_id" =>  $request_data["garage_id"]
        ]))->update(collect($request_data)->only([
            "job_start_date",
            "job_start_time",
            "job_end_time",
            "status",

        ])->toArray()
        )
            // ->with("somthing")

            ->first();
            $booking->price  = $request_data["price"];
            if(!$booking){
                return response()->json([
            "message" => "booking not found"
                ], 404);
            }
            $notification_template = NotificationTemplate::where([
                "type" => "booking_confirmed_by_garage_owner"
            ])
                ->first();
            Notification::create([
                "sender_id" =>  $booking->garage->owner_id,
                "receiver_id" => $booking->customer_id,
                "customer_id" => $booking->customer_id,
                "garage_id" => $booking->garage_id,
                "booking_id" => $booking->id,
                "notification_template_id" => $notification_template->id,
                "status" => "unread",
            ]);
            if(env("SEND_EMAIL") == true) {
                Mail::to($booking->customer->email)->send(new DynamicMail(
                $booking,
                "booking_confirmed_by_garage_owner"
            ));
            }
    return response($booking, 201);
});


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }




 /**
        *
     * @OA\Get(
     *      path="/v1.0/bookings/{garage_id}/{perPage}",
     *      operationId="getBookings",
     *      tags={"booking_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
 *              @OA\Parameter(
     *         name="garage_id",
     *         in="path",
     *         description="garage_id",
     *         required=true,
     *  example="6"
     *      ),
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
* name="search_key",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),
     *      summary="This method is to get  bookings ",
     *      description="This method is to get bookings",
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

    public function getBookings($garage_id,$perPage,Request $request) {
        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('booking_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }


            $bookingQuery = Booking::with("booking_sub_services.sub_service","automobile_make",
            "automobile_model",
            "customer"

            )
            ->where([
                "garage_id" => $garage_id
            ]);

            if(!empty($request->search_key)) {
                $bookingQuery = $bookingQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("car_registration_no", "like", "%" . $term . "%");
                });

            }

            if (!empty($request->start_date)) {
                $bookingQuery = $bookingQuery->where('created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $bookingQuery = $bookingQuery->where('created_at', "<=", $request->end_date);
            }
            $bookings = $bookingQuery->orderByDesc("id")->paginate($perPage);
            return response()->json($bookings, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }
    }


     /**
        *
     * @OA\Get(
     *      path="/v1.0/bookings/single/{garage_id}/{id}",
     *      operationId="getBookingById",
     *      tags={"booking_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
 *              @OA\Parameter(
     *         name="garage_id",
     *         in="path",
     *         description="garage_id",
     *         required=true,
     *  example="6"
     *      ),
     *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="This method is to  get booking by id",
     *      description="This method is to get booking by id",
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

    public function getBookingById($garage_id,$id,Request $request) {
        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('booking_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }


            $booking = Booking::with("booking_sub_services.sub_service","automobile_make","automobile_model",
            "customer")
            ->where([
                "garage_id" => $garage_id,
                "id" => $id
            ])
            ->first();
             if(!$booking){
                return response()->json([
            "message" => "booking not found"
                ], 404);
            }


            return response()->json($booking, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }
    }


 /**
        *
     * @OA\Delete(
     *      path="/v1.0/bookings/{garage_id}/{id}",
     *      operationId="deleteBookingById",
     *      tags={"booking_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
 *              @OA\Parameter(
     *         name="garage_id",
     *         in="path",
     *         description="garage_id",
     *         required=true,
     *  example="6"
     *      ),
     *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="This method is to  delete booking by id",
     *      description="This method is to delete booking by id",
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

    public function deleteBookingById($garage_id,$id,Request $request) {
        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('booking_delete')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }


            $booking = Booking::where([
                "garage_id" => $garage_id,
                "id" => $id
            ])
            ->first();
             if(!$booking){
                return response()->json([
            "message" => "booking not found"
                ], 404);
            }
            $booking->delete();

            $notification_template = NotificationTemplate::where([
                "type" => "booking_deleted_by_garage_owner"
            ])
                ->first();
            Notification::create([
                "sender_id" =>  $booking->garage->owner_id,
                "receiver_id" => $booking->customer_id,
                "customer_id" => $booking->customer_id,
                "garage_id" => $booking->garage_id,
                "booking_id" => $booking->id,
                "notification_template_id" => $notification_template->id,
                "status" => "unread",
            ]);
            if(env("SEND_EMAIL") == true) {
                Mail::to($booking->customer->email)->send(new DynamicMail(
                $booking,
                "booking_deleted_by_garage_owner"
            ));
            }

            return response()->json(["ok" => true], 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }
    }

















}
