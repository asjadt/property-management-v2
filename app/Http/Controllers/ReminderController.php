<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReminderCreateRequest;
use App\Http\Requests\ReminderUpdateRequest;

use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\Reminder;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReminderController extends Controller
{
    use ErrorUtil, UserActivityUtil;



    /**
     *
     * @OA\Post(
     *      path="/v1.0/reminders",
     *      operationId="createReminder",
     *      tags={"reminders"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store reminder",
     *      description="This method is to store reminder",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *
     *
 *     @OA\Property(property="title", type="string", format="string", example="Your Title"),
 *     @OA\Property(property="entity_name", type="string", format="string", example="Your Entity Name"),
 *     @OA\Property(property="duration", type="integer", format="int", example=10),
 *     @OA\Property(property="duration_unit", type="string", format="string", example="days", enum={"days", "weeks", "months"}),
 *     @OA\Property(property="send_time", type="string", format="string", example="before_expiry", enum={"before_expiry", "after_expiry"}),
 *     @OA\Property(property="frequency_after_first_reminder", type="integer", format="int", example=2),
 *  * *     @OA\Property(property="reminder_limit", type="integer", format="int", example=2),
 *     @OA\Property(property="keep_sending_until_update", type="boolean", format="boolean", example=true)
 *
 *
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

    public function createReminder(ReminderCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            return DB::transaction(function () use ($request) {



                $request_data = $request->validated();


                $request_data["is_active"] = true;
                $request_data["created_by"] = $request->user()->id;

                $reminder =  Reminder::create($request_data);



                return response($reminder, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Put(
     *      path="/v1.0/reminders",
     *      operationId="updateReminder",
     *      tags={"reminders"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update reminder ",
     *      description="This method is to update reminder",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
*      @OA\Property(property="id", type="number", format="number", example="Updated Christmas"),
 *     @OA\Property(property="title", type="string", format="string", example="Your Title"),
 *     @OA\Property(property="entity_name", type="string", format="string", example="Your Entity Name"),
 *     @OA\Property(property="duration", type="integer", format="int", example=10),
 *     @OA\Property(property="duration_unit", type="string", format="string", example="days", enum={"days", "weeks", "months"}),
 *     @OA\Property(property="send_time", type="string", format="string", example="before_expiry", enum={"before_expiry", "after_expiry"}),
 *     @OA\Property(property="frequency_after_first_reminder", type="integer", format="int", example=2),
 * *     @OA\Property(property="reminder_limit", type="integer", format="int", example=2),
 *     @OA\Property(property="keep_sending_until_update", type="boolean", format="boolean", example=true)

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

    public function updateReminder(ReminderUpdateRequest $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");
            return DB::transaction(function () use ($request) {




                $created_by =  auth()->user()->id;
                $request_data = $request->validated();





                $reminder  =  Reminder::where(
                    [
                        "id" => $request_data["id"],
                        "created_by" => $created_by
                    ]
                )
                ->first();

                if (!$reminder) {
                    return response()->json([
                        "message" => "something went wrong."
                    ], 500);
                }
                $reminder->fill($request_data);
                $reminder->save();

                return response($reminder, 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }




    /**
     *
     * @OA\Get(
     *      path="/v1.0/reminders",
     *      operationId="getReminders",
     *      tags={"reminders"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *              @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="per_page",
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
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),

     *      summary="This method is to get reminders  ",
     *      description="This method is to get reminders ",
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

    public function getReminders(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");



            $created_by =  auth()->user()->id;

            $reminders = Reminder::where(
                [
                    "reminders.created_by" => $created_by
                ]
            )
                ->when(!empty($request->search_key), function ($query) use ($request) {
                    return $query->where(function ($query) use ($request) {
                        $term = $request->search_key;
                        // $query->where("reminders.name", "like", "%" . $term . "%")
                        //     ->orWhere("reminders.description", "like", "%" . $term . "%");
                    });
                })
                //    ->when(!empty($request->product_category_id), function ($query) use ($request) {
                //        return $query->where('product_category_id', $request->product_category_id);
                //    })
                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('reminders.created_at', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('reminders.created_at', "<=", ($request->end_date . ' 23:59:59'));
                })
                ->when(!empty($request->order_by) && in_array(strtoupper($request->order_by), ['ASC', 'DESC']), function ($query) use ($request) {
                    return $query->orderBy("reminders.id", $request->order_by);
                }, function ($query) {
                    return $query->orderBy("reminders.id", "DESC");
                })
                ->when(!empty($request->per_page), function ($query) use ($request) {
                    return $query->paginate($request->per_page);
                }, function ($query) {
                    return $query->get();
                });



            return response()->json($reminders, 200);

        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/reminders/{id}",
     *      operationId="getReminderById",
     *      tags={"reminders"},
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
     *      summary="This method is to get reminder by id",
     *      description="This method is to get reminder by id",
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


    public function getReminderById($id, Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");



            $created_by =  auth()->user()->id;

            $reminder =  Reminder::where([
                "id" => $id,
                "created_by" => $created_by
            ])
                ->first();

            if (!$reminder) {
                return response()->json([
                    "message" => "no data found"
                ], 404);
            }

            return response()->json($reminder, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }



    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/reminders/{ids}",
     *      operationId="deleteRemindersByIds",
     *      tags={"reminders"},
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
     *      summary="This method is to delete reminder by id",
     *      description="This method is to delete reminder by id",
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

    public function deleteRemindersByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity","DUMMY description");

            $business = Business::where([
                "owner_id" => request()->user()->id
            ])->first();

            if (!$business) {
                return response()->json([
                    "message" => "you don't have a valid business"
                ], 401);
            }

            if (!($business->pin == request()->header("pin"))) {
                return response()->json([
                    "message" => "invalid pin"
                ], 401);
            }

            $created_by =  auth()->user()->id;
            $idsArray = explode(',', $ids);
            $existingIds = Reminder::where([
                "created_by" => $created_by
            ])
                ->whereIn('id', $idsArray)
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

            Reminder::destroy($existingIds);



            return response()->json(["message" => "data deleted sussfully","deleted_ids" => $existingIds], 200);


        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
