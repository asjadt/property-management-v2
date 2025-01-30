<?php





namespace App\Http\Controllers;

use App\Http\Requests\PropertyNoteCreateRequest;
use App\Http\Requests\PropertyNoteUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\PropertyNote;
use App\Models\DisabledPropertyNote;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyNoteController extends Controller
{

    use ErrorUtil, UserActivityUtil, BasicUtil;


    /**
     *
     * @OA\Post(
     * path="/v1.0/property-notes",
     * operationId="createPropertyNote",
     * tags={"property_notes"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to store property notes",
     * description="This method is to store property notes",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="title", type="string", format="string", example="title"),
     * @OA\Property(property="description", type="string", format="string", example="description"),
     * @OA\Property(property="property_id", type="string", format="string", example="property_id"),
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

    public function createPropertyNote(PropertyNoteCreateRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");



            $request_data = $request->validated();


            $request_data["created_by"] = auth()->user()->id;


            $property_note = PropertyNote::create($request_data);



            DB::commit();
            return response($property_note, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Put(
     * path="/v1.0/property-notes",
     * operationId="updatePropertyNote",
     * tags={"property_notes"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to update property notes ",
     * description="This method is to update property notes ",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="title", type="string", format="string", example="title"),
     * @OA\Property(property="description", type="string", format="string", example="description"),
     * @OA\Property(property="property_id", type="string", format="string", example="property_id"),
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

    public function updatePropertyNote(PropertyNoteUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $request_data = $request->validated();



            $property_note_query_params = [
                "id" => $request_data["id"],
            ];

            $property_note =
                PropertyNote::where($property_note_query_params)->first();

            if ($property_note) {
                $property_note->fill($request_data);
                $property_note->save();
            } else {
                return response()->json([
                    "message" => "something went wrong."
                ], 500);
            }



            DB::commit();
            return response($property_note, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }




    public function query_filters($query)
    {



        return $query->where('property_notes.created_by', auth()->user()->id)

            ->when(request()->filled("title"), function ($query) {
                return $query->where(
                    'property_notes.title',
                    request()->input("title")
                );
            })
            ->when(request()->filled("description"), function ($query) {
                return $query->where(
                    'property_notes.description',
                    request()->input("description")
                );
            })

            ->when(request()->filled("property_ids"), function ($query) {
                return $query->whereHas('property', function ($q) {
                    $property_ids = explode(',', request()->input("property_ids"));
                    $q->whereIn('properties.id', $property_ids);
                });
            })

            ->when(request()->filled("search_key"), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->input("search_key");
                    $query

                        ->orWhere("property_notes.title", "like", "%" . $term . "%")
                        ->where("property_notes.description", "like", "%" . $term . "%")
                    ;
                });
            })


            ->when(request()->filled("start_date"), function ($query) {
                return $query->whereDate('property_notes.created_at', ">=", request()->input("start_date"));
            })
            ->when(request()->filled("end_date"), function ($query) {
                return $query->whereDate('property_notes.created_at', "<=", request()->input("end_date"));
            });
    }



    /**
     *
     * @OA\Get(
     * path="/v1.0/property-notes",
     * operationId="getPropertyNotes",
     * tags={"property_notes"},
     * security={
     * {"bearerAuth": {}}
     * },

     * @OA\Parameter(
     * name="title",
     * in="query",
     * description="title",
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
     * name="property_ids",
     * in="query",
     * description="property_id",
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




     * summary="This method is to get property notes ",
     * description="This method is to get property notes ",
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

    public function getPropertyNotes(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");



            $query = PropertyNote::query();
            $query = $this->query_filters($query);
            $property_notes = $this->retrieveData($query, "id", "property_notes");




            return response()->json($property_notes, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Delete(
     * path="/v1.0/property-notes/{ids}",
     * operationId="deletePropertyNotesByIds",
     * tags={"property_notes"},
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
     * summary="This method is to delete property note by id",
     * description="This method is to delete property note by id",
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

    public function deletePropertyNotesByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $idsArray = explode(',', $ids);
            $existingIds = PropertyNote::whereIn('id', $idsArray)
                ->where('property_notes.created_by', auth()->user()->id)

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



            PropertyNote::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
