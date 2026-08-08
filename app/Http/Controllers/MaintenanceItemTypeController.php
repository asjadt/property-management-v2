<?php





namespace App\Http\Controllers;

use App\Http\Requests\MaintenanceItemTypeRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\MaintenanceItemType;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaintenanceItemTypeController extends Controller
{

    use ErrorUtil, UserActivityUtil, BasicUtil;


    /**
     *
     * @OA\Post(
     * path="/v1.0/maintenance-item-types",
     * operationId="createMaintenanceItemType",
     * tags={"maintenance_item_types"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to store maintenance item types",
     * description="This method is to store maintenance item types",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", format="string", example="name"),
     *
     *
     *
     * ),
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function createMaintenanceItemType(MaintenanceItemTypeRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // GET AUTHENTICATED USER
            /** @var \App\Models\User $authUser */
            $authUser = Auth::user();

            $request_data = $request->validated();
            $request_data["is_active"] = 1;
            $request_data["created_by"] = $authUser->id;

            // SET DEFAULT STATUS AND BUSINESS ID BASED ON ROLE
            if ($authUser->hasRole("superadmin")) {
                $request_data["is_default"] = 1;
                $request_data["business_id"] = null;
            } else {
                $request_data["is_default"] = 0;
                $request_data["business_id"] = $authUser->business_id;
            }

            // CREATE MAINTENANCE ITEM TYPE
            $maintenance_item_type = MaintenanceItemType::create($request_data);

            DB::commit();
            return response($maintenance_item_type, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Put(
     * path="/v1.0/maintenance-item-types",
     * operationId="updateMaintenanceItemType",
     * tags={"maintenance_item_types"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to update maintenance item types ",
     * description="This method is to update maintenance item types ",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="name", type="string", format="string", example="name"),
     *
     * ),
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function updateMaintenanceItemType(MaintenanceItemTypeRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // GET AUTHENTICATED USER
            /** @var \App\Models\User $authUser */
            $authUser = Auth::user();

            $request_data = $request->validated();

            // CHECK RECORD OWNERSHIP
            $maintenance_item_type = MaintenanceItemType::find($request_data["id"]);

            if (!$maintenance_item_type) {
                return response()->json([
                    "message" => "no maintenance item type found"
                ], 404);
            }

            if ($maintenance_item_type->is_default && !$authUser->hasRole("superadmin")) {
                return response()->json([
                    'success' => false,
                    "message" => "you can not update default maintenance item type"
                ], 403);
            }

            if ($maintenance_item_type->business_id !== $authUser->business_id) {
                return response()->json([
                    'success' => false,
                    "message" => "you can not update maintenance item type of another business"
                ], 403);
            }

            $maintenance_item_type->update($request_data);

            DB::commit();
            return response($maintenance_item_type, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Put(
     * path="/v1.0/maintenance-item-types/toggle-active",
     * operationId="toggleActiveMaintenanceItemType",
     * tags={"maintenance_item_types"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to toggle maintenance item types",
     * description="This method is to toggle maintenance item types",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(

     * @OA\Property(property="id", type="string", format="number",example="1"),
     *
     * ),
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function toggleActiveMaintenanceItemType(GetIdRequest $request)
    {

        try {

            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // GET AUTHENTICATED USER
            /** @var \App\Models\User $authUser */
            $authUser = Auth::user();

            $request_data = $request->validated();

            // CHECK RECORD OWNERSHIP
            $maintenance_item_type = MaintenanceItemType::find($request_data["id"]);

            if (!$maintenance_item_type) {
                return response()->json([
                    "message" => "no data found"
                ], 404);
            }

            if ($maintenance_item_type->is_default && !$authUser->hasRole("superadmin")) {
                return response()->json([
                    'success' => false,
                    "message" => "you can not update default maintenance item type"
                ], 403);
            }

            if ($maintenance_item_type->business_id !== $authUser->business_id) {
                return response()->json([
                    'success' => false,
                    "message" => "you can not update maintenance item type of another business"
                ], 403);
            }

            $maintenance_item_type->update([
                'is_active' => !$maintenance_item_type->is_active
            ]);

            return response()->json(['message' => 'maintenance item type status updated successfully'], 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }







    /**
     *
     * @OA\Get(
     * path="/v1.0/maintenance-item-types",
     * operationId="getMaintenanceItemTypes",
     * tags={"maintenance_item_types"},
     * security={
     * {"bearerAuth": {}}
     * },

     * @OA\Parameter(
     * name="name",
     * in="query",
     * description="name",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="per_page",
     * required=true,
     * example="6"
     * ),

     * @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=true,
     * example="1"
     * ),
     * @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * * @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * * @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=true,
     * example="search_key"
     * ),
     * * @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),
     * * @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=true,
     * example="ASC"
     * ),
     * summary="This method is to get maintenance item types ",
     * description="This method is to get maintenance item types ",
     *

     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    /**
     *
     * @OA\Get(
     *      path="/v1.0/maintenance-item-types/{id}",
     *      operationId="getMaintenanceItemTypeById",
     *      tags={"maintenance_item_types"},
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
     *      summary="This method is to get a maintenance item type by id",
     *      description="This method is to get a maintenance item type by id",
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
    public function getMaintenanceItemTypeById($id, Request $request)
    {
        try {
            $this->storeActivity($request,"");

            $maintenance_item_type = MaintenanceItemType::maintenanceItemQuery()
                ->where("id", $id)
                ->first();

            if(!$maintenance_item_type) {
                return response()->json([
                    'success'=>false,
                    "message" => "no maintenance item type found"
                ],404);
            }

            return response()->json($maintenance_item_type, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500,$request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/maintenance-item-types",
     *      operationId="getMaintenanceItemTypes",
     *      tags={"maintenance_item_types"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *              @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="perPage",
     *         required=true,
     *  example="6"
     *      ),
     *      * * @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * * @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=true,
     * example="2019-06-29"
     * ),
     * * @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=true,
     * example="search_key"
     * ),
     * * @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),
     * * @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=true,
     * example="ASC"
     * ),
     * summary="This method is to get maintenance item types ",
     * description="This method is to get maintenance item types ",
     *

     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function getMaintenanceItemTypes(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $query = MaintenanceItemType::maintenanceItemQuery();
            $maintenance_item_types = $this->retrieveData($query, "id", "maintenance_item_types");

            return response()->json($maintenance_item_types, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Delete(
     * path="/v1.0/maintenance-item-types/{ids}",
     * operationId="deleteMaintenanceItemTypesByIds",
     * tags={"maintenance_item_types"},
     * security={
     * {"bearerAuth": {}}
     * },
     * @OA\Parameter(
     * name="ids",
     * in="path",
     * description="ids",
     * required=true,
     * example="1,2,3"
     * ),
     * summary="This method is to delete maintenance item type by id",
     * description="This method is to delete maintenance item type by id",
     *

     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function deleteMaintenanceItemTypesByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            // GET AUTHENTICATED USER
            /** @var \App\Models\User $authUser */
            $authUser = Auth::user();

            $idsArray = explode(',', $ids);

            // FETCH ALL REQUESTED RECORDS
            $items = MaintenanceItemType::whereIn('id', $idsArray)->get();

            if ($items->count() !== count($idsArray)) {
                return response()->json([
                    'success' => false,
                    "message" => "Some or all of the specified data do not exist."
                ], 404);
            }

            foreach ($items as $item) {
                if ($item->is_default && !$authUser->hasRole("superadmin")) {
                    return response()->json([
                        'success' => false,
                        "message" => "You can not perform this action on default items"
                    ], 403);
                }

                if (!$authUser->hasRole("superadmin") && $item->business_id !== $authUser->business_id) {
                    return response()->json([
                        'success' => false,
                        "message" => "You can not perform this action on other business items"
                    ], 403);
                }
            }

            // DELETE RECORDS
            MaintenanceItemType::destroy($idsArray);

            return response()->json([
                "success" => true,
                "message" => "data deleted successfully",
                "deleted_ids" => $idsArray
            ], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
