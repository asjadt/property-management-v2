<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentTypeCreateRequest;
use App\Http\Requests\DocumentTypeUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\DocumentType;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocumentTypeController extends Controller
{
    use ErrorUtil, UserActivityUtil;

    /**
     *
     * @OA\Post(
     *      path="/v1.0/document-types",
     *      operationId="createDocumentType",
     *      tags={"property_management.document_type_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store document type",
     *      description="This method is to store document type",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"name","description","is_active"},
     *    @OA\Property(property="name", type="string", format="string",example="car"),
     *    @OA\Property(property="description", type="string", format="string",example="car"),
     *    @OA\Property(property="is_active", type="boolean", format="boolean",example="true"),
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

    public function createDocumentType(DocumentTypeCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('document_type_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();

                $document_type =  DocumentType::create($request_data);


                return response($document_type, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Put(
     *      path="/v1.0/document-types",
     *      operationId="updateDocumentType",
     *      tags={"property_management.document_type_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update document type",
     *      description="This method is to update document type",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","name","description","is_active"},
     * *    @OA\Property(property="id", type="number", format="number",example="1"),
     *    @OA\Property(property="name", type="string", format="string",example="car"),
     *    @OA\Property(property="description", type="string", format="string",example="car"),
     *    @OA\Property(property="is_active", type="boolean", format="boolean",example="true"),
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

    public function updateDocumentType(DocumentTypeUpdateRequest $request)
    {
        try {
            $this->storeActivity($request, "");
            return  DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('document_type_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                $updatableData = $request->validated();



                $fuel_station  =  tap(DocumentType::where(["id" => $updatableData["id"]]))->update(
                    collect($updatableData)->only([
                        "name",
                        "icon",
                        "description",
                        "is_active",
                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();

                return response($fuel_station, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Get(
     *      path="/v1.0/document-types/{perPage}",
     *      operationId="getDocumentTypes",
     *      tags={"property_management.document_type_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

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
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),
     * *  @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=true,
     * example="search_key"
     * ),
     *      summary="This method is to get document types ",
     *      description="This method is to get document types",
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

    public function getDocumentTypes($perPage, Request $request)
    {
        try {
            $this->storeActivity($request, "");
            if (!$request->user()->hasPermissionTo('document_type_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }



            $documentTypeQuery = new DocumentType();

            if (!empty($request->search_key)) {
                $documentTypeQuery = $documentTypeQuery->where(function ($query) use ($request) {
                    $term = $request->search_key;
                    $query->where("name", "like", "%" . $term . "%");
                });
            }

            if (!empty($request->start_date)) {
                $documentTypeQuery = $documentTypeQuery->where('created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $documentTypeQuery = $documentTypeQuery->where('created_at', "<=", $request->end_date);
            }
            $document_types = $documentTypeQuery->orderBy("id", $request->order_by)->paginate($perPage);
            return response()->json($document_types, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Get(
     *      path="/v1.0/document-types/get/all",
     *      operationId="getAllDocumentTypes",
     *      tags={"property_management.document_type_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

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
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=true,
     * example="ASC"
     * ),
     * *  @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=true,
     * example="search_key"
     * ),
     *      summary="This method is to get all document types ",
     *      description="This method is to get all document types",
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

    public function getAllDocumentTypes(Request $request)
    {
        try {
            $this->storeActivity($request, "");
            if (!$request->user()->hasPermissionTo('document_type_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }



            $documentTypeQuery = new DocumentType();

            if (!empty($request->search_key)) {
                $documentTypeQuery = $documentTypeQuery->where(function ($query) use ($request) {
                    $term = $request->search_key;
                    $query->where("name", "like", "%" . $term . "%");
                });
            }

            if (!empty($request->start_date)) {
                $documentTypeQuery = $documentTypeQuery->where('created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $documentTypeQuery = $documentTypeQuery->where('created_at', "<=", $request->end_date);
            }
            $document_types = $documentTypeQuery->orderBy("name", "ASC")->get();
            return response()->json($document_types, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/document-types/{id}",
     *      operationId="deleteDocumentTypeById",
     *      tags={"property_management.document_type_management"},
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
     *      summary="This method is to delete fuel station by id",
     *      description="This method is to delete fuel station by id",
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

    public function deleteDocumentTypeById($id, Request $request)
    {

        try {
            $this->storeActivity($request, "");
            if (!$request->user()->hasPermissionTo('document_type_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            DocumentType::where([
                "id" => $id
            ])
                ->delete();

            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
