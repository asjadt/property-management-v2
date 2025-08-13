<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\ExpenseCategoryCreateRequest;
use App\Http\Requests\ExpenseCategoryUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\ExpenseCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ExpenseCategoryController extends Controller
{
    use ErrorUtil, UserActivityUtil;
    /**
    *
 * @OA\Post(
 *      path="/v1.0/expense-category-icon",
 *      operationId="createExpenseCategoryImage",
 *      tags={"property_management.expense_category_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store expense category logo",
 *      description="This method is to store expense category logo",
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

public function createExpenseCategoryImage(ImageUploadRequest $request)
{
    try{
        $this->storeActivity($request,"");

        $request_data = $request->validated();

        $location =  config("setup-config.expense_category_image");

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
 *      path="/v1.0/expense-categories",
 *      operationId="createExpenseCategory",
 *      tags={"property_management.expense_category_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store expense category",
 *      description="This method is to store expense category",
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

public function createExpenseCategory(ExpenseCategoryCreateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return DB::transaction(function () use ($request) {
            if (!$request->user()->hasPermissionTo('expense_category_create')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }


            $request_data = $request->validated();
            $request_data["created_by"] = $request->user()->id;
            $expense_category =  ExpenseCategory::create($request_data);
            $expense_category->generated_id = Str::random(4) . $expense_category->id . Str::random(4);
            $expense_category->save();


            return response($expense_category, 201);





        });




    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 * @OA\Put(
 *      path="/v1.0/expense-categories",
 *      operationId="updateExpenseCategory",
 *      tags={"property_management.expense_category_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to update expense category",
 *      description="This method is to update expense category",
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

public function updateExpenseCategory(ExpenseCategoryUpdateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return  DB::transaction(function () use ($request) {
            if (!$request->user()->hasPermissionTo('expense_category_update')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            $request_data = $request->validated();

            // $affiliationPrev = ExpenseCategory::where([
            //     "id" => $request_data["id"]
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




            $expense_category  =  tap(ExpenseCategory::where(["id" => $request_data["id"], "created_by" => $request->user()->id]))->update(
                collect($request_data)->only([
    'name',
    'icon',

                ])->toArray()
            )
                // ->with("somthing")

                ->first();

            return response($expense_category, 200);
        });
    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v1.0/expense-categories/{perPage}",
 *      operationId="getExpenseCategories",
 *      tags={"property_management.expense_category_management"},
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
 *      summary="This method is to get expense categories ",
 *      description="This method is to get expense categories",
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

public function getExpenseCategories($perPage, Request $request)
{
    try {
        $this->storeActivity($request,"");
        if (!$request->user()->hasPermissionTo('expense_category_view')) {
            return response()->json([
                "message" => "You can not perform this action"
            ], 401);
        }

        // $automobilesQuery = AutomobileMake::with("makes");

        $expense_categoryQuery =  new ExpenseCategory();

        if (!empty($request->search_key)) {
            $expense_categoryQuery = $expense_categoryQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                $query->where("name", "like", "%" . $term . "%");
            });
        }

        if (!empty($request->start_date)) {
            $expense_categoryQuery = $expense_categoryQuery->where('created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $expense_categoryQuery = $expense_categoryQuery->where('created_at', "<=", $request->end_date);
        }

        $expense_categories = $expense_categoryQuery->orderBy("id",$request->order_by)->paginate($perPage);

        return response()->json($expense_categories, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}


/**
 *
 * @OA\Get(
 *      path="/v1.0/expense-categories/get/all/optimized",
 *      operationId="getAllExpenseCategoriesOptimized",
 *      tags={"property_management.expense_category_management"},
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
 *      summary="This method is to get expense categories ",
 *      description="This method is to get expense categories",
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

 public function getAllExpenseCategoriesOptimized( Request $request)
 {
     try {
         $this->storeActivity($request,"");
         if (!$request->user()->hasPermissionTo('expense_category_view')) {
             return response()->json([
                 "message" => "You can not perform this action"
             ], 401);
         }

         // $automobilesQuery = AutomobileMake::with("makes");

         $expense_categoryQuery =  new ExpenseCategory();

         if (!empty($request->search_key)) {
             $expense_categoryQuery = $expense_categoryQuery->where(function ($query) use ($request) {
                 $term = $request->search_key;
                 $query->where("name", "like", "%" . $term . "%");
             });
         }

         if (!empty($request->start_date)) {
             $expense_categoryQuery = $expense_categoryQuery->where('created_at', ">=", $request->start_date);
         }
         if (!empty($request->end_date)) {
             $expense_categoryQuery = $expense_categoryQuery->where('created_at', "<=", $request->end_date);
         }

         $expense_categories = $expense_categoryQuery
         ->select(
            "expense_categories.id",
            "expense_categories.generated_id",
            "expense_categories.name",


            )
         ->orderBy("id",$request->order_by)->get();

         return response()->json($expense_categories, 200);
     } catch (Exception $e) {

         return $this->sendError($e, 500,$request);
     }
 }


/**
 *
 * @OA\Get(
 *      path="/v1.0/expense-categories/get/single/{id}",
 *      operationId="getExpenseCategoryById",
 *      tags={"property_management.expense_category_management"},
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

 *      summary="This method is to get expense category by id",
 *      description="This method is to get expense category by id",
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

public function getExpenseCategoryById($id, Request $request)
{
    try {
        $this->storeActivity($request,"");
        if (!$request->user()->hasPermissionTo('expense_category_view')) {
            return response()->json([
                "message" => "You can not perform this action"
            ], 401);
        }


        $expense_category = ExpenseCategory::where([
            "generated_id" => $id,
            // "created_by" => $request->user()->id
        ])
        ->first();

        if(!$expense_category) {
     return response()->json([
"message" => "no expense category found"
],404);
        }


        return response()->json($expense_category, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}










/**
 *
 *     @OA\Delete(
 *      path="/v1.0/expense-categories/{id}",
 *      operationId="deleteExpenseCategoryById",
 *      tags={"property_management.expense_category_management"},
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
 *      summary="This method is to delete expense category by id",
 *      description="This method is to delete expense category by id",
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

public function deleteExpenseCategoryById($id, Request $request)
{

    try {
        $this->storeActivity($request,"");
        if (!$request->user()->hasPermissionTo('expense_category_delete')) {
            return response()->json([
                "message" => "You can not perform this action"
            ], 401);
        }

        if (!Hash::check($request->header("password"), $request->user()->password)) {
            return response()->json([
                "message" => "Invalid password"
            ], 401);
        }

       

        $expense_category = ExpenseCategory::where([
            "id" => $id,
            "created_by" => $request->user()->id
        ])
        ->first();

        if(!$expense_category) {
     return response()->json([
 "message" => "no expense category found"
],404);
        }
        $expense_category->delete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}
}
