<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\RepairCategoryCreateRequest;
use App\Http\Requests\RepairCategoryUpdateRequest;
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
    try{
        $this->storeActivity($request,"");

        $request_data = $request->validated();

        $location =  config("setup-config.repair_category_image");

        $new_file_name = time() . '_' . str_replace(' ', '_', $request_data["image"]->getClientOriginalName());

        $request_data["image"]->move(public_path($location), $new_file_name);


        return response()->json(["image" => $new_file_name,"location" => $location,"full_location"=>("/".$location."/".$new_file_name)], 200);


    } catch(Exception $e){

        return $this->sendError($e,500,$request);
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

public function createRepairCategory(RepairCategoryCreateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return DB::transaction(function () use ($request) {
            if (!$request->user()->hasPermissionTo('repair_category_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }


            $request_data = $request->validated();
            $request_data["created_by"] = $request->user()->id;
            $repair_category =  RepairCategory::create($request_data);
            $repair_category->generated_id = Str::random(4) . $repair_category->id . Str::random(4);
            $repair_category->save();


            return response($repair_category, 201);





        });




    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
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

public function updateRepairCategory(RepairCategoryUpdateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return  DB::transaction(function () use ($request) {
            if (!$request->user()->hasPermissionTo('repair_category_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $updatableData = $request->validated();

            // $affiliationPrev = RepairCategory::where([
            //     "id" => $updatableData["id"]
            //    ]);

            //    if(!$request->user()->hasRole('superadmin')) {
            //     $affiliationPrev =    $affiliationPrev->where([
            //         "created_by" =>$request->user()->id
            //     ]);
            // }
            // $affiliationPrev = $affiliationPrev->first();
            //  if(!$affiliationPrev) {
            //         return response()->json([
            //            "message" => "you did not create this affiliation."
            //         ],404);
            //  }




            $repair_category  =  tap(RepairCategory::where(["id" => $updatableData["id"], "created_by" => $request->user()->id]))->update(
                collect($updatableData)->only([
    'name',
    'icon',

                ])->toArray()
            )
                // ->with("somthing")

                ->first();

            return response($repair_category, 200);
        });
    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
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
        $this->storeActivity($request,"");
        if (!$request->user()->hasPermissionTo('repair_category_view')) {
            return response()->json([
                "message" => "You can not perform this action"
            ], 401);
        }

        // $automobilesQuery = AutomobileMake::with("makes");

        $repair_categoryQuery =  new RepairCategory();

        if (!empty($request->search_key)) {
            $repair_categoryQuery = $repair_categoryQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                $query->where("name", "like", "%" . $term . "%");
            });
        }

        if (!empty($request->start_date)) {
            $repair_categoryQuery = $repair_categoryQuery->where('created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $repair_categoryQuery = $repair_categoryQuery->where('created_at', "<=", $request->end_date);
        }

        $repair_categories = $repair_categoryQuery->orderBy("id",$request->order_by)->paginate($perPage);

        return response()->json($repair_categories, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
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

 public function getAllRepairCategoriesOptimized( Request $request)
 {
     try {
         $this->storeActivity($request,"");
         if (!$request->user()->hasPermissionTo('repair_category_view')) {
             return response()->json([
                 "message" => "You can not perform this action"
             ], 401);
         }

         // $automobilesQuery = AutomobileMake::with("makes");

         $repair_categoryQuery =  new RepairCategory();

         if (!empty($request->search_key)) {
             $repair_categoryQuery = $repair_categoryQuery->where(function ($query) use ($request) {
                 $term = $request->search_key;
                 $query->where("name", "like", "%" . $term . "%");
             });
         }

         if (!empty($request->start_date)) {
             $repair_categoryQuery = $repair_categoryQuery->where('created_at', ">=", $request->start_date);
         }
         if (!empty($request->end_date)) {
             $repair_categoryQuery = $repair_categoryQuery->where('created_at', "<=", $request->end_date);
         }

         $repair_categories = $repair_categoryQuery
         ->select(
            "repair_categories.id",
            "repair_categories.generated_id",
            "repair_categories.name",


            )
         ->orderBy("id",$request->order_by)->get();

         return response()->json($repair_categories, 200);
     } catch (Exception $e) {

         return $this->sendError($e, 500,$request);
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
        $this->storeActivity($request,"");
        if (!$request->user()->hasPermissionTo('repair_category_view')) {
            return response()->json([
                "message" => "You can not perform this action"
            ], 401);
        }


        $repair_category = RepairCategory::where([
            "generated_id" => $id,
            // "created_by" => $request->user()->id
        ])
        ->first();

        if(!$repair_category) {
     return response()->json([
"message" => "no repair category found"
],404);
        }


        return response()->json($repair_category, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
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
        $this->storeActivity($request,"");
        if (!$request->user()->hasPermissionTo('repair_category_delete')) {
            return response()->json([
                "message" => "You can not perform this action"
            ], 401);
        }

        if (!Hash::check($request->header("password"), $request->user()->password)) {
            return response()->json([
                "message" => "Invalid password"
            ], 401);
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
        $repair_category = RepairCategory::where([
            "id" => $id,
            "created_by" => $request->user()->id
        ])
        ->first();

        if(!$repair_category) {
     return response()->json([
"message" => "no repair category found"
],404);
        }
        $repair_category->delete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}
}
