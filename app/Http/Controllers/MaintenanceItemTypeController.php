<?php





namespace App\Http\Controllers;

use App\Http\Requests\MaintenanceItemTypeCreateRequest;
use App\Http\Requests\MaintenanceItemTypeUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\MaintenanceItemType;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
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

    public function createMaintenanceItemType(MaintenanceItemTypeCreateRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $request_data = $request->validated();
            $request_data["is_active"] = 1;
            $request_data["created_by"] = auth()->user()->id;

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

    public function updateMaintenanceItemType(MaintenanceItemTypeUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $request_data = $request->validated();
            $maintenance_item_type_query_params = [
                "id" => $request_data["id"],
            ];

            $maintenance_item_type =
                MaintenanceItemType::where($maintenance_item_type_query_params)->first();

            if ($maintenance_item_type) {
                $maintenance_item_type->fill(collect($request_data)->only([

                    "name",
                    // "is_default",
                    // "is_active",
                    // "business_id",
                    // "created_by"
                ])->toArray());
                $maintenance_item_type->save();
            } else {
                return response()->json([
                    "message" => "something went wrong."
                ], 500);
            }



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


            $request_data = $request->validated();

            $maintenance_item_type = MaintenanceItemType::where([
                "id" => $request_data["id"],
                "created_by" => auth()->user()->id
            ])
                ->first();
            if (!$maintenance_item_type) {

                return response()->json([
                    "message" => "no data found"
                ], 404);
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



    public function query_filters($query)
    {


        return     $query

            ->when(request()->filled("name"), function ($query) {
                return $query->where(
                    'maintenance_item_types.name',
                    request()->input("name")
                );
            })

            ->when(request()->filled("search_key"), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->input("search_key");
                    $query

                        ->orWhere("maintenance_item_types.name", "like", "%" . $term . "%");
                });
            })


            ->when(request()->filled("start_date"), function ($query) {
                return $query->whereDate('maintenance_item_types.created_at', ">=", request()->input("start_date"));
            })
            ->when(request()->filled("end_date"), function ($query) {
                return $query->whereDate('maintenance_item_types.created_at', "<=", request()->input("end_date"));
            });

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

    public function getMaintenanceItemTypes(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $query = MaintenanceItemType::query();
            $query = $this->query_filters($query);
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


            $idsArray = explode(',', $ids);
            $existingIds = MaintenanceItemType::whereIn('id', $idsArray)
                ->where('maintenance_item_types.created_by', auth()->user()->id)

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





            MaintenanceItemType::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
