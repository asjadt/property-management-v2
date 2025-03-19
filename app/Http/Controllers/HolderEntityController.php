<?php





namespace App\Http\Controllers;

use App\Http\Requests\HolderEntityCreateRequest;
use App\Http\Requests\HolderEntityUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\HolderEntity;
use App\Models\DisabledHolderEntity;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HolderEntityController extends Controller
{

    use ErrorUtil, UserActivityUtil, BasicUtil;


    /**
     *
     * @OA\Post(
     * path="/v1.0/holder-entities",
     * operationId="createHolderEntity",
     * tags={"holder_entities"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to store holder entities",
     * description="This method is to store holder entities",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="name", type="string", format="string", example="name"),
     * @OA\Property(property="description", type="string", format="string", example="description"),
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

    public function createHolderEntity(HolderEntityCreateRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");



            $request_data = $request->validated();

            $request_data["is_active"] = 1;
            $request_data["created_by"] = auth()->user()->id;






            $holder_entity = HolderEntity::create($request_data);



            DB::commit();
            return response($holder_entity, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Put(
     * path="/v1.0/holder-entities",
     * operationId="updateHolderEntity",
     * tags={"holder_entities"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to update holder entities ",
     * description="This method is to update holder entities ",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="name", type="string", format="string", example="name"),
     * @OA\Property(property="description", type="string", format="string", example="description"),
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

    public function updateHolderEntity(HolderEntityUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $request_data = $request->validated();



            $holder_entity_query_params = [
                "id" => $request_data["id"],
            ];

            $holder_entity =
                HolderEntity::where($holder_entity_query_params)->first();

            if ($holder_entity) {
                $holder_entity->fill($request_data);
                $holder_entity->save();
            } else {
                return response()->json([
                    "message" => "something went wrong."
                ], 500);
            }



            DB::commit();
            return response($holder_entity, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Put(
     * path="/v1.0/holder-entities/toggle-active",
     * operationId="toggleActiveHolderEntity",
     * tags={"holder_entities"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to toggle holder entities",
     * description="This method is to toggle holder entities",
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

    public function toggleActiveHolderEntity(GetIdRequest $request)
    {

        try {

            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            if (!$request->user()->hasPermissionTo('holder_entity_activate')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $request_data = $request->validated();

            $holder_entity = HolderEntity::where([
                "id" => $request_data["id"],
            ])
                ->first();
            if (!$holder_entity) {

                return response()->json([
                    "message" => "no data found"
                ], 404);
            }

            $holder_entity->update([
                'is_active' => !$holder_entity->is_active
            ]);




            return response()->json(['message' => 'holder entity status updated successfully'], 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }



    public function query_filters($query)
    {

        return $query->where('holder_entities.created_by', auth()->user()->id)
            ->when(request()->filled("name"), function ($query) {
                return $query->where(
                    'holder_entities.name',
                    request()->input("name")
                );
            })
            ->when(request()->filled("description"), function ($query) {
                return $query->where(
                    'holder_entities.description',
                    request()->input("description")
                );
            })

            ->when(request()->filled("search_key"), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->input("search_key");
                    $query

                        ->orWhere("holder_entities.name", "like", "%" . $term . "%")
                        ->where("holder_entities.description", "like", "%" . $term . "%")
                    ;
                });
            })

            ->when(request()->filled("start_date"), function ($query) {
                return $query->whereDate('holder_entities.created_at', ">=", request()->input("start_date"));
            })
            ->when(request()->filled("end_date"), function ($query) {
                return $query->whereDate('holder_entities.created_at', "<=", request()->input("end_date"));
            });
    }



    /**
     *
     * @OA\Get(
     * path="/v1.0/holder-entities",
     * operationId="getHolderEntities",
     * tags={"holder_entities"},
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
     * name="description",
     * in="query",
     * description="description",
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




     * summary="This method is to get holder entities ",
     * description="This method is to get holder entities ",
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

    public function getHolderEntities(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");



            $query = HolderEntity::query();
            $query = $this->query_filters($query);
            $holder_entities = $this->retrieveData($query, "id", "holder_entities");




            return response()->json($holder_entities, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Delete(
     * path="/v1.0/holder-entities/{ids}",
     * operationId="deleteHolderEntitiesByIds",
     * tags={"holder_entities"},
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
     * summary="This method is to delete holder entity by id",
     * description="This method is to delete holder entity by id",
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

    public function deleteHolderEntitiesByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $idsArray = explode(',', $ids);
            $existingIds = HolderEntity::whereIn('id', $idsArray)
                ->where('holder_entities.created_by', auth()->user()->id)

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



            HolderEntity::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);


        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
