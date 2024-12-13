<?php





namespace App\Http\Controllers;

use App\Http\Requests\ApplicantCreateRequest;
use App\Http\Requests\ApplicantUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Applicant;
use App\Models\DisabledApplicant;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApplicantController extends Controller
{

    use ErrorUtil, UserActivityUtil, BusinessUtil;


    /**
     *
     * @OA\Post(
     *      path="/v1.0/applicants",
     *      operationId="createApplicant",
     *      tags={"applicants"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store applicants",
     *      description="This method is to store applicants",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     * @OA\Property(property="customer_name", type="string", format="string", example="customer_name"),
     * @OA\Property(property="customer_phone", type="string", format="string", example="customer_phone"),
     * @OA\Property(property="min_price", type="string", format="string", example="min_price"),
     * @OA\Property(property="max_price", type="string", format="string", example="max_price"),
     * @OA\Property(property="address_line_1", type="string", format="string", example="address_line_1"),
     * @OA\Property(property="latitude", type="string", format="string", example="latitude"),
     * @OA\Property(property="longitude", type="string", format="string", example="longitude"),
     * @OA\Property(property="radius", type="string", format="string", example="radius"),
     * @OA\Property(property="property_type", type="string", format="string", example="property_type"),
     * @OA\Property(property="no_of_beds", type="string", format="string", example="no_of_beds"),
     * @OA\Property(property="no_of_baths", type="string", format="string", example="no_of_baths"),
     * @OA\Property(property="deadline_to_move", type="string", format="string", example="deadline_to_move"),
     * @OA\Property(property="working", type="string", format="string", example="working"),
     * @OA\Property(property="job_title", type="string", format="string", example="job_title"),
     * @OA\Property(property="is_dss", type="string", format="string", example="is_dss"),
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

    public function createApplicant(ApplicantCreateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return DB::transaction(function () use ($request) {


                $request_data = $request->validated();

                $request_data["is_active"] = 1;
                $request_data["created_by"] = auth()->user()->id;

                $applicant =  Applicant::create($request_data);




                return response($applicant, 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Put(
     *      path="/v1.0/applicants",
     *      operationId="updateApplicant",
     *      tags={"applicants"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update applicants ",
     *      description="This method is to update applicants ",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *      @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="customer_name", type="string", format="string", example="customer_name"),
     * @OA\Property(property="customer_phone", type="string", format="string", example="customer_phone"),
     * @OA\Property(property="min_price", type="string", format="string", example="min_price"),
     * @OA\Property(property="max_price", type="string", format="string", example="max_price"),
     * @OA\Property(property="address_line_1", type="string", format="string", example="address_line_1"),
     * @OA\Property(property="latitude", type="string", format="string", example="latitude"),
     * @OA\Property(property="longitude", type="string", format="string", example="longitude"),
     * @OA\Property(property="radius", type="string", format="string", example="radius"),
     * @OA\Property(property="property_type", type="string", format="string", example="property_type"),
     * @OA\Property(property="no_of_beds", type="string", format="string", example="no_of_beds"),
     * @OA\Property(property="no_of_baths", type="string", format="string", example="no_of_baths"),
     * @OA\Property(property="deadline_to_move", type="string", format="string", example="deadline_to_move"),
     * @OA\Property(property="working", type="string", format="string", example="working"),
     * @OA\Property(property="job_title", type="string", format="string", example="job_title"),
     * @OA\Property(property="is_dss", type="string", format="string", example="is_dss"),
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

    public function updateApplicant(ApplicantUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");
            return DB::transaction(function () use ($request) {

                $request_data = $request->validated();

                $applicant_query_params = [
                    "id" => $request_data["id"],
                ];

                $applicant = Applicant::where($applicant_query_params)->first();

                if ($applicant) {
                    $applicant->fill(collect($request_data)->only([

                        "customer_name",
                        "customer_phone",
                        "min_price",
                        "max_price",
                        "address_line_1",
                        "latitude",
                        "longitude",
                        "radius",
                        "property_type",
                        "no_of_beds",
                        "no_of_baths",
                        "deadline_to_move",
                        "working",
                        "job_title",
                        "is_dss",
                        // "is_default",
                        // "is_active",
                        // "business_id",
                        // "created_by"
                    ])->toArray());
                    $applicant->save();
                } else {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }




                return response($applicant, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Put(
     *      path="/v1.0/applicants/toggle-active",
     *      operationId="toggleActiveApplicant",
     *      tags={"applicants"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to toggle applicants",
     *      description="This method is to toggle applicants",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(

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

    public function toggleActiveApplicant(GetIdRequest $request)
    {

        try {

            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $request_data = $request->validated();

            $applicant =  Applicant::where([
                "id" => $request_data["id"],
                "created_by" => auth()->user()->id
            ])
                ->first();
            if (!$applicant) {

                return response()->json([
                    "message" => "no data found"
                ], 404);
            }

            $applicant->update([
                'is_active' => !$applicant->is_active
            ]);




            return response()->json(['message' => 'applicant status updated successfully'], 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }



    /**
     *
     * @OA\Get(
     *      path="/v1.0/applicants",
     *      operationId="getApplicants",
     *      tags={"applicants"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *         @OA\Parameter(
     *         name="customer_name",
     *         in="query",
     *         description="customer_name",
     *         required=false,
     *  example=""
     *      ),



     *         @OA\Parameter(
     *         name="customer_phone",
     *         in="query",
     *         description="customer_phone",
     *         required=false,
     *  example=""
     *      ),





     *         @OA\Parameter(
     *         name="address_line_1",
     *         in="query",
     *         description="address_line_1",
     *         required=false,
     *  example=""
     *      ),






     *         @OA\Parameter(
     *         name="property_type",
     *         in="query",
     *         description="property_type",
     *         required=false,
     *  example=""
     *      ),



     *         @OA\Parameter(
     *         name="no_of_beds",
     *         in="query",
     *         description="no_of_beds",
     *         required=false,
     *  example=""
     *      ),



     *         @OA\Parameter(
     *         name="no_of_baths",
     *         in="query",
     *         description="no_of_baths",
     *         required=false,
     *  example=""
     *      ),
     *         @OA\Parameter(
     *         name="start_deadline_to_move",
     *         in="query",
     *         description="start_deadline_to_move",
     *         required=false,
     *  example=""
     *      ),
     *         @OA\Parameter(
     *         name="end_deadline_to_move",
     *         in="query",
     *         description="end_deadline_to_move",
     *         required=false,
     *  example=""
     *      ),
     *         @OA\Parameter(
     *         name="working",
     *         in="query",
     *         description="working",
     *         required=false,
     *  example=""
     *      ),
     *         @OA\Parameter(
     *         name="job_title",
     *         in="query",
     *         description="job_title",
     *         required=false,
     *  example=""
     *      ),
     *         @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="per_page",
     *         required=true,
     *  example="6"
     *      ),

     *     @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=true,
     * example="1"
     * ),
     *     @OA\Parameter(
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
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),
     * *  @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=true,
     * example="ASC"
     * ),




     *      summary="This method is to get applicants  ",
     *      description="This method is to get applicants ",
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

    public function getApplicants(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $created_by  = auth()->user()->id;
            $applicants = Applicant::where('applicants.created_by', $created_by)
                ->when(!empty($request->customer_name), function ($query) use ($request) {
                    return $query->where('applicants.customer_name', $request->customer_name);
                })
                ->when(!empty($request->customer_phone), function ($query) use ($request) {
                    return $query->where('applicants.customer_phone', $request->customer_phone);
                })

                ->when(!empty($request->address_line_1), function ($query) use ($request) {
                    return $query->where('applicants.address_line_1', $request->address_line_1);
                })
                ->when(!empty($request->property_type), function ($query) use ($request) {
                    return $query->where('applicants.property_type', $request->property_type);
                })
                ->when(!empty($request->no_of_beds), function ($query) use ($request) {
                    return $query->where('applicants.no_of_beds', $request->no_of_beds);
                })
                ->when(!empty($request->no_of_baths), function ($query) use ($request) {
                    return $query->where('applicants.no_of_baths', $request->no_of_baths);
                })
                ->when(!empty($request->start_deadline_to_move), function ($query) use ($request) {
                    return $query->where('applicants.deadline_to_move', ">=", $request->start_deadline_to_move);
                })
                ->when(!empty($request->end_deadline_to_move), function ($query) use ($request) {
                    return $query->where('applicants.deadline_to_move', "<=", ($request->end_deadline_to_move . ' 23:59:59'));
                })
                ->when(!empty($request->working), function ($query) use ($request) {
                    return $query->where('applicants.working', $request->working);
                })
                ->when(!empty($request->job_title), function ($query) use ($request) {
                    return $query->where('applicants.job_title', $request->job_title);
                })
                ->when(!empty($request->search_key), function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $term = $request->search_key;
                        $query
                            ->orWhere("applicants.customer_name", "like", "%" . $term . "%")
                            ->where("applicants.customer_phone", "like", "%" . $term . "%")
                            ->orWhere("applicants.address_line_1", "like", "%" . $term . "%")
                            ->orWhere("applicants.property_type", "like", "%" . $term . "%")
                            ->orWhere("applicants.no_of_beds", "like", "%" . $term . "%")
                            ->orWhere("applicants.no_of_baths", "like", "%" . $term . "%")
                            ->orWhere("applicants.working", "like", "%" . $term . "%")
                            ->orWhere("applicants.job_title", "like", "%" . $term . "%")
                        ;
                    });
                })
                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->whereDate('applicants.created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->whereDate('applicants.created_at', "<=", ($request->end_date));
                })
                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("applicants.id", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("applicants.id", "DESC");
                })
                ->when($request->filled("id"), function ($query) use ($request) {
                    return $query
                        ->where("applicants.id", $request->input("id"))
                        ->first();
                }, function ($query) {
                    return $query->when(!empty(request()->per_page), function ($query) {
                        return $query->paginate(request()->per_page);
                    }, function ($query) {
                        return $query->get();
                    });
                });

            if ($request->filled("id") && empty($applicants)) {
                throw new Exception("No data found", 404);
            }


            return response()->json($applicants, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/applicants/{ids}",
     *      operationId="deleteApplicantsByIds",
     *      tags={"applicants"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="ids",
     *         in="path",
     *         description="ids",
     *         required=true,
     *  example="1,2,3"
     *      ),
     *      summary="This method is to delete applicant by id",
     *      description="This method is to delete applicant by id",
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

    public function deleteApplicantsByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $idsArray = explode(',', $ids);
            $existingIds = Applicant::whereIn('id', $idsArray)
                ->where('applicants.created_by', auth()->user()->id)

                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();
            $nonExistingIds = array_diff($idsArray, $existingIds);

            if (!empty($nonExistingIds)) {

                return response()->json([
                    "message" => "Some or all of the specified data do not exist."
                ], 404);
            }

            Applicant::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
