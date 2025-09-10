<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\MultipleFileUploadRequest;
use App\Http\Requests\ExpenseCreateRequest;
use App\Http\Requests\ExpenseUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\Expense;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    use ErrorUtil, UserActivityUtil;

  /**
    *
 * @OA\Post(
 *      path="/v1.0/expense-receipts-file",
 *      operationId="createExpenseReceiptFile",
 *      tags={"property_management.expense_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store reciept file",
 *      description="This method is to store reciept file",
 *
*  @OA\RequestBody(
    *   * @OA\MediaType(
*     mediaType="multipart/form-data",
*     @OA\Schema(
*         required={"file"},
*         @OA\Property(
*             description="file to upload",
*             property="file",
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

public function createExpenseReceiptFile(FileUploadRequest $request)
{
    try{
        $this->storeActivity($request,"");

        $request_data = $request->validated();

        $location =  config("setup-config.expense_receipt_file");

        $new_file_name = time() . '_' . str_replace(' ', '_', $request_data["file"]->getClientOriginalName());


        $request_data["file"]->move(public_path($location), $new_file_name);


        return response()->json(["file" => $new_file_name,"location" => $location,"full_location"=>("/".$location."/".$new_file_name)], 200);


    } catch(Exception $e){

        return $this->sendError($e,500,$request);
    }
}
 /**
        *
     * @OA\Post(
     *      path="/v1.0/expense-receipts-file/multiple",
     *      operationId="createExpenseReceiptFileMultiple",
     *      tags={"property_management.expense_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *      summary="This method is to store multiple expense file",
     *      description="This method is to store multiple expense file",
     *
   *  @OA\RequestBody(
        *   * @OA\MediaType(
*     mediaType="multipart/form-data",
*     @OA\Schema(
*         required={"files[]"},
*         @OA\Property(
*             description="array of files to upload",
*             property="files[]",
*             type="array",
*             @OA\Items(
*                 type="file"
*             ),
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

     public function createExpenseReceiptFileMultiple(MultipleFileUploadRequest $request)
     {
         try{
             $this->storeActivity($request,"");

             $request_data = $request->validated();

             $location =  config("setup-config.expense_receipt_file");

             $files = [];
             if(!empty($request_data["files"])) {
                 foreach($request_data["files"] as $file){
                     $new_file_name = time() . '_' . $file->getClientOriginalName();
                     $new_file_name = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                     $file->move(public_path($location), $new_file_name);

                     array_push($files,("/".$location."/".$new_file_name));
                 }
             }

             return response()->json(["files" => $files], 201);


         } catch(Exception $e){
             error_log($e->getMessage());
         return $this->sendError($e,500,$request);
         }
     }
 
/**
 *
 * @OA\Post(
 *      path="/v1.0/expenses",
 *      operationId="createExpense",
 *      tags={"property_management.expense_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store expense",
 *      description="This method is to store expense",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"name","description","logo"},
 *  *             @OA\Property(property="property_id", type="number", format="number",example="1"),
  *             @OA\Property(property="expense_category_id", type="string", format="string",example="1"),
  *             @OA\Property(property="payment_method", type="string", format="string",example="1"),
 *            @OA\Property(property="item_description", type="string", format="string",example="item_description"),
 *
 *  *            @OA\Property(property="status", type="string", format="string",example="status"),
 *

 *  * *  @OA\Property(property="price", type="string", format="string",example="10"),
 *  * *  @OA\Property(property="create_date", type="string", format="string",example="2019-06-29"),

 *  *  *  * *  @OA\Property(property="receipt", type="string", format="array",example={"a.jpg","b.jpg","c.jpg"}),

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

public function createExpense(ExpenseCreateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return DB::transaction(function () use ($request) {




            $request_data = $request->validated();
            $request_data["receipt"]   = json_encode($request_data["receipt"] );
            $request_data["created_by"] = $request->user()->id;
            $expense =  Expense::create($request_data);

            if(!$expense) {
                throw new Exception("something went wrong");
            }
            $expense->generated_id = Str::random(4) . $expense->id . Str::random(4);
            $expense->save();


   

            $expense->load(["expense_category","property"]);

            return response($expense, 201);





        });




    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 * @OA\Put(
 *      path="/v1.0/expenses",
 *      operationId="updateExpense",
 *      tags={"property_management.expense_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to update expense",
 *      description="This method is to update expense",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"id","name","description","logo"},
 *     *             @OA\Property(property="id", type="number", format="number",example="1"),
 *  *             @OA\Property(property="property_id", type="number", format="number",example="1"),
  *             @OA\Property(property="expense_category_id", type="string", format="string",example="1"),
  *             @OA\Property(property="payment_method", type="string", format="string",example="1"),
 *            @OA\Property(property="item_description", type="string", format="string",example="item_description"),
 *  *            @OA\Property(property="status", type="string", format="string",example="status"),
 *

 *  * *  @OA\Property(property="price", type="string", format="string",example="10"),
 *  * *  @OA\Property(property="create_date", type="string", format="string",example="2019-06-29"),

 *  *  * *  @OA\Property(property="receipt", type="string", format="array",example={"a.jpg","b.jpg","c.jpg"}),

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

public function updateExpense(ExpenseUpdateRequest $request)
{
    try {
        $this->storeActivity($request,"");

        return  DB::transaction(function () use ($request) {


            $request_data = $request->validated();
            $request_data["receipt"]   = json_encode($request_data["receipt"] );




            $expense  =  tap(Expense::where(["id" => $request_data["id"],"created_by" => $request->user()->id]))->update(
                collect($request_data)->only([
                    'property_id',
                    "payment_method",
                    'expense_category_id',
                    'item_description',
                    'status',
                    'receipt',
                    'price',
                    'create_date',
                ])->toArray()
            )
                 ->with("expense_category","property")

                ->first();

                if(!$expense) {
                    throw new Exception("something went wrong");
                }

             





            return response($expense, 200);
        });
    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v1.0/expenses/{perPage}",
 *      operationId="getExpenses",
 *      tags={"property_management.expense_management"},
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
 *
 *  * *      * *  @OA\Parameter(
* name="expense_id",
* in="query",
* description="expense_id",
* required=true,
* example="1"
* ),
 *
 * *      * *  @OA\Parameter(
* name="property_id",
* in="query",
* description="property_id",
* required=true,
* example="1"
* ),
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
 * *  @OA\Parameter(
* name="expense_category",
* in="query",
* description="expense_category",
* required=true,
* example="expense_category"
* ),

 *      summary="This method is to get expenses ",
 *      description="This method is to get expenses",
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

public function getExpenses($perPage, Request $request)
{
    try {
        $this->storeActivity($request,"");

        $expenseQuery =  Expense::with("expense_category","property")
        ->leftJoin('invoice_items', 'invoice_items.expense_id', '=', 'expenses.id')
        ->leftJoin('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
        ->leftJoin('properties', 'properties.id', '=', 'expenses.property_id')
        ->leftJoin('expense_categories', 'expense_categories.id', '=', 'expenses.expense_category_id')
        ->where(["expenses.created_by" => $request->user()->id])
        ->when(request()->filled("invoice_not_issued"), function ($query) {
            return $query->whereDoesntHave('invoice_items')
            ->whereDoesntHave('rent_adjustments')
            ->where([
                "paid_by" => "agent"
            ])
            ;
        });

        if (!empty($request->search_key)) {
            $expenseQuery = $expenseQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                $query->orWhere("expenses.item_description", "like", "%" . $term . "%");
            });
        }

        if (!empty($request->expense_category)) {
            $expenseQuery = $expenseQuery->where('expense_categories.name', $request->expense_category);
        }

        if (!empty($request->status)) {
            $expenseQuery = $expenseQuery->where('expenses.status', $request->status);
        }

        if (!empty($request->start_date)) {
            $expenseQuery = $expenseQuery->where('expenses.created_at', ">=", $request->start_date);
        }
        
        if (!empty($request->end_date)) {
            $expenseQuery = $expenseQuery->where('expenses.created_at', "<=", $request->end_date);
        }
        if (!empty($request->property_id)) {
            $expenseQuery = $expenseQuery->where('expenses.property_id', $request->property_id);
        }


        $expenses = $expenseQuery
        ->select("expenses.*",
    //     DB::raw('CASE
    //     WHEN (SELECT COUNT(invoice_items.id) FROM invoice_items WHERE invoice_items.expense_id = expenses.id) = 0 THEN 0
    //     ELSE 1
    // END AS is_invoice_issued'),
    // DB::raw('CASE
    // WHEN invoices.status = "paid" THEN "paid"
    // WHEN invoices.status = "partial" THEN "partial"
    // ELSE "due"
    // END AS payment_status'
    // )



        )
        ->groupBy("expenses.id")
        ->
        orderBy("expenses.id",$request->order_by)
        ->paginate($perPage);

        return response()->json($expenses, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}


/**
 *
 * @OA\Get(
 *      path="/v1.0/expenses/get/single/{id}",
 *      operationId="getExpenseById",
 *      tags={"property_management.expense_management"},
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

 *      summary="This method is to get expense by id",
 *      description="This method is to get expense by id",
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

public function getExpenseById($id, Request $request)
{
    try {
        $this->storeActivity($request,"");


        $expense = Expense::with("expense_category","property")
        ->where([
            "generated_id" => $id,
            "created_by" => $request->user()->id
        ])
        ->first();

        if(!$expense) {
     return response()->json([
"message" => "no expense found"
],404);
        }


        return response()->json($expense, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}










/**
 *
 *     @OA\Delete(
 *      path="/v1.0/expenses/{id}",
 *      operationId="deleteExpenseById",
 *      tags={"property_management.expense_management"},
 *       security={
 *           {"bearerAuth": {}},
 *            {"pin": {}}
 *       },
 *              @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="id",
 *         required=true,
 *  example="1"
 *      ),
 *      summary="This method is to delete expense by id",
 *      description="This method is to delete expense by id",
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

public function deleteExpenseById($id, Request $request)
{


    try {
        $this->storeActivity($request,"");

        $business = Business::where([
            "owner_id" => $request->user()->id
          ])->first();

        if(!$business) {
            return response()->json([
             "message" => "you don't have a valid business"
            ],401);
         }
         if(!($business->pin == $request->header("pin"))) {
             return response()->json([
                 "message" => "invalid pin"
                ],401);
         }



        $expense = Expense::where([
            "id" => $id,
            "created_by" => $request->user()->id
        ])
        ->first();

        if(!$expense) {
     return response()->json([
"message" => "no expense found"
],404);
        }
        $expense->delete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

}
