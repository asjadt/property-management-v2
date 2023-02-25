<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingToJobRequest;
use App\Http\Requests\JobPaymentCreateRequest;
use App\Http\Requests\JobStatusChangeRequest;
use App\Http\Requests\JobUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\GarageUtil;
use App\Models\Booking;
use App\Models\BookingSubService;
use App\Models\GarageSubService;
use App\Models\Job;
use App\Models\JobPayment;
use App\Models\JobSubService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    use ErrorUtil,GarageUtil;

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
   * *   required={"booking_id","garage_id","discount_type","discount_amount","price","job_start_date","job_start_time","job_end_time","status"},
   * *    @OA\Property(property="booking_id", type="number", format="number",example="1"),
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


                if(!$booking){
                    return response()->json([
                "message" => "booking not found"
                    ], 404);
                }


                $job = Job::create([
                    "garage_id" => $booking->garage_id,
                    "customer_id" => $booking->customer_id,
                    "automobile_make_id"=> $booking->automobile_make_id,
                    "automobile_model_id"=> $booking->automobile_model_id,
                    "car_registration_no"=> $booking->car_registration_no,
                    "additional_information" => $booking->additional_information,



                    "discount_type" => $updatableData["discount_type"],
                    "discount_amount"=> $updatableData["discount_amount"],
                    "price"=>$updatableData["price"],
                    "job_start_date"=> $updatableData["job_start_date"],
                    "job_start_time"=> $updatableData["job_start_time"],
                    "job_end_time"=> $updatableData["job_end_time"],
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
                    "sub_service_id" => $booking_sub_service->sub_service_id
                 ]);

                }

                $booking->delete();


    return response([
        "ok" => true
    ], 201);
});


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500);
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
     *            required={"id","garage_id","automobile_make_id","automobile_model_id","car_registration_no","job_sub_service_ids","job_start_time","job_end_time"},
     * *    @OA\Property(property="id", type="number", format="number",example="1"),
     *  * *    @OA\Property(property="garage_id", type="number", format="number",example="1"),

     *    @OA\Property(property="automobile_make_id", type="number", format="number",example="1"),
     *    @OA\Property(property="automobile_model_id", type="number", format="number",example="1"),

     * *    @OA\Property(property="car_registration_no", type="string", format="string",example="r-00011111"),
     *  * *    @OA\Property(property="job_sub_service_ids", type="string", format="array",example={1,2,3,4}),
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

    public function updateJob(JobUpdateRequest $request)
    {
        try{
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
            "automobile_make_id",
            "automobile_model_id",

            "car_registration_no",
            "status",
            "job_start_date",
             "job_start_time",
            "job_end_time",
            "discount_type",
            "discount_amount",
            "price",

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


            foreach($updatableData["job_sub_service_ids"] as $sub_service_id) {
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
                 throw new Exception("invalid service");
                    }
                    JobSubService::create([
                        "sub_service_id" => $garage_sub_service->sub_service_id,
                        "job_id" => $job->id
                    ]);

                }






    return response($job, 201);
});


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500);
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
    return response($job, 201);
});

        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500);
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


            $jobQuery = Job::with("job_sub_services.sub_service")
            ->where([
                "garage_id" => $garage_id
            ]);

            if(!empty($request->search_key)) {
                $jobQuery = $jobQuery->where(function($query) use ($request){
                    $term = $request->search_key;
                    $query->where("car_registration_no", "like", "%" . $term . "%");
                });

            }

            if(!empty($request->start_date) && !empty($request->end_date)) {
                $jobQuery = $jobQuery->whereBetween('created_at', [
                    $request->start_date,
                    $request->end_date
                ]);

            }
            $jobs = $jobQuery->orderByDesc("id")->paginate($perPage);
            return response()->json($jobs, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
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


            $job = Job::with("job_sub_services.sub_service")
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

        return $this->sendError($e,500);
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


            return response()->json($job, 200);
        } catch(Exception $e){

        return $this->sendError($e,500);
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
        return $this->sendError($e,500);
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
        return $this->sendError($e,500);
        }
    }














}
