<?php





namespace App\Http\Controllers;

use App\Http\Requests\PropertyInventoryCreateRequest;
use App\Http\Requests\PropertyInventoryUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\PropertyInventory;
use App\Models\DisabledPropertyInventory;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyInventoryController extends Controller
{

    use ErrorUtil, UserActivityUtil, BasicUtil;


    /**
     *
     * @OA\Post(
     * path="/v1.0/property-inventories",
     * operationId="createPropertyInventory",
     * tags={"property_inventories"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to store property inventories",
     * description="This method is to store property inventories",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="item_name", type="string", format="string", example="item_name"),
     * @OA\Property(property="item_location", type="string", format="string", example="item_location"),
     * @OA\Property(property="item_quantity", type="string", format="string", example="item_quantity"),
     * @OA\Property(property="item_condition", type="string", format="string", example="item_condition"),
     * @OA\Property(property="item_details", type="string", format="string", example="item_details"),
     * @OA\Property(property="property_id", type="string", format="string", example="property_id"),
     * @OA\Property(property="files", type="string", format="string", example="files"),
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

    public function createPropertyInventory(PropertyInventoryCreateRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");



            $request_data = $request->validated();

            $request_data["created_by"] = auth()->user()->id;


            $property_inventory = PropertyInventory::create($request_data);


            DB::commit();
            return response($property_inventory, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Put(
     * path="/v1.0/property-inventories",
     * operationId="updatePropertyInventory",
     * tags={"property_inventories"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to update property inventories ",
     * description="This method is to update property inventories ",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="item_name", type="string", format="string", example="item_name"),
     * @OA\Property(property="item_location", type="string", format="string", example="item_location"),
     * @OA\Property(property="item_quantity", type="string", format="string", example="item_quantity"),
     * @OA\Property(property="item_condition", type="string", format="string", example="item_condition"),
     * @OA\Property(property="item_details", type="string", format="string", example="item_details"),
     * @OA\Property(property="property_id", type="string", format="string", example="property_id"),
     * @OA\Property(property="files", type="string", format="string", example="files"),
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

    public function updatePropertyInventory(PropertyInventoryUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $request_data = $request->validated();

            $property_inventory_query_params = [
                "id" => $request_data["id"],
            ];

            $property_inventory =
                PropertyInventory::where($property_inventory_query_params)->first();

            if ($property_inventory) {
                $property_inventory->fill(collect($request_data)->only([

                    "item_name",
                    "item_location",
                    "item_quantity",
                    "item_condition",
                    "item_details",
                    "property_id",
                    "files",
                    // "is_default",
                    // "is_active",
                    // "business_id",
                    // "created_by"
                ])->toArray());
                $property_inventory->save();
            } else {
                return response()->json([
                    "message" => "something went wrong."
                ], 500);
            }


            DB::commit();

            return response($property_inventory, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }




    public function query_filters($query)
    {

        return $query->where('property_inventories.created_by', auth()->user()->id)

        ->when(request()->filled("property_ids"), function ($query) {
            return $query->whereHas("tenancy_agreement", function ($query) {
                $property_ids = explode(',', request()->input("property_ids"));
                $query->whereIn("property_inventories.property_id", $property_ids);
            });
        })
            ->when(request()->filled("item_name"), function ($query) {
                return $query->where(
                    'property_inventories.item_name',
                    request()->input("item_name")
                );
            })
            ->when(request()->filled("item_location"), function ($query) {
                return $query->where(
                    'property_inventories.item_location',
                    request()->input("item_location")
                );
            })
            ->when(request()->filled("item_condition"), function ($query) {
                return $query->where(
                    'property_inventories.item_condition',
                    request()->input("item_condition")
                );
            })
            ->when(request()->filled("item_details"), function ($query) {
                return $query->where(
                    'property_inventories.item_details',
                    request()->input("item_details")
                );
            })
            ->when(request()->filled("search_key"), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->input("search_key");
                    $query
                        ->orWhere("property_inventories.item_name", "like", "%" . $term . "%")
                        ->where("property_inventories.item_location", "like", "%" . $term . "%")
                        ->orWhere("property_inventories.item_condition", "like", "%" . $term . "%")
                        ->orWhere("property_inventories.item_details", "like", "%" . $term . "%")
                    ;
                });
            })
            ->when(request()->filled("start_date"), function ($query) {
                return $query->whereDate('property_inventories.created_at', ">=", request()->input("start_date"));
            })
            ->when(request()->filled("end_date"), function ($query) {
                return $query->whereDate('property_inventories.created_at', "<=", request()->input("end_date"));
            });


    }



    /**
     *
     * @OA\Get(
     * path="/v1.0/property-inventories",
     * operationId="getPropertyInventories",
     * tags={"property_inventories"},
     * security={
     * {"bearerAuth": {}}
     * },

     * @OA\Parameter(
     * name="item_name",
     * in="query",
     * description="item_name",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="item_location",
     * in="query",
     * description="item_location",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="item_condition",
     * in="query",
     * description="item_condition",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="item_details",
     * in="query",
     * description="item_details",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="per_page",
     * required=false,
     * example=""
     * ),

     * @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=false,
     * example="ASC"
     * ),
     * * @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=false,
     * example=""
     * ),
     * summary="This method is to get property inventories ",
     * description="This method is to get property inventories ",
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

    public function getPropertyInventories(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");



            $query = PropertyInventory::query();
            $query = $this->query_filters($query);
            $property_inventories = $this->retrieveData($query, "id", "property_inventories");



            return response()->json($property_inventories, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Delete(
     * path="/v1.0/property-inventories/{ids}",
     * operationId="deletePropertyInventoriesByIds",
     * tags={"property_inventories"},
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
     * summary="This method is to delete property inventory by id",
     * description="This method is to delete property inventory by id",
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

    public function deletePropertyInventoriesByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $idsArray = explode(',', $ids);
            $existingIds = PropertyInventory::whereIn('id', $idsArray)
                ->where('property_inventories.created_by', auth()->user()->id)
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





            PropertyInventory::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
