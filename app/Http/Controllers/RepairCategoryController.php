<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\RepairCategoryRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\RepairCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RepairCategoryController extends Controller
{
    use ErrorUtil, UserActivityUtil;
    /**
     *
     * @OA\Post(
     *      path="/v1.0/repair-category-icon",
     *      operationId="createRepairCategoryImage",
     *      tags={"property_management.repair_category_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store repair category logo",
     *      description="This method is to store repair category logo",
     *
     *  @OA\RequestBody(
     *   * @OA\MediaType(
     *     mediaType="multipart/form-data",
     *     @OA\Schema(
     *         required={"image"},
     *         @OA\Property(
     *             description="image to upload",
     *             property="image",
     *             type="file",
     *             collectionFormat="multi",
     *         )
     *     )
     * )



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

    public function createRepairCategoryImage(ImageUploadRequest $request)
    {
        try {
            $this->storeActivity($request, "");

            $request_data = $request->validated();

            $location =  config("setup-config.repair_category_image");

            $new_file_name = time() . '_' . str_replace(' ', '_', $request_data["image"]->getClientOriginalName());

            $request_data["image"]->move(public_path($location), $new_file_name);


            return response()->json(["image" => $new_file_name, "location" => $location, "full_location" => ("/" . $location . "/" . $new_file_name)], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Post(
     *      path="/v1.0/repair-categories",
     *      operationId="createRepairCategory",
     *      tags={"property_management.repair_category_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store repair category",
     *      description="This method is to store repair category",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"name","description","logo"},
     *  *             @OA\Property(property="icon", type="string", format="string",example="image.jpg"),
     *             @OA\Property(property="name", type="string", format="string",example="Rifat"),
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

    public function createRepairCategory(RepairCategoryRequest $request)
    {
        try {
            $this->storeActivity($request, "");
            return DB::transaction(function () use ($request) {
                /** @var \App\Models\User $authUser */
                $authUser = $request->user();

                if (!$authUser->hasPermissionTo('repair_category_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();
                $request_data["created_by"] = $authUser->id;

                if ($authUser->hasRole('superadmin')) {
                    $request_data['is_default'] = $request->filled('business_id') ? 0 : 1;
                    $request_data['business_id'] = $request->filled('business_id') ? $request->input('business_id') : null;
                } else {
                    $request_data['is_default'] = 0;
                    $request_data['business_id'] = $authUser->business_id;
                }

                $repair_category = RepairCategory::create($request_data);
                $repair_category->generated_id = Str::random(4) . $repair_category->id . Str::random(4);
                $repair_category->save();

                return response($repair_category, 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Put(
     *      path="/v1.0/repair-categories",
     *      operationId="updateRepairCategory",
     *      tags={"property_management.repair_category_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update repair category",
     *      description="This method is to update repair category",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","name","description","logo"},
     *     *             @OA\Property(property="id", type="number", format="number",example="1"),

     *             @OA\Property(property="name", type="string", format="string",example="dfthth"),
     *            @OA\Property(property="icon", type="string", format="string",example="Al.jpg"),

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

    public function updateRepairCategory(RepairCategoryRequest $request)
    {
        try {
            $this->storeActivity($request, "");
            return DB::transaction(function () use ($request) {
                /** @var \App\Models\User $authUser */
                $authUser = $request->user();

                if (!$authUser->hasPermissionTo('repair_category_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();

                $repair_category = RepairCategory::where('id', $request_data['id'])->first();

                if ($repair_category->is_default == 1 && !$authUser->hasRole('superadmin')) {
                    return response()->json([
                        'success' => false,
                        "message" => "you can not update default repair category"
                    ], 403);
                }

                if ($repair_category->business_id !== $authUser->business_id) {
                    return response()->json([
                        'success' => false,
                        "message" => "you can not update repair category of another business"
                    ], 403);
                }

                $repair_category->update($request_data);

                return response($repair_category, 200);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Get(
     *      path="/v1.0/repair-categories/{perPage}",
     *      operationId="getRepairCategories",
     *      tags={"property_management.repair_category_management"},
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
     *      summary="This method is to get repair categories ",
     *      description="This method is to get repair categories",
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

    /**
     *
     * @OA\Get(
     *      path="/v1.0/repair-categories",
     *      operationId="getRepairCategoriesList",
     *      tags={"property_management.repair_category_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      * *  @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=false,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=false,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=false,
     * example="ASC"
     * ),
     * *  @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=false,
     * example="search_key"
     * ),
     * *  @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="per_page",
     * required=false,
     * example="10"
     * ),
     *      summary="This method is to get repair categories list with query params",
     *      description="This method is to get repair categories list with query params",
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
    public function getRepairCategoriesList(Request $request)
    {
        try {

            $repair_categoryQuery = RepairCategory::repairCategoryQuery();

            $repair_categories = retrieve_data($repair_categoryQuery, "id", "repair_categories");

            return response()->json($repair_categories, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/repair-categories/{perPage}",
     *      operationId="getRepairCategories",
     *      tags={"property_management.repair_category_management"},
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
     *      summary="This method is to get repair categories ",
     *      description="This method is to get repair categories",
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

    public function getRepairCategories($perPage, Request $request)
    {
        try {
            $this->storeActivity($request, "");
            if (!$request->user()->hasPermissionTo('repair_category_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // $automobilesQuery = AutomobileMake::with("makes");

            $repair_categoryQuery = RepairCategory::repairCategoryQuery();

            $repair_categories = $repair_categoryQuery->orderBy("id", $request->order_by)->paginate($perPage);

            return response()->json($repair_categories, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/repair-categories/get/all/optimized",
     *      operationId="getAllRepairCategoriesOptimized",
     *      tags={"property_management.repair_category_management"},
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
     *      summary="This method is to get repair categories ",
     *      description="This method is to get repair categories",
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

    public function getAllRepairCategoriesOptimized(Request $request)
    {
        try {
            $this->storeActivity($request, "");
            if (!$request->user()->hasPermissionTo('repair_category_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // $automobilesQuery = AutomobileMake::with("makes");

            $repair_categoryQuery = RepairCategory::repairCategoryQuery();

            $repair_categories = $repair_categoryQuery
                ->select(
                    "repair_categories.id",
                    "repair_categories.generated_id",
                    "repair_categories.name",


                )
                ->orderBy("id", $request->order_by)->get();

            return response()->json($repair_categories, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/repair-categories/get/single/{id}",
     *      operationId="getRepairCategoryById",
     *      tags={"property_management.repair_category_management"},
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

     *      summary="This method is to get repair category by id",
     *      description="This method is to get repair category by id",
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

    public function getRepairCategoryById($id, Request $request)
    {
        try {
            $this->storeActivity($request, "");
            if (!$request->user()->hasPermissionTo('repair_category_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }


            $repair_category = RepairCategory::repairCategoryQuery()
                ->where("id", $id)
                ->first();

            if (!$repair_category) {
                return response()->json([
                    "success"=>false,
                    "message" => "no repair category found"
                ], 404);
            }


            return response()->json($repair_category, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }










    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/repair-categories/{id}",
     *      operationId="deleteRepairCategoryById",
     *      tags={"property_management.repair_category_management"},
     *       security={
     *           {"bearerAuth": {}},
     *
     *       },
     *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="This method is to delete repair category by id",
     *      description="This method is to delete repair category by id",
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

    public function deleteRepairCategoryById($id, Request $request)
    {

        try {
            $this->storeActivity($request, "");

            $authUser = $request->user();

            if (!$authUser->hasRole('superadmin')) {
                $businessPin = $authUser->current_business->pin ?? ($authUser->my_business->pin ?? null);

                if ($businessPin !== $request->header("password")) {
                    return response()->json([
                        "message" => "Invalid pin"
                    ], 403);
                }
            }

            // $business = Business::where([
            //     "owner_id" => $request->user()->id
            //   ])->first();

            // if(!$business) {
            //     return response()->json([
            //      "message" => "you don't have a valid business"
            //     ],401);
            //  }
            //  if(!($business->pin == $request->header("pin"))) {
            //      return response()->json([
            //          "message" => "invalid pin"
            //         ],401);
            //  }

            /** @var \App\Models\User $authUser */
            $authUser = $request->user();

            $repair_category = RepairCategory::where('id', $id)->first();

            if (!$repair_category) {
                return response()->json([
                    'success' => false,
                    "message" => "repair category not found"
                ], 404);
            }

            if ($repair_category->is_default == 1 && !$authUser->hasRole('superadmin')) {
                return response()->json([
                    'success' => false,
                    "message" => "you can not delete default repair category"
                ], 403);
            }

            if ($repair_category->business_id !== $authUser->business_id) {
                return response()->json([
                    'success' => false,
                    "message" => "you can not delete repair category of another business"
                ], 403);
            }

            $repair_category->delete();

            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
