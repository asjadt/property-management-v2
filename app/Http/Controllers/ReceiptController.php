<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReceiptCreateRequest;
use App\Http\Requests\ReceiptUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Receipt;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReceiptController extends Controller
{
    use ErrorUtil, UserActivityUtil;


    /**
     *
     * @OA\Post(
     *      path="/v1.0/receipts",
     *      operationId="createReceipt",
     *      tags={"property_management.receipt_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store receipt",
     *      description="This method is to store receipt",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"name","description","logo"},
     *  *             @OA\Property(property="tenant_id", type="number", format="number",example="1"),
      *             @OA\Property(property="property_address", type="string", format="string",example="property_address"),
     *            @OA\Property(property="amount", type="number", format="number",example="100"),
     *            @OA\Property(property="receipt_by", type="string", format="string",example="receipt_by"),
     *  * *  @OA\Property(property="receipt_date", type="string", format="boolean",example="12/12/2012"),
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

    public function createReceipt(ReceiptCreateRequest $request)
    {
        try {
            $this->storeActivity($request,"");
            return DB::transaction(function () use ($request) {



                $insertableData = $request->validated();
                $insertableData["created_by"] = $request->user()->id;
                $receipt =  Receipt::create($insertableData);



                return response($receipt, 201);





            });




        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }

   /**
     *
     * @OA\Put(
     *      path="/v1.0/receipts",
     *      operationId="updateReceipt",
     *      tags={"property_management.receipt_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update receipt",
     *      description="This method is to update receipt",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","name","description","logo"},
     *     *             @OA\Property(property="id", type="number", format="number",example="1"),
     *  *             @OA\Property(property="tenant_id", type="number", format="number",example="1"),
      *             @OA\Property(property="property_address", type="string", format="string",example="property_address"),
     *            @OA\Property(property="amount", type="number", format="number",example="100"),
     *            @OA\Property(property="receipt_by", type="string", format="string",example="receipt_by"),
     *  * *  @OA\Property(property="receipt_date", type="string", format="boolean",example="12/12/2012"),
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

    public function updateReceipt(ReceiptUpdateRequest $request)
    {
        try {
            $this->storeActivity($request,"");
            return  DB::transaction(function () use ($request) {

                $updatableData = $request->validated();

                // $affiliationPrev = Receipt::where([
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




                $receipt  =  tap(Receipt::where(["id" => $updatableData["id"]]))->update(
                    collect($updatableData)->only([
                        'tenant_id',
                        'property_address',
                        'amount',
                        'receipt_by',
                        'receipt_date',
                        "created_by"
                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();

                return response($receipt, 200);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500,$request);
        }
    }
 /**
     *
     * @OA\Get(
     *      path="/v1.0/receipts/{perPage}",
     *      operationId="getReceipts",
     *      tags={"property_management.receipt_management"},
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
* name="search_key",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),
     *      summary="This method is to get receipts ",
     *      description="This method is to get receipts",
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

    public function getReceipts($perPage, Request $request)
    {
        try {
            $this->storeActivity($request,"");

            // $automobilesQuery = AutomobileMake::with("makes");

            $receiptQuery = new Receipt();

            if (!empty($request->search_key)) {
                $receiptQuery = $receiptQuery->where(function ($query) use ($request) {
                    $term = $request->search_key;
                    $query->where("name", "like", "%" . $term . "%");
                });
            }

            if (!empty($request->start_date)) {
                $receiptQuery = $receiptQuery->where('created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $receiptQuery = $receiptQuery->where('created_at', "<=", $request->end_date);
            }

            $receipts = $receiptQuery->orderByDesc("id")->paginate($perPage);

            return response()->json($receipts, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }



 /**
     *
     * @OA\Get(
     *      path="/v1.0/receipts/get/single/{id}",
     *      operationId="getReceiptsyId",
     *      tags={"property_management.receipt_management"},
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

     *      summary="This method is to get receipt by id",
     *      description="This method is to get receipt by id",
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

    public function getReceiptById($id, Request $request)
    {
        try {
            $this->storeActivity($request,"");


            $receipt = Receipt::where([
                "id" => $id
            ])
            ->first();

            if(!$receipt) {
         return response()->json([
"message" => "no receipt found"
],404);
            }


            return response()->json($receipt, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }










    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/receipts/{id}",
     *      operationId="deleteReceiptById",
     *      tags={"property_management.receipt_management"},
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
     *      summary="This method is to delete receipt by id",
     *      description="This method is to delete receipt by id",
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

    public function deleteReceiptById($id, Request $request)
    {

        try {
            $this->storeActivity($request,"");



            $receipt = Receipt::where([
                "id" => $id
            ])
            ->first();

            if(!$receipt) {
         return response()->json([
"message" => "no receipt found"
],404);
            }
            $receipt->delete();

            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }
}
