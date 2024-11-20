<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingToJobRequest;
use App\Http\Requests\JobPaymentCreateRequest;
use App\Http\Requests\JobStatusChangeRequest;
use App\Http\Requests\JobUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\GarageUtil;
use App\Http\Utils\PriceUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\DynamicMail;
use App\Models\Booking;
use App\Models\BookingPackage;
use App\Models\BookingSubService;
use App\Models\GaragePackage;
use App\Models\GarageSubService;
use App\Models\Job;
use App\Models\JobPackage;
use App\Models\JobPayment;
use App\Models\JobSubService;
use App\Models\Notification;
use App\Models\NotificationTemplate;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class JobController extends Controller
{
    use ErrorUtil,GarageUtil,PriceUtil,UserActivityUtil;

      /**
        *
     * @OA\Patch(
     *      path="/v1.0/jobs/booking-to-job",
     *      operationId="bookingToJob",
     *      tags={"job_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to convert booking to job",
     *      description="This method is to convert booking to job",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
   * *   required={"booking_id","coupon_code","garage_id","discount_type","discount_amount","price","job_start_date","job_start_time","job_end_time","status"},
   * *    @OA\Property(property="booking_id", type="number", format="number",example="1"),
   *   *   *    @OA\Property(property="coupon_code", type="string", format="string",example="123456"),
     *  * *    @OA\Property(property="garage_id", type="number", format="number",example="1"),
 *  * *    @OA\Property(property="discount_type", type="string", format="string",example="percentage"),
 * *  * *    @OA\Property(property="discount_amount", type="number", format="number",example="10"),
 *  * *  * *    @OA\Property(property="price", type="number", format="number",example="30"),
 *     *  * @OA\Property(property="job_start_date", type="string", format="string",example="2019-06-29"),
     *
     * * @OA\Property(property="job_start_time", type="string", format="string",example="08:10"),

     *  * *    @OA\Property(property="job_end_time", type="string", format="string",example="10:10"),
     *  *  * *    @OA\Property(property="status", type="string", format="string",example="pending"),
     *
     *
     * *     *  *   * *    @OA\Property(property="transmission", type="string", format="string",example="transmission"),
     *    *  *   * *    @OA\Property(property="fuel", type="string", format="string",example="Fuel"),
     *
     *         ),
     *  * *

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

    public function bookingToJob(BookingToJobRequest $request)
    {
        try{
            $this->storeActivity($request,"");
   return  DB::transaction(function () use($request) {
    if(!$request->user()->hasPermissionTo('job_create')){
        return response()->json([
           "message" => "You can not perform this action"
        ],401);
   }
    $updatableData = $request->validated();
    if (!$this->garageOwnerCheck($updatableData["garage_id"])) {
        return response()->json([
            "message" => "you are not the owner of the garage or the requested garage does not exist."
        ], 401);
    }


        $booking  = Booking::where([
            "id" => $updatableData["booking_id"],
            "garage_id" =>  $updatableData["garage_id"]
            ])
            ->first();

            // $coupon_discount = false;
            // if(!empty($request_data["coupon_code"])){
            //     $coupon_discount = $this->getDiscount(
            //         $request_data["garage_id"],
            //         $request_data["coupon_code"],
            //         $request_data["price"]
            //     );
            // }


                if(!$booking){
                    return response()->json([
                "message" => "booking not found"
                    ], 404);
                }




                $job = Job::create([
                    "booking_id" => $booking->id,
                    "garage_id" => $booking->garage_id,
                    "customer_id" => $booking->customer_id,
                    "automobile_make_id"=> $booking->automobile_make_id,
                    "automobile_model_id"=> $booking->automobile_model_id,
                    "car_registration_no"=> $booking->car_registration_no,
                    "car_registration_year"=> $booking->car_registration_year,
                    "additional_information" => $booking->additional_information,


                    // "coupon_discount_type" => ($coupon_discount?$coupon_discount["discount_type"]:0),
                    // "coupon_discount_amount" => ($coupon_discount?$coupon_discount["discount_amount"]:0),



                    "coupon_code" => $updatableData["coupon_code"],



                    "job_start_date"=> $updatableData["job_start_date"],
                    "job_start_time"=> $updatableData["job_start_time"],
                    "job_end_time"=> $updatableData["job_end_time"],

                    "fuel" => $updatableData['fuel'],
                    "transmission" => $updatableData['transmission'],

                    "discount_type" => $updatableData["discount_type"],
                    "discount_amount"=> $updatableData["discount_amount"],
                    "price"=>($updatableData["price"]?$updatableData["price"]:$booking->price),
                    "status" => $updatableData["status"],
                    "payment_status" => "due",
                ]);

                foreach(

                BookingSubService::where([
                    "booking_id" => $booking->id
                ])->get()
                as
                $booking_sub_service
                ) {

                 JobSubService::create([
                    "job_id" => $job->id,
                    "sub_service_id" => $booking_sub_service->sub_service_id,
                    "price" => $booking_sub_service->price,
                 ]);

                }

                foreach(

                    BookingPackage::where([
                        "booking_id" => $booking->id
                    ])->get()
                    as
                    $booking_package
                    ) {

                     JobPackage::create([
                        "job_id" => $job->id,
                        "garage_package_id" => $booking_package->garage_package_id,
                        "price" => $booking_package->price,
                     ]);

                    }

                if(!empty($updatableData["coupon_code"])){
                    $coupon_discount = $this->getDiscount(
                        $updatableData["garage_id"],
                        $updatableData["coupon_code"],
                        $job->price

                    );

                    if($coupon_discount) {

                        $job->coupon_discount_type = $coupon_discount["discount_type"];
                        $job->coupon_discount_amount = $coupon_discount["discount_amount"];


                    }
                }

                $job->save();

                $booking->status = "converted_to_job";
                $booking->save();

                // $booking->delete();
                $notification_template = NotificationTemplate::where([
                    "type" => "job_created_by_garage_owner"
                ])
                    ->first();
                Notification::create([
                    "sender_id" =>  $job->garage->owner_id,
                    "receiver_id" => $job->customer_id,
                    "customer_id" => $job->customer_id,
                    "garage_id" => $job->garage_id,
                    "job_id" => $job->id,
                    "notification_template_id" => $notification_template->id,
                    "status" => "unread",
                ]);
                if(env("SEND_EMAIL") == true) {
                    Mail::to($job->customer->email)->send(new DynamicMail(
                    $job,
                    "job_created_by_garage_owner"
                ));
            }

    return response([
        "ok" => true
    ], 201);
});


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }

  /**
        *
     * @OA\Put(
     *      path="/v1.0/jobs",
     *      operationId="updateJob",
     *      tags={"job_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update job",
     *      description="This method is to update job",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","garage_id","coupon_code","automobile_make_id","automobile_model_id","car_registration_no","car_registration_year","job_sub_service_ids","job_garage_package_ids","job_start_time","job_end_time"},
     * *    @OA\Property(property="id", type="number", format="number",example="1"),
     *  * *    @OA\Property(property="garage_id", type="number", format="number",example="1"),
    * *   *    @OA\Property(property="coupon_code", type="string", format="string",example="123456"),
     *    @OA\Property(property="automobile_make_id", type="number", format="number",example="1"),
     *    @OA\Property(property="automobile_model_id", type="number", format="number",example="1"),

     * *    @OA\Property(property="car_registration_no", type="string", format="string",example="r-00011111"),
     * *     * * *    @OA\Property(property="car_registration_year", type="string", format="string",example="2019-06-29"),
     *
     *  * *    @OA\Property(property="job_sub_service_ids", type="string", format="array",example={1,2,3,4}),
     *
     *    *  * *    @OA\Property(property="job_garage_package_ids", type="string", format="array",example={1,2,3,4}),
     *
     *  *  * *

     *
     *  *  * *    @OA\Property(property="discount_type", type="string", format="string",example="percentage"),
 * *  * *    @OA\Property(property="discount_amount", type="number", format="number",example="10"),
 *  * *  * *    @OA\Property(property="price", type="number", format="number",example="60"),
 *
 *  *     *  * @OA\Property(property="job_start_date", type="string", format="string",example="2019-06-29"),
     *
     * * @OA\Property(property="job_start_time", type="string", format="string",example="08:10"),

     *  * *    @OA\Property(property="job_end_time", type="string", format="string",example="10:10"),
     *  *  * *    @OA\Property(property="status", type="string", format="string",example="pending"),
     *
     *
     *   * *     *  *   * *    @OA\Property(property="transmission", type="string", format="string",example="transmission"),
     *    *  *   * *    @OA\Property(property="fuel", type="string", format="string",example="Fuel"),
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

    public function updateJob(JobUpdateRequest $request)
    {
        try{
            $this->storeActivity($request,"");
   return  DB::transaction(function () use($request) {
    if(!$request->user()->hasPermissionTo('job_update')){
        return response()->json([
           "message" => "You can not perform this action"
        ],401);
   }
    $updatableData = $request->validated();
    if (!$this->garageOwnerCheck($updatableData["garage_id"])) {
        return response()->json([
            "message" => "you are not the owner of the garage or the requested garage does not exist."
        ], 401);
    }

    // $coupon_discount = false;
    // if(!empty($updatableData["coupon_code"])){
    //     $coupon_discount = $this->getDiscount(
    //         $updatableData["garage_id"],
    //         $updatableData["coupon_code"],
    //         $updatableData["price"]
    //     );

    // }

    // if($coupon_discount) {
    //     $updatableData["coupon_discount_type"] = $coupon_discount["discount_type"];
    //     $updatableData["coupon_discount_amount"] = $coupon_discount["discount_amount"];

    // }


        $job  =  tap(Job::where([
            "id" => $updatableData["id"],
            "garage_id" =>  $updatableData["garage_id"]
            ]))->update(collect($updatableData)->only([
            "automobile_make_id",
            "automobile_model_id",



            // "coupon_discount_type",
            // "coupon_discount_amount",

            "coupon_code",

            "car_registration_no",
            "car_registration_year",
            "status",
            "job_start_date",
            "job_start_time",
            "job_end_time",
            "discount_type",
            "discount_amount",
            "price",
            "fuel",
            "transmission",
        ])->toArray()
        )
            // ->with("somthing")

            ->first();
            if(!$job){
                return response()->json([
            "message" => "job not found"
                ], 404);
            }
            JobSubService::where([
               "job_id" => $job->id
            ])->delete();

            JobPackage::where([
                "job_id" => $job->id
             ])->delete();

            foreach($updatableData["job_sub_service_ids"] as $index=>$sub_service_id) {
                $garage_sub_service =  GarageSubService::leftJoin('garage_services', 'garage_sub_services.garage_service_id', '=', 'garage_services.id')
                    ->where([
                        "garage_services.garage_id" => $job->garage_id,
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
                            "errors" => [("job_sub_service_ids[".$index."]")=>["invalid service"]]
                     ];
                        throw new Exception(json_encode($error),422);
                    }
                    $price = $this->getPrice(
                        $sub_service_id,
                        $garage_sub_service->id,
                        $updatableData["automobile_make_id"]
                    );

                    JobSubService::create([
                        "sub_service_id" => $garage_sub_service->sub_service_id,
                        "job_id" => $job->id,
                        "price" => $price
                    ]);

                }
                foreach($updatableData["job_garage_package_ids"] as $index=>$garage_package_id) {
                    $garage_package =  GaragePackage::where([
                        "garage_id" => $job->garage_id,
                         "id" => $garage_package_id
                    ])
                    ->first();

                        if(!$garage_package ){
                            $error =  [
                                "message" => "The given data was invalid.",
                                "errors" => [("job_garage_package_ids[".$index."]")=>["invalid package"]]
                         ];
                            throw new Exception(json_encode($error),422);
                        }
                        JobPackage::create([
                            "garage_package_id" => $garage_package->id,
                            "job_id" => $job->id,
                            "price" => $garage_package->price
                        ]);

                    }






                if(!empty($updatableData["coupon_code"])){
                    $coupon_discount = $this->getDiscount(
                        $updatableData["garage_id"],
                        $updatableData["coupon_code"],
                        $job->price

                    );

                    if($coupon_discount) {

                        $job->coupon_discount_type = $coupon_discount["discount_type"];
                        $job->coupon_discount_amount = $coupon_discount["discount_amount"];


                    }
                }

                $job->save();

                $notification_template = NotificationTemplate::where([
                    "type" => "job_updated_by_garage_owner"
                ])
                    ->first();
                Notification::create([
                    "sender_id" =>  $job->garage->owner_id,
                    "receiver_id" => $job->customer_id,
                    "customer_id" => $job->customer_id,
                    "garage_id" => $job->garage_id,
                    "job_id" => $job->id,
                    "notification_template_id" => $notification_template->id,
                    "status" => "unread",
                ]);
                if(env("SEND_EMAIL") == true) {
                    Mail::to($job->customer->email)->send(new DynamicMail(
                    $job,
                    "job_updated_by_garage_owner"
                ));
            }


    return response($job, 201);
});


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }

    /**
        *
     * @OA\Put(
     *      path="/v1.0/jobs/change-status",
     *      operationId="changeJobStatus",
     *      tags={"job_management"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to change job status",
     *      description="This method is to change job status",
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

    public function changeJobStatus(JobStatusChangeRequest $request)
    {
        try{
            $this->storeActivity($request,"");
   return  DB::transaction(function () use($request) {
    if(!$request->user()->hasPermissionTo('job_update')){
        return response()->json([
           "message" => "You can not perform this action"
        ],401);
   }
   $updatableData = $request->validated();
   if (!$this->garageOwnerCheck($updatableData["garage_id"])) {
    return response()->json([
        "message" => "you are not the owner of the garage or the requested garage does not exist."
    ], 401);
}


        $job  =  tap(Job::where([
            "id" => $updatableData["id"],
            "garage_id" =>  $updatableData["garage_id"]
        ]))->update(collect($updatableData)->only([
            "status",
        ])->toArray()
        )
            // ->with("somthing")
            ->first();
            if(!$job){
                return response()->json([
            "message" => "job not found"
                ], 404);
            }
            $notification_template = NotificationTemplate::where([
                "type" => "job_status_changed_by_garage_owner"
            ])
                ->first();
            Notification::create([
                "sender_id" =>  $job->garage->owner_id,
                "receiver_id" => $job->customer_id,
                "customer_id" => $job->customer_id,
                "garage_id" => $job->garage_id,
                "job_id" => $job->id,
                "notification_template_id" => $notification_template->id,
                "status" => "unread",
            ]);
            if(env("SEND_EMAIL") == true) {
                Mail::to($job->customer->email)->send(new DynamicMail(
                $job,
                "job_status_changed_by_garage_owner"
            ));
        }
    return response($job, 201);
});

        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }





    /**
        *
     * @OA\Get(
     *      path="/v1.0/jobs/{garage_id}/{perPage}",
     *      operationId="getJobs",
     *      tags={"job_management"},
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
     *      summary="This method is to get  jobs ",
     *      description="This method is to get jobs",
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

    public function getJobs($garage_id,$perPage,Request $request) {
        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('job_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }


            $jobQuery = Job::with(
                "garage",
                "customer",
                "job_sub_services.sub_service",
                "job_packages.garage_package",
                "automobile_make",
                "automobile_model",
                "job_payments"
                )
            ->where([
                "garage_id" => $garage_id
            ]);

            if(!empty($request->search_key)) {
                $jobQuery = $jobQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("car_registration_no", "like", "%" . $term . "%");
                });

            }

            if (!empty($request->start_date)) {
                $jobQuery = $jobQuery->where('created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $jobQuery = $jobQuery->where('created_at', "<=", $request->end_date);
            }
            $jobs = $jobQuery->orderByDesc("id")->paginate($perPage);
            return response()->json($jobs, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }
    }


     /**
        *
     * @OA\Get(
     *      path="/v1.0/jobs/single/{garage_id}/{id}",
     *      operationId="getJobById",
     *      tags={"job_management"},
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
     *      summary="This method is to  get job by id",
     *      description="This method is to get job by id",
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

    public function getJobById($garage_id,$id,Request $request) {
        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('job_view')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }


            $job = Job::with(
                "garage",
                "customer",
                "job_sub_services.sub_service",
                "job_packages.garage_package",
                "automobile_make",
                "automobile_model",
                "job_payments"
                )
            ->where([
                "garage_id" => $garage_id,
                "id" => $id
            ])
            ->first();
             if(!$job){
                return response()->json([
            "message" => "job not found"
                ], 404);
            }


            return response()->json($job, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }
    }




     /**
        *
     * @OA\Delete(
     *      path="/v1.0/jobs/{garage_id}/{id}",
     *      operationId="deleteJobById",
     *      tags={"job_management"},
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
     *      summary="This method is to  delete job by id",
     *      description="This method is to delete job by id",
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

    public function deleteJobById($garage_id,$id,Request $request) {
        try{
            $this->storeActivity($request,"");
            if(!$request->user()->hasPermissionTo('job_delete')){
                return response()->json([
                   "message" => "You can not perform this action"
                ],401);
           }
            if (!$this->garageOwnerCheck($garage_id)) {
                return response()->json([
                    "message" => "you are not the owner of the garage or the requested garage does not exist."
                ], 401);
            }


            $job = Job::where([
                "garage_id" => $garage_id,
                "id" => $id
            ])
            ->first();
             if(!$job){
                return response()->json([
            "message" => "job not found"
                ], 404);
            }
            $job->delete();

            $notification_template = NotificationTemplate::where([
                "type" => "job_deleted_by_garage_owner"
            ])
                ->first();
            Notification::create([
                "sender_id" =>  $job->garage->owner_id,
                "receiver_id" => $job->customer_id,
                "customer_id" => $job->customer_id,
                "garage_id" => $job->garage_id,
                "job_id" => $job->id,
                "notification_template_id" => $notification_template->id,
                "status" => "unread",
            ]);
            if(env("SEND_EMAIL") == true) {
                Mail::to($job->customer->email)->send(new DynamicMail(
                $job,
                "job_deleted_by_garage_owner"
            ));
        }
            return response()->json($job, 200);
        } catch(Exception $e){

        return $this->sendError($e,500,$request);
        }
    }







      /**
        *
     * @OA\Patch(
     *      path="/v1.0/jobs/payment",
     *      operationId="addPayment",
     *      tags={"job_management.payment"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to add payment",
     *      description="This method is to add payment",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
   * *   required={"job_id","garage_id","payments"},
   * *    @OA\Property(property="job_id", type="number", format="number",example="1"),
     *  * *    @OA\Property(property="garage_id", type="number", format="number",example="1"),
 *  * *    @OA\Property(property="payments", type="string", format="array",example={
 * {"payment_type_id":1,"amount":50},
 *  * {"payment_type_id":1,"amount":60},
 * }),

     *
     *
     *         ),
     *  * *

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

    public function addPayment(JobPaymentCreateRequest $request)
    {
        try{
            $this->storeActivity($request,"");
   return  DB::transaction(function () use($request) {

    if(!$request->user()->hasPermissionTo('job_update')){
        return response()->json([
           "message" => "You can not perform this action"
        ],401);
   }
    $updatableData = $request->validated();
    if (!$this->garageOwnerCheck($updatableData["garage_id"])) {
        return response()->json([
            "message" => "you are not the owner of the garage or the requested garage does not exist."
        ], 401);
    }


        $job  = Job::where([
            "id" => $updatableData["job_id"],
            "garage_id" =>  $updatableData["garage_id"]
            ])
            ->first();

        if(!$job){
                    return response()->json([
                "message" => "job not found"
                    ], 404);
                }
         $total_payable = $job->price;

         if(!empty($job->discount_type) && !empty($job->discount_amount)) {
            $discount_type = $job->discount_type;
            $discount_amount = $job->discount_amount;
              if($discount_type = "fixed") {
                $total_payable -= $discount_amount;
              }
              else if($discount_type = "percentage") {
                $total_payable -= (($total_payable/100) * $discount_amount);
              }
         }





            $payments = collect($updatableData["payments"]);
            $payment_amount =  $payments->sum("amount");

          $job_payment_amount =  JobPayment::where([
                "job_id" => $job->id
            ])
            ->sum("amount");

            $total_payment = $job_payment_amount + $payment_amount;



            if($total_payable < $total_payment){
                return response([
                    "payment is greater than payable"
                ], 409);
            }

  foreach($payments->all() as $payment){
    JobPayment::create([
        "job_id"=>$job->id,
        "payment_type_id"=>$payment["payment_type_id"],
        "amount"=>$payment["amount"],
    ]);
  }
  if($total_payable == $total_payment){
    Job::where([
        "id" => $payment->job_id
    ])
    ->update([
       "payment_status" => "complete"
    ]);
  }







    return response($job, 201);
});


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }



  /**
        *
     * @OA\Delete(
     *      path="/v1.0/jobs/payment/{garage_id}{id}",
     *
     *      operationId="deletePaymentById",
     *      tags={"job_management.payment"},
    *       security={
     *           {"bearerAuth": {}}
     *       },
     *  *  *      @OA\Parameter(
     *         name="garage_id",
     *         in="path",
     *         description="garage_id",
     *         required=true,
     *  example="6"
     *      ),
     *  *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="6"
     *      ),
     *      summary="This method is to delete payment by id",
     *      description="This method is to delete payment by id",
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

    public function deletePaymentById($garage_id,$id,Request $request)
    {
        try{
            $this->storeActivity($request,"");
   return  DB::transaction(function () use(&$id,&$garage_id,&$request) {

    if(!$request->user()->hasPermissionTo('job_update')){
        return response()->json([
           "message" => "You can not perform this action"
        ],401);
   }
   if (!$this->garageOwnerCheck($garage_id)) {
    return response()->json([
        "message" => "you are not the owner of the garage or the requested garage does not exist."
    ], 401);
}



    $payment = JobPayment::leftJoin('jobs', 'job_payments.job_id', '=', 'jobs.id')
    ->where([
         "jobs.garage_id" => $garage_id,
        "job_payments.id" => $id
    ])
    ->first();
     if(!$payment){
        return response()->json([
    "message" => "payment not found"
        ], 404);
    }
    Job::where([
        "id" => $payment->job_id
    ])
    ->update([
       "payment_status" => "due"
    ]);
    $payment->delete();




    return response()->json(["ok" => true], 200);
});


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }














}
