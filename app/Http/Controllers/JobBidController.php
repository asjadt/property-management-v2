<?php

namespace App\Http\Controllers;

use App\Http\Requests\JobBidCreateRequest;
use App\Http\Requests\JobBidUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\GarageUtil;
use App\Http\Utils\PriceUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\GarageAutomobileMake;
use App\Models\GarageAutomobileModel;
use App\Models\GarageService;
use App\Models\GarageSubService;
use App\Models\JobBid;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use App\Models\PreBooking;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobBidController extends Controller
{
    use ErrorUtil, GarageUtil, PriceUtil,UserActivityUtil;
    /**
     *
     * @OA\Get(
     *      path="/v1.0/pre-bookings/{garage_id}/{perPage}",
     *      operationId="getPreBookings",
     *      tags={"pre_booking_management"},
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
     * *  @OA\Parameter(
     * name="country_or_postcode",
     * in="query",
     * description="country_or_postcode",
     * required=true,
     * example="country_or_postcode"
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

     *  @OA\Parameter(
     *      name="automobile_make_ids[]",
     *      in="query",
     *      description="automobile_make_ids",
     *      required=true,
     *      example="1,2"
     * ),
     *  @OA\Parameter(
     *      name="sub_service_ids[]",
     *      in="query",
     *      description="sub_service_id",
     *      required=true,
     *      example="1,2"
     * ),
     *      summary="This method is to get pre bookings ",
     *      description="This method is to get pre bookings by garage id. only supported prebooking will show. the garage must have the sub service selected in the pre booking",
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

    public function getPreBookings($garage_id, $perPage, Request $request)
    {
        try {
            $this->storeActivity($request,"");
            if (!$request->user()->hasPermissionTo('job_bids_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }

            // $garage_sub_service_ids = GarageSubService::
            // leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
            // ->where([
            //     "garage_services.garage_id" => $garage_id
            // ])
            // ->pluck("garage_sub_services.sub_service_id");


            $preBookingQuery = PreBooking::with(
                "pre_booking_sub_services.sub_service",
                "automobile_make",
                "automobile_model"

                )

                ->leftJoin('users', 'pre_bookings.customer_id', '=', 'users.id')

                ->leftJoin('pre_booking_sub_services', 'pre_bookings.id', '=', 'pre_booking_sub_services.pre_booking_id');



            // ->whereIn("pre_booking_sub_services.sub_service_id",$garage_sub_service_ids);

            if (!empty($request->automobile_make_ids)) {
                $null_filter = collect(array_filter($request->automobile_make_ids))->values();
                $automobile_make_ids =  $null_filter->all();
                if (count($automobile_make_ids)) {
                    $preBookingQuery =   $preBookingQuery->whereIn("pre_bookings.automobile_make_id", $automobile_make_ids);
                }
            }

            if (!empty($request->sub_service_ids)) {
                $null_filter = collect(array_filter($request->sub_service_ids))->values();
                $sub_service_ids =  $null_filter->all();
                if (count($sub_service_ids)) {
                    $preBookingQuery =   $preBookingQuery->whereIn("pre_booking_sub_services.sub_service_id", $sub_service_ids);
                }
            }

            if (!empty($request->start_lat)) {
                $preBookingQuery = $preBookingQuery->where('users.lat', ">=", $request->start_lat);
            }
            if (!empty($request->end_lat)) {
                $preBookingQuery = $preBookingQuery->where('users.lat', "<=", $request->end_lat);
            }
            if (!empty($request->start_long)) {
                $preBookingQuery = $preBookingQuery->where('users.long', ">=", $request->start_long);
            }
            if (!empty($request->end_long)) {
                $preBookingQuery = $preBookingQuery->where('users.long', "<=", $request->end_long);
            }


            if (!empty($request->search_key)) {
                $preBookingQuery = $preBookingQuery->where(function ($query) use ($request) {
                    $term = $request->search_key;
                    $query->where("pre_bookings.car_registration_no", "like", "%" . $term . "%");
                });
            }
            if (!empty($request->country_or_postcode)) {
                $preBookingQuery = $preBookingQuery->where(function ($query) use ($request) {
                    $term = $request->country_or_postcode;
                    $query->where("pre_bookings.city", "like", "%" . $term . "%");
                    $query->orWhere("pre_bookings.postcode", "like", "%" . $term . "%");
                });
            }




            if (!empty($request->start_date)) {
                $preBookingQuery = $preBookingQuery->where('pre_bookings.created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $preBookingQuery = $preBookingQuery->where('pre_bookings.created_at', "<=", $request->end_date);
            }
            $pre_bookings = $preBookingQuery
                ->select(
                    "pre_bookings.*",

                    DB::raw('(SELECT COUNT(job_bids.id) FROM job_bids WHERE job_bids.pre_booking_id = pre_bookings.id) AS job_bids_count'),

                    DB::raw('(SELECT COUNT(job_bids.id) FROM job_bids
                    WHERE
                    job_bids.pre_booking_id = pre_bookings.id
                    AND
                    job_bids.garage_id = ' . $garage_id .'

                    ) AS garage_applied')

                )
                ->groupBy("pre_bookings.id")
                ->orderByDesc("pre_bookings.id")

                 ->havingRaw('(SELECT COUNT(job_bids.id) FROM job_bids WHERE job_bids.pre_booking_id = pre_bookings.id)  < 4')
                ->paginate($perPage);
            return response()->json($pre_bookings, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/pre-bookings/single/{garage_id}/{id}",
     *      operationId="getPreBookingById",
     *      tags={"pre_booking_management"},
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
     *  example="6"
     *      ),

     *      summary="This method is to get pre booking by garae id and id ",
     *      description="This method is to get pre bookings by garage id and id. only supported prebooking will show. the garage must have the sub service selected in the pre booking",
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

    public function getPreBookingById($garage_id, $id, Request $request)
    {
        try {
            $this->storeActivity($request,"");
            if (!$request->user()->hasPermissionTo('job_bids_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }

            // $garage_sub_service_ids = GarageSubService::
            // leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
            // ->where([
            //     "garage_services.garage_id" => $garage_id
            // ])
            // ->pluck("garage_sub_services.sub_service_id");

            $pre_booking = PreBooking::with(
                "pre_booking_sub_services.sub_service",
                "automobile_make",
                "automobile_model"
                )
                ->leftJoin('pre_booking_sub_services', 'pre_bookings.id', '=', 'pre_booking_sub_services.pre_booking_id')
                // ->whereIn("pre_booking_sub_services.sub_service_id",$garage_sub_service_ids)
                ->where([
                    "pre_bookings.id" => $id
                ])
                ->select("pre_bookings.*",   DB::raw('(SELECT COUNT(job_bids.id) FROM job_bids WHERE job_bids.pre_booking_id = pre_bookings.id) AS job_bids_count'),
                DB::raw('(SELECT COUNT(job_bids.id) FROM job_bids
                WHERE
                job_bids.pre_booking_id = pre_bookings.id
                AND
                job_bids.garage_id = ' . $garage_id .'

                ) AS garage_applied')
                )
                ->first();

            if (!$pre_booking) {
                return response()->json(
                    [
                        "message" => "no pre booking found"
                    ],
                    404
                );
            }



            return response()->json($pre_booking, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }

    /**
     *
     * @OA\Post(
     *      path="/v1.0/job-bids",
     *      operationId="createJobBid",
     *      tags={"job_bid_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store job bid",
     *      description="This method is to store job bid",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"garage_id","pre_booking_id","price","offer_template","description",  "job_start_date","job_start_time","job_end_time"},
     *    @OA\Property(property="garage_id", type="number", format="number",example="1"),
     *    @OA\Property(property="pre_booking_id", type="number", format="number",example="1"),
     *    @OA\Property(property="price", type="number", format="number",example="10.99"),
     * *    @OA\Property(property="offer_template", type="string", format="string",example="offer template goes here"),
     *     *  * @OA\Property(property="job_start_date", type="string", format="string",example="2019-06-29"),
     *
     * * @OA\Property(property="job_start_time", type="string", format="string",example="08:10"),

     *  * *    @OA\Property(property="job_end_time", type="string", format="string",example="10:10"),
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

    public function createJobBid(JobBidCreateRequest $request)
    {
        try {
            $this->storeActivity($request,"");
            return DB::transaction(function () use ($request) {

                if (!$request->user()->hasPermissionTo('job_bids_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();
                if (!$this->garageOwnerCheck($request_data["garage_id"])) {
                    return response()->json([
                        "message" => "you are not the owner of the garage or the requested garage does not exist."
                    ], 401);
                }

                // $garage_sub_service_ids = GarageSubService::
                // leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
                // ->where([
                //     "garage_services.garage_id" => $request_data["garage_id"]
                // ])
                // ->pluck("garage_sub_services.sub_service_id");

                $pre_booking = PreBooking::with("pre_booking_sub_services.sub_service")

                    ->leftJoin('job_bids', 'pre_bookings.id', '=', 'job_bids.pre_booking_id')



                    ->leftJoin('pre_booking_sub_services', 'pre_bookings.id', '=', 'pre_booking_sub_services.pre_booking_id')
                    // ->whereIn("pre_booking_sub_services.sub_service_id",$garage_sub_service_ids)
                    ->where([
                        "pre_bookings.id" => $request_data["pre_booking_id"]
                    ])

                    ->select(
                        "pre_bookings.*",
                        DB::raw('COUNT(job_bids.id) AS bid_count')
                    )
                    ->groupBy("pre_bookings.id")
                    // ->havingRaw('bid_count < 4')
                    ->first();

                if (!$pre_booking) {
                    return response()->json(
                        [
                            "message" => "no pre booking found"
                        ],
                        404
                    );
                }

                foreach ($pre_booking->pre_booking_sub_services as $pre_booking_sub_service) {
                    $pre_booking_service_id =  $pre_booking_sub_service->sub_service->service_id;

                    $garage_service =   GarageService::where([
                        "garage_id" => $request_data["garage_id"],
                        "service_id" => $pre_booking_service_id
                    ])
                        ->first();
                    if (!$garage_service) {
                        $garage_service =  GarageService::create([
                            "garage_id" => $request_data["garage_id"],
                            "service_id" => $pre_booking_service_id
                        ]);
                    }
                    $garage_sub_service = GarageSubService::where([
                        "garage_service_id" => $garage_service->id,
                        "sub_service_id" => $pre_booking_sub_service->id,
                    ])
                        ->first();
                    if (!$garage_sub_service) {
                        $garage_sub_service =  GarageSubService::create([
                            "garage_service_id" => $garage_service->id,
                            "sub_service_id" => $pre_booking_sub_service->id,
                        ]);
                    }
                }



                $garage_automobile_make =   GarageAutomobileMake::where([
                    "garage_id" => $request_data["garage_id"],
                    "automobile_make_id" => $pre_booking->automobile_make_id
                ])
                    ->first();
                if (!$garage_automobile_make) {
                    $garage_automobile_make =  GarageAutomobileMake::create([
                        "garage_id" => $request_data["garage_id"],
                        "automobile_make_id" => $pre_booking->automobile_make_id
                    ]);
                }
                $garage_automobile_model = GarageAutomobileModel::where([
                    "garage_automobile_make_id" => $garage_automobile_make->id,
                    "automobile_model_id" => $pre_booking->automobile_model_id,
                ])
                    ->first();
                if (!$garage_automobile_model) {
                    $garage_automobile_model =  GarageAutomobileModel::create([
                        "garage_automobile_make_id" => $garage_automobile_make->id,
                        "automobile_model_id" => $pre_booking->automobile_model_id,
                    ]);
                }



                $previous_job_bid = JobBid::where([
                    "pre_booking_id" => $request_data["pre_booking_id"],
                    "garage_id" => $request_data["garage_id"],
                ])
                    ->first();

                if ($previous_job_bid) {
                    return response()->json(
                        [
                            "message" => "bid already present"
                        ],
                        409
                    );
                }

                $job_bid =  JobBid::create($request_data);

                $notification_template = NotificationTemplate::where([
                    "type" => "bid_created_by_garage_owner"
                ])
                    ->first();


                Notification::create([
                    "sender_id" => $request->user()->id,
                    "receiver_id" => $job_bid->pre_booking->customer_id,
                    "customer_id" => $job_bid->pre_booking->customer_id,
                    "garage_id" => $job_bid->garage_id,
                    "bid_id" => $job_bid->id,
                    "pre_booking_id" => $job_bid->pre_booking->id,
                    "notification_template_id" => $notification_template->id,
                    "status" => "unread",
                ]);



                return response($job_bid, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500,$request);
        }
    }


    /**
     *
     * @OA\Put(
     *      path="/v1.0/job-bids",
     *      operationId="updateJobBid",
     *      tags={"job_bid_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update job bid",
     *      description="This method is to update job bid",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","garage_id","pre_booking_id","price","offer_template","description"},
     *    @OA\Property(property="id", type="number", format="number",example="1"),
     *    @OA\Property(property="garage_id", type="number", format="number",example="1"),
     *    @OA\Property(property="pre_booking_id", type="number", format="number",example="1"),
     *    @OA\Property(property="price", type="number", format="number",example="10.99"),
     * *    @OA\Property(property="offer_template", type="string", format="string",example="offer template goes here"),
     * *     *  * @OA\Property(property="job_start_date", type="string", format="string",example="2019-06-29"),
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

    public function updateJobBid(JobBidUpdateRequest $request)
    {
        try {
            $this->storeActivity($request,"");
            return  DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('fuel_station_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                $request_data = $request->validated();

                if (!$this->garageOwnerCheck($request_data["garage_id"])) {
                    return response()->json([
                        "message" => "you are not the owner of the garage or the requested garage does not exist."
                    ], 401);
                }

                $garage_sub_service_ids = GarageSubService::leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
                    ->where([
                        "garage_services.garage_id" => $request_data["garage_id"]
                    ])
                    ->pluck("garage_sub_services.sub_service_id");

                $pre_booking = PreBooking::with("pre_booking_sub_services.sub_service")
                    ->leftJoin('pre_booking_sub_services', 'pre_bookings.id', '=', 'pre_booking_sub_services.pre_booking_id')
                    ->whereIn("pre_booking_sub_services.sub_service_id", $garage_sub_service_ids)
                    ->where([
                        "pre_bookings.id" => $request_data["pre_booking_id"]
                    ])
                    ->first();

                if (!$pre_booking) {
                    return response()->json(
                        [
                            "message" => "no pre booking found"
                        ],
                        404
                    );
                }

                foreach ($pre_booking->pre_booking_sub_services as $pre_booking_sub_service) {
                    $pre_booking_service_id =  $pre_booking_sub_service->sub_service->service_id;

                    $garage_service =   GarageService::where([
                        "garage_id" => $request_data["garage_id"],
                        "service_id" => $pre_booking_service_id
                    ])
                        ->first();
                    if (!$garage_service) {
                        $garage_service =  GarageService::create([
                            "garage_id" => $request_data["garage_id"],
                            "service_id" => $pre_booking_service_id
                        ]);
                    }
                    $garage_sub_service = GarageSubService::where([
                        "garage_service_id" => $garage_service->id,
                        "sub_service_id" => $pre_booking_sub_service->id,
                    ])
                        ->first();
                    if (!$garage_sub_service) {
                        $garage_sub_service =  GarageSubService::create([
                            "garage_service_id" => $garage_service->id,
                            "sub_service_id" => $pre_booking_sub_service->id,
                        ]);
                    }
                }



                $garage_automobile_make =   GarageAutomobileMake::where([
                    "garage_id" => $request_data["garage_id"],
                    "automobile_make_id" => $pre_booking->automobile_make_id
                ])
                    ->first();
                if (!$garage_automobile_make) {
                    $garage_automobile_make =  GarageAutomobileMake::create([
                        "garage_id" => $request_data["garage_id"],
                        "automobile_make_id" => $pre_booking->automobile_make_id
                    ]);
                }
                $garage_automobile_model = GarageAutomobileModel::where([
                    "garage_automobile_make_id" => $garage_automobile_make->id,
                    "automobile_model_id" => $pre_booking->automobile_model_id,
                ])
                    ->first();
                if (!$garage_automobile_model) {
                    $garage_automobile_model =  GarageAutomobileModel::create([
                        "garage_automobile_make_id" => $garage_automobile_make->id,
                        "automobile_model_id" => $pre_booking->automobile_model_id,
                    ]);
                }





                $job_bid  =  tap(JobBid::where(["id" => $request_data["id"]]))->update(
                    collect($request_data)->only([
                        "garage_id",
                        "pre_booking_id",
                        "price",
                        "offer_template",
                        "job_start_date",
                        "job_start_time",
                        "job_end_time"
                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();

                $notification_template = NotificationTemplate::where([
                    "type" => "bid_updated_by_garage_owner"
                ])
                    ->first();

                Notification::create([
                    "sender_id" => $request->user()->id,
                    "receiver_id" => $job_bid->pre_booking->customer_id,
                    "customer_id" => $job_bid->pre_booking->customer_id,
                    "garage_id" => $job_bid->garage_id,
                    "bid_id" => $job_bid->id,
                    "pre_booking_id" => $job_bid->pre_booking->id,
                    "notification_template_id" => $notification_template->id,
                    "status" => "unread",
                ]);


                return response($job_bid, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500,$request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/job-bids/{garage_id}/{perPage}",
     *      operationId="getJobBids",
     *      tags={"job_bid_management"},
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
     *      summary="This method is to get job bids",
     *      description="This method is to get job bids",
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

    public function getJobBids($garage_id, $perPage, Request $request)
    {
        try {
            $this->storeActivity($request,"");
            if (!$request->user()->hasPermissionTo('job_bids_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }



            $jobBidQuery = JobBid::with("pre_booking.pre_booking_sub_services")
                ->leftJoin('pre_bookings', 'job_bids.pre_booking_id', '=', 'pre_bookings.id');

            if (!empty($request->search_key)) {
                $jobBidQuery = $jobBidQuery->where(function ($query) use ($request) {
                    $term = $request->search_key;
                    $query->where("pre_bookings.car_registration_no", "like", "%" . $term . "%");
                });
            }

            if (!empty($request->start_date)) {
                $jobBidQuery = $jobBidQuery->where('job_bids.created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $jobBidQuery = $jobBidQuery->where('job_bids.created_at', "<=", $request->end_date);
            }
            $job_bids = $jobBidQuery->orderByDesc("job_bids.id")
                ->select("job_bids.*")
                ->paginate($perPage);
            return response()->json($job_bids, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }



    /**
     *
     * @OA\Get(
     *      path="/v1.0/job-bids/single/{garage_id}/{id}",
     *      operationId="getJobBidById",
     *      tags={"job_bid_management"},
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
     *  example="6"
     *      ),

     *      summary="This method is to get job bid by garae id and id ",
     *      description="This method is to get job bid by garage id and id.",
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

    public function getJobBidById($garage_id, $id, Request $request)
    {
        try {
            $this->storeActivity($request,"");
            if (!$request->user()->hasPermissionTo('job_bids_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }



            $job_bid =  JobBid::with("pre_booking.pre_booking_sub_services")
                ->leftJoin('pre_bookings', 'job_bids.pre_booking_id', '=', 'pre_bookings.id')


                ->where([
                    "job_bids.id" => $id
                ])
                ->select("job_bids.*")
                ->first();

            if (!$job_bid) {
                return response()->json(
                    [
                        "message" => "no job bid found"
                    ],
                    404
                );
            }



            return response()->json($job_bid, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }


    /**
     *
     * @OA\Delete(
     *      path="/v1.0/job-bids/{garage_id}/{id}",
     *      operationId="deleteJobBidById",
     *      tags={"job_bid_management"},
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
     *      summary="This method is to  delete job bid by id",
     *      description="This method is to delete job bid by id",
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

    public function deleteJobBidById($garage_id, $id, Request $request)
    {
        try {
            $this->storeActivity($request,"");
            if (!$request->user()->hasPermissionTo('job_bids_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }


            $job_bid = JobBid::where([
                "garage_id" => $garage_id,
                "id" => $id
            ])
                ->first();
            if (!$job_bid) {
                return response()->json([
                    "message" => "job bid not found"
                ], 404);
            }
            $job_bid->delete();


            return response()->json($job_bid, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }
}
