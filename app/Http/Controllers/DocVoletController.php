<?php





namespace App\Http\Controllers;

use App\Http\Requests\DocVoletCreateRequest;
use App\Http\Requests\DocVoletUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\DocVolet;
use App\Models\DisabledDocVolet;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocVoletController extends Controller
{

    use ErrorUtil, UserActivityUtil, BasicUtil;


    /**
     *
     * @OA\Post(
     * path="/v1.0/doc-volets",
     * operationId="createDocVolet",
     * tags={"doc_volets"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to store doc volets",
     * description="This method is to store doc volets",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="title", type="string", format="string", example="title"),
     * @OA\Property(property="description", type="string", format="string", example="description"),
     * @OA\Property(property="date", type="string", format="string", example="date"),
     * @OA\Property(property="note", type="string", format="string", example="note"),
     * @OA\Property(property="files", type="string", format="string", example="files"),
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

    public function createDocVolet(DocVoletCreateRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");



            $request_data = $request->validated();

            $request_data["created_by"] = auth()->user()->id;


            $doc_volet = DocVolet::create($request_data);



            DB::commit();
            return response($doc_volet, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Put(
     * path="/v1.0/doc-volets",
     * operationId="updateDocVolet",
     * tags={"doc_volets"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to update doc volets ",
     * description="This method is to update doc volets ",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="title", type="string", format="string", example="title"),
     * @OA\Property(property="description", type="string", format="string", example="description"),
     * @OA\Property(property="date", type="string", format="string", example="date"),
     * @OA\Property(property="note", type="string", format="string", example="note"),
     * @OA\Property(property="files", type="string", format="string", example="files"),
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

    public function updateDocVolet(DocVoletUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $request_data = $request->validated();


            $doc_volet_query_params = [
                "id" => $request_data["id"],
            ];

            $doc_volet =
                DocVolet::where($doc_volet_query_params)->first();

            if ($doc_volet) {
                $doc_volet->fill(collect($request_data)->only([

                    "title",
                    "description",
                    "date",
                    "note",
                    "files",
                    "property_id",
                    // "is_default",
                    // "is_active",
                    // "business_id",
                    // "created_by"
                ])->toArray());
                $doc_volet->save();
            } else {
                return response()->json([
                    "message" => "something went wrong."
                ], 500);
            }



            DB::commit();
            return response($doc_volet, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }




    public function query_filters($query)
    {


        return $query->where('doc_volets.created_by', auth()->user()->id)

        ->when(request()->filled('property_ids'), function ($q)  {
            $property_ids = explode(',', request()->input('property_ids'));
            $q->whereIn('doc_volets.property_id', $property_ids);
        })
            ->when(request()->filled("title"), function ($query) {
                return $query->where(
                    'doc_volets.title',
                    request()->input("title")
                );
            })
            ->when(request()->filled("description"), function ($query) {
                return $query->where(
                    'doc_volets.description',
                    request()->input("description")
                );
            })
            ->when(request()->filled("date"), function ($query) {
                return $query->where(
                    'doc_volets.date',
                    request()->input("date")
                );
            })
            ->when(request()->filled("note"), function ($query) {
                return $query->where(
                    'doc_volets.note',
                    request()->input("note")
                );
            })

            ->when(request()->filled("search_key"), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->input("search_key");
                    $query

                        ->orWhere("doc_volets.title", "like", "%" . $term . "%")
                        ->where("doc_volets.description", "like", "%" . $term . "%")
                        ->orWhere("doc_volets.date", "like", "%" . $term . "%")
                        ->orWhere("doc_volets.note", "like", "%" . $term . "%")
                    ;
                });
            })


            ->when(request()->filled("start_date"), function ($query) {
                return $query->whereDate('doc_volets.created_at', ">=", request()->input("start_date"));
            })
            ->when(request()->filled("end_date"), function ($query) {
                return $query->whereDate('doc_volets.created_at', "<=", request()->input("end_date"));
            });
    }



    /**
     *
     * @OA\Get(
     * path="/v1.0/doc-volets",
     * operationId="getDocVolets",
     * tags={"doc_volets"},
     * security={
     * {"bearerAuth": {}}
     * },


          * @OA\Parameter(
     * name="property_ids",
     * in="query",
     * description="property_ids",
     * required=false,
     * example=""
     * ),
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
     * name="date",
     * in="query",
     * description="date",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="note",
     * in="query",
     * description="note",
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




     * summary="This method is to get doc volets ",
     * description="This method is to get doc volets ",
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

    public function getDocVolets(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");



            $query = DocVolet::query();
            $query = $this->query_filters($query);
            $doc_volets = $this->retrieveData($query, "id", "doc_volets");


            return response()->json($doc_volets, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Delete(
     * path="/v1.0/doc-volets/{ids}",
     * operationId="deleteDocVoletsByIds",
     * tags={"doc_volets"},
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
     * summary="This method is to delete doc volet by id",
     * description="This method is to delete doc volet by id",
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

    public function deleteDocVoletsByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $idsArray = explode(',', $ids);
            $existingIds = DocVolet::whereIn('id', $idsArray)
                ->where('doc_volets.created_by', auth()->user()->id)

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

            DocVolet::destroy($existingIds);

            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
