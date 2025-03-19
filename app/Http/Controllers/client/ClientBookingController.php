<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookingCreateRequestClient;
use App\Http\Requests\BookingStatusChangeRequestClient;
use App\Http\Requests\BookingUpdateRequestClient;
use App\Http\Utils\CouponUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\PriceUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\DynamicMail;
use App\Models\Booking;
use App\Models\BookingPackage;
use App\Models\BookingSubService;
use App\Models\Garage;
use App\Models\GarageAutomobileMake;
use App\Models\GarageAutomobileModel;
use App\Models\GaragePackage;
use App\Models\GarageSubService;
use App\Models\Job;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ClientBookingController extends Controller
{
    use ErrorUtil, CouponUtil, PriceUtil,UserActivityUtil;
    /**
     *
     * @OA\Post(
     *      path="/v1.0/client/bookings",
     *      operationId="createBookingClient",
     *      tags={"client.booking"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store booking",
     *      description="This method is to store booking",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"garage_id","coupon_code","automobile_make_id","automobile_model_id","car_registration_no","car_registration_year","booking_sub_service_ids","booking_garage_package_ids"},
     *    @OA\Property(property="garage_id", type="number", format="number",example="1"),
     *   *    @OA\Property(property="coupon_code", type="string", format="string",example="123456"),
     *
     *    @OA\Property(property="automobile_make_id", type="number", format="number",example="1"),
     *    @OA\Property(property="automobile_model_id", type="number", format="number",example="1"),
     * * *    @OA\Property(property="car_registration_no", type="string", format="string",example="r-00011111"),
     *     * * *    @OA\Property(property="car_registration_year", type="string", format="string",example="2019-06-29"),
     *
     *   * *    @OA\Property(property="additional_information", type="string", format="string",example="r-00011111"),
     *
     *  *   * *    @OA\Property(property="transmission", type="string", format="string",example="transmission"),
     *    *  *   * *    @OA\Property(property="fuel", type="string", format="string",example="Fuel"),

     *
     *
     * @OA\Property(property="job_start_date", type="string", format="string",example="2019-06-29"),


     *  * *    @OA\Property(property="booking_sub_service_ids", type="string", format="array",example={1,2,3,4}),
     *  *  * *    @OA\Property(property="booking_garage_package_ids", type="string", format="array",example={1,2,3,4}),
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

    public function createBookingClient(BookingCreateRequestClient $request)
    {
        try {
            $this->storeActivity($request,"");
            return DB::transaction(function () use ($request) {
                $request_data = $request->validated();

                $request_data["customer_id"] = auth()->user()->id;
                $request_data["status"] = "pending";

                $garage = Garage::where([
                    "id" => $request_data["garage_id"]
                ])
                    ->first();

                if (!$garage) {
                    return response()
                        ->json(
                            [
                                "message" => "garage not found."
                            ],
                            404
                        );
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



                $booking =  Booking::create($request_data);


                $total_price = 0;

                foreach ($request_data["booking_sub_service_ids"] as $index=>$sub_service_id) {
                    $garage_sub_service =  GarageSubService::leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
                        ->where([
                            "garage_services.garage_id" => $garage->id,
                            "garage_sub_services.sub_service_id" => $sub_service_id
                        ])
                        ->select(
                            "garage_sub_services.id",
                            "garage_sub_services.sub_service_id",
                            "garage_sub_services.garage_service_id"
                        )
                        ->first();

                    if (!$garage_sub_service) {

                        $error =  [
                            "message" => "The given data was invalid.",
                            "errors" => [("booking_sub_service_ids[" . $index . "]")=>["invalid service"]]
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

                foreach ($request_data["booking_garage_package_ids"] as $index=>$garage_package_id) {
                    $garage_package =  GaragePackage::where([
                            "garage_id" => $garage->id,
                            "id" => $garage_package_id
                        ])

                        ->first();

                    if (!$garage_package) {

                        $error =  [
                            "message" => "The given data was invalid.",
                            "errors" => [("booking_garage_package_ids[" . $index . "]")=>["invalid package"]]
                     ];
                        throw new Exception(json_encode($error),422);
                    }


                    $total_price += $garage_package->price;

                    $booking->booking_packages()->create([
                        "garage_package_id" => $garage_package->id,
                        "price" => $garage_package->price
                    ]);


                }



                $booking->price = $total_price;
                $booking->save();

                if (!empty($request_data["coupon_code"])) {
                    $coupon_discount = $this->getDiscount(
                        $request_data["garage_id"],
                        $request_data["coupon_code"],
                        $total_price
                    );

                    if ($coupon_discount) {

                        $booking->coupon_discount_type = $coupon_discount["discount_type"];
                        $booking->coupon_discount_amount = $coupon_discount["discount_amount"];
                        $booking->coupon_code = $request_data["coupon_code"];

                        $booking->save();
                    }
                }

                $notification_template = NotificationTemplate::where([
                    "type" => "booking_created_by_client"
                ])
                    ->first();
                Notification::create([
                    "sender_id" => $request->user()->id,
                    "receiver_id" => $booking->garage->owner_id,
                    "customer_id" => $booking->customer_id,
                    "garage_id" => $booking->garage_id,
                    "booking_id" => $booking->id,
                    "notification_template_id" => $notification_template->id,
                    "status" => "unread",
                ]);

                if(env("SEND_EMAIL") == true) {
                    Mail::to($booking->customer->email)->send(new DynamicMail(
                    $booking,
                    "booking_created_by_client"
                ));
                }

                return response($booking, 201);
            });
        } catch (Exception $e) {


             return $this->sendError($e,500,$request);
        }
    }

    /**
     *
     * @OA\Patch(
     *      path="/v1.0/client/bookings/change-status",
     *      operationId="changeBookingStatusClient",
     *      tags={"client.booking"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to change booking status",
     *      description="This method is to change booking status.
     * if status is accepted. the booking will be converted to a job.  and the status of the job will be pending ",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","status"},
     * *    @OA\Property(property="id", type="number", format="number",example="1"),
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

    public function changeBookingStatusClient(BookingStatusChangeRequestClient $request)
    {
        try {
            $this->storeActivity($request,"");
            return  DB::transaction(function () use ($request) {

                $request_data = $request->validated();

                $booking = Booking::where([
                    "id" => $request_data["id"],
                    "customer_id" =>  auth()->user()->id
                ])
                    ->first();
                if (!$booking) {
                    return response()->json([
                        "message" => "booking not found"
                    ], 404);
                }
                if ($booking->status != "confirmed") {
                    return response()->json([
                        "message" => "you can only accecpt or reject only a confirmed booking"
                    ], 409);
                }





                if ($request_data["status"] == "rejected_by_client") {
                    $booking->status = $request_data["status"];
                    $booking->save();
                    $notification_template = NotificationTemplate::where([
                        "type" => "booking_rejected_by_client"
                    ])
                        ->first();
                    Notification::create([
                        "sender_id" => $request->user()->id,
                        "receiver_id" => $booking->garage->owner_id,
                        "customer_id" => $booking->customer_id,
                        "garage_id" => $booking->garage_id,
                        "booking_id" => $booking->id,
                        "notification_template_id" => $notification_template->id,
                        "status" => "unread",
                    ]);
                    if(env("SEND_EMAIL") == true) {
                        Mail::to($booking->customer->email)->send(new DynamicMail(
                        $booking,
                        "booking_rejected_by_client"
                    ));
                    }

                } else if ($request_data["status"] == "accepted") {

                    $job = Job::create([
                        "booking_id" => $booking->id,
                        "garage_id" => $booking->garage_id,
                        "customer_id" => $booking->customer_id,
                        "automobile_make_id" => $booking->automobile_make_id,
                        "automobile_model_id" => $booking->automobile_model_id,
                        "car_registration_no" => $booking->car_registration_no,
                        "car_registration_year" => $booking->car_registration_year,
                        "additional_information" => $booking->additional_information,
                        "job_start_date" => $booking->job_start_date,

                        "job_start_time" => $booking->job_start_time,
                        "job_end_time" => $booking->job_end_time,

                        "fuel" => $booking->fuel,
                        "transmission" => $booking->transmission,



                        "coupon_discount_type" => $booking->coupon_discount_type,
                        "coupon_discount_amount" => $booking->coupon_discount_amount,


                        "discount_type" => "fixed",
                        "discount_amount" => 0,
                        "price" => 0,
                        "status" => "pending",
                        "payment_status" => "due",



                    ]);

                    $total_price = 0;

                    foreach (BookingSubService::where([
                            "booking_id" => $booking->id
                        ])->get()
                        as
                        $booking_sub_service) {
                        $job->job_sub_services()->create([
                            "sub_service_id" => $booking_sub_service->sub_service_id,
                            "price" => $booking_sub_service->price
                        ]);
                        $total_price += $booking_sub_service->price;

                    }

                    foreach (BookingPackage::where([
                        "booking_id" => $booking->id
                    ])->get()
                    as
                    $booking_package) {
                    $job->job_packages()->create([
                        "garage_package_id" => $booking_package->garage_package_id,
                        "price" => $booking_package->price
                    ]);
                    $total_price += $booking_package->price;

                }




                    $job->price = $total_price;
                    $job->save();
                    $booking->status = "converted_to_job";
                    $booking->save();
                    // $booking->delete();

                    $notification_template = NotificationTemplate::where([
                        "type" => "booking_accepted_by_client"
                    ])
                        ->first();
                    Notification::create([
                        "sender_id" => $request->user()->id,
                        "receiver_id" => $booking->garage->owner_id,
                        "customer_id" => $booking->customer_id,
                        "garage_id" => $booking->garage_id,
                        "booking_id" => $booking->id,
                        "notification_template_id" => $notification_template->id,
                        "status" => "unread",
                    ]);
                    if(env("SEND_EMAIL") == true) {
                        Mail::to($booking->customer->email)->send(new DynamicMail(
                        $booking,
                        "booking_accepted_by_client"
                    ));}
                }




                return response([
                    "ok" => true
                ], 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e,500,$request);
        }
    }


    /**
     *
     * @OA\Put(
     *      path="/v1.0/client/bookings",
     *      operationId="updateBookingClient",
     *      tags={"client.booking"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update booking",
     *      description="This method is to update booking",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","garage_id","coupon_code","automobile_make_id","automobile_model_id","car_registration_no","car_registration_year","booking_sub_service_ids","booking_garage_package_ids"},
     * *    @OA\Property(property="id", type="number", format="number",example="1"),
     *    @OA\Property(property="garage_id", type="number", format="number",example="1"),
     * *   *    @OA\Property(property="coupon_code", type="string", format="string",example="123456"),
     *    @OA\Property(property="automobile_make_id", type="number", format="number",example="1"),
     *    @OA\Property(property="automobile_model_id", type="number", format="number",example="1"),
     * *    @OA\Property(property="car_registration_no", type="string", format="string",example="r-00011111"),
     * *     * * *    @OA\Property(property="car_registration_year", type="string", format="string",example="2019-06-29"),
     *
     *    *  *   * *    @OA\Property(property="transmission", type="string", format="string",example="transmission"),
     *    *  *   * *    @OA\Property(property="fuel", type="string", format="string",example="Fuel"),
     *
     *
     *
     *  * *    @OA\Property(property="booking_sub_service_ids", type="string", format="array",example={1,2,3,4}),
     *   *  * *    @OA\Property(property="booking_garage_package_ids", type="string", format="array",example={1,2,3,4}),
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

    public function updateBookingClient(BookingUpdateRequestClient $request)
    {
        try {
            $this->storeActivity($request,"");
            return  DB::transaction(function () use ($request) {

                $request_data = $request->validated();

                $garage = Garage::where([
                    "id" => $request_data["garage_id"]
                ])
                    ->first();

                if (!$garage) {
                    return response()
                        ->json(
                            [
                                "message" => "garage not found."
                            ],
                            404
                        );
                }

                $garage_make = GarageAutomobileMake::where([
                    "automobile_make_id" => $request_data["automobile_make_id"],
                    "garage_id"=>$garage->id
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






                $booking  =  tap(Booking::where(["id" => $request_data["id"]]))->update(
                    collect($request_data)->only([
                        "garage_id",
                        "automobile_make_id",
                        "automobile_model_id",
                        "car_registration_no",
                        "car_registration_year",
                        "additional_information",
                        "coupon_code",
                        "fuel",
                        "transmission",
                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();
                if (!$booking) {
                    return response()->json([
                        "message" => "booking not found"
                    ], 404);
                }
                BookingSubService::where([
                    "booking_id" => $booking->id
                ])->delete();

                $total_price = 0;
                foreach ($request_data["booking_sub_service_ids"] as $index=>$sub_service_id) {
                    $garage_sub_service =  GarageSubService::leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
                        ->where([
                            "garage_services.garage_id" => $garage->id,
                            "garage_sub_services.sub_service_id" => $sub_service_id
                        ])
                        ->select(
                            "garage_sub_services.id",
                            "garage_sub_services.sub_service_id",
                            "garage_sub_services.garage_service_id"
                        )
                        ->first();

                    if (!$garage_sub_service) {

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

                foreach ($request_data["booking_garage_package_ids"] as $index=>$garage_package_id) {
                    $garage_package =  GaragePackage::where([
                            "garage_id" => $garage->id,
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



                $booking->price = $total_price;
                $booking->save();
                if (!empty($request_data["coupon_code"])) {
                    $coupon_discount = $this->getDiscount(
                        $request_data["garage_id"],
                        $request_data["coupon_code"],
                        $total_price
                    );

                    if ($coupon_discount) {

                        $booking->coupon_discount_type = $coupon_discount["discount_type"];
                        $booking->coupon_discount_amount = $coupon_discount["discount_amount"];
                        $booking->coupon_code = $request_data["coupon_code"];
                        $booking->save();
                    }
                }
                $notification_template = NotificationTemplate::where([
                    "type" => "booking_updated_by_client"
                ])
                    ->first();
                Notification::create([
                    "sender_id" => $request->user()->id,
                    "receiver_id" => $booking->garage->owner_id,
                    "customer_id" => $booking->customer_id,
                    "garage_id" => $booking->garage_id,
                    "booking_id" => $booking->id,
                    "notification_template_id" => $notification_template->id,
                    "status" => "unread",
                ]);
                if(env("SEND_EMAIL") == true) {
                    Mail::to($booking->customer->email)->send(new DynamicMail(
                    $booking,
                    "booking_updated_by_client"
                ));}


                return response($booking, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e,500,$request);
        }
    }



    /**
     *
     * @OA\Get(
     *      path="/v1.0/client/bookings/{perPage}",
     *      operationId="getBookingsClient",
     *      tags={"client.booking"},
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

    public function getBookingsClient($perPage, Request $request)
    {
        try {
            $this->storeActivity($request,"");
            $bookingQuery = Booking::with("booking_sub_services.sub_service","automobile_make","automobile_model")
                ->where([
                    "customer_id" => $request->user()->id
                ]);

            if (!empty($request->search_key)) {
                $bookingQuery = $bookingQuery->where(function ($query) use ($request) {
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
        } catch (Exception $e) {

            return $this->sendError($e,500,$request);
        }
    }





    /**
     *
     * @OA\Get(
     *      path="/v1.0/client/bookings/single/{id}",
     *      operationId="getBookingByIdClient",
     *      tags={"client.booking"},
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
     *      summary="This method is to get  booking by id ",
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

    public function getBookingByIdClient($id, Request $request)
    {
        try {
            $this->storeActivity($request,"");
            $booking = Booking::with("booking_sub_services.sub_service","automobile_make","automobile_model")
                ->where([
                    "id" => $id,
                    "customer_id" => $request->user()->id
                ])
                ->first();

            if (!$booking) {
                return response()->json([
                    "message" => "booking not found"
                ], 404);
            }


            return response()->json($booking, 200);
        } catch (Exception $e) {

            return $this->sendError($e,500,$request);
        }
    }






    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/client/bookings/{id}",
     *      operationId="deleteBookingByIdClient",
     *      tags={"client.booking"},
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
     *      summary="This method is to delete booking by id",
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

    public function deleteBookingByIdClient($id, Request $request)
    {

        try {
            $this->storeActivity($request,"");
            $booking =  Booking::where([
                "id" => $id,
                "customer_id" => $request->user()->id
            ])->first();
            if(!$booking){
                return response()->json([
                    "message" => "no booking found"
                ],
            404);
            }
            $booking->delete();

            $notification_template = NotificationTemplate::where([
                "type" => "booking_deleted_by_client"
            ])
                ->first();
            Notification::create([
                "sender_id" => $request->user()->id,
                "receiver_id" => $booking->garage->owner_id,
                "customer_id" => $booking->customer_id,
                "garage_id" => $booking->garage_id,
                "booking_id" => $booking->id,
                "notification_template_id" => $notification_template->id,
                "status" => "unread",
            ]);
                if(env("SEND_EMAIL") == true) {
                    Mail::to($booking->customer->email)->send(new DynamicMail(
                    $booking,
                    "booking_deleted_by_client"
                ));
            }

            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {
            return $this->sendError($e,500,$request);
        }
    }











}
