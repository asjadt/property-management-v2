<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoicePaymentCreateRequest;
use App\Http\Requests\InvoicePaymentUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoicePaymentController extends Controller
{
    use ErrorUtil, UserActivityUtil;



/**
 *
 * @OA\Post(
 *      path="/v1.0/invoice-payments",
 *      operationId="createInvoicePayment",
 *      tags={"property_management.invoice_payment_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store invoice",
 *      description="This method is to store invoice",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"name","description","logo"},
 *  *             @OA\Property(property="amount", type="number", format="number",example="10"),
  *             @OA\Property(property="payment_method", type="string", format="string",example="bkash"),
 *            @OA\Property(property="payment_date", type="string", format="string",example="12/12/2012"),
 *            @OA\Property(property="invoice_id", type="number", format="number",example="1"),

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

public function createInvoicePayment(InvoicePaymentCreateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return DB::transaction(function () use ($request) {



            $insertableData = $request->validated();
            $insertableData["created_by"] = $request->user()->id;

            $invoice = Invoice::where([
                "id" => $insertableData["invoice_id"]
            ])
            ->first();
            if(!$invoice) {
                throw new Exception("something went wrong");
            }

            $sum_payment_amounts = $invoice->invoice_items()->sum('amount');
            $invoice_due = $invoice->total_amount - $sum_payment_amounts;



            if($invoice_due < $insertableData["amount"]) {
                $error =  [
                    "message" => "The given data was invalid.",
                    "errors" => ["amount"=>["amount is more than total amount"]]
             ];
                throw new Exception(json_encode($error),422);
            }
           else if($invoice_due == $insertableData["amount"]) {
               $invoice->payment_status = "paid";
               $invoice->save();
            }
            else {
                $invoice->payment_status = "due";
                $invoice->save();
             }



            $invoice_payment =  InvoicePayment::create($insertableData);

            if(!$invoice_payment) {
                throw new Exception("something went wrong");
            }



            return response($invoice_payment, 201);





        });




    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 * @OA\Put(
 *      path="/v1.0/invoice-payments",
 *      operationId="updateInvoicePayment",
 *      tags={"property_management.invoice_payment_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to update invoice",
 *      description="This method is to update invoice",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"id","name","description","logo"},
 *     *             @OA\Property(property="id", type="number", format="number",example="1"),
 *  *             @OA\Property(property="amount", type="number", format="number",example="10"),
  *             @OA\Property(property="payment_method", type="string", format="string",example="bkash"),
 *            @OA\Property(property="payment_date", type="string", format="string",example="12/12/2012"),
 *            @OA\Property(property="invoice_id", type="number", format="number",example="1"),
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

public function updateInvoicePayment(InvoicePaymentUpdateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return  DB::transaction(function () use ($request) {

            $updatableData = $request->validated();

            // $affiliationPrev = InvoicePayments::where([
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


            $invoice = Invoice::where([
                "id" => $updatableData["invoice_id"]
            ])
            ->first();
            if(!$invoice) {
                throw new Exception("something went wrong");
            }

            $sum_payment_amounts = $invoice->invoice_items()->where('id', '!=', $updatableData["id"])->sum('amount');

            $invoice_due = $invoice->total_amount - $sum_payment_amounts;


            if($invoice_due < $updatableData["amount"]) {
                $error =  [
                    "message" => "The given data was invalid.",
                    "errors" => ["amount"=>["amount is more than total amount"]]
             ];
                throw new Exception(json_encode($error),422);
            }
           else if($invoice_due == $updatableData["amount"]) {
               $invoice->payment_status = "paid";
               $invoice->save();
            }
            else {
                $invoice->payment_status = "due";
                $invoice->save();
             }

            $invoice_payment  =  tap(InvoicePayment::where(["id" => $updatableData["id"]]))->update(
                collect($updatableData)->only([
                    "amount",
                    "payment_method",
                    "payment_date",
                    "invoice_id",
                ])->toArray()
            )
                // ->with("somthing")

                ->first();

            return response($invoice_payment, 200);
        });
    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v1.0/invoice-payments/{perPage}",
 *      operationId="getInvoicePayments",
 *      tags={"property_management.invoice_payment_management"},
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
 *      summary="This method is to get invoice-payments ",
 *      description="This method is to get invoice-payments",
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

public function getInvoicePayments($perPage, Request $request)
{
    try {
        $this->storeActivity($request,"");

        // $automobilesQuery = AutomobileMake::with("makes");

        $invoice_paymentQuery = new InvoicePayment();

        if (!empty($request->search_key)) {
            $invoice_paymentQuery = $invoice_paymentQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                $query->where("name", "like", "%" . $term . "%");
            });
        }

        if (!empty($request->start_date)) {
            $invoice_paymentQuery = $invoice_paymentQuery->where('created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $invoice_paymentQuery = $invoice_paymentQuery->where('created_at', "<=", $request->end_date);
        }

        $invoice_payments = $invoice_paymentQuery->orderByDesc("id")->paginate($perPage);

        return response()->json($invoice_payments, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}



/**
 *
 * @OA\Get(
 *      path="/v1.0/invoice-payments/get/single/{id}",
 *      operationId="getInvoicePaymentById",
 *      tags={"property_management.invoice_payment_management"},
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

 *      summary="This method is to get invoice by id",
 *      description="This method is to get invoice by id",
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

public function getInvoicePaymentById($id, Request $request)
{
    try {
        $this->storeActivity($request,"");


        $invoice_payment = InvoicePayment::where([
            "id" => $id
        ])
        ->first();

        if(!$invoice_payment) {
     return response()->json([
"message" => "no invoice found"
],404);
        }


        return response()->json($invoice_payment, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}










/**
 *
 *     @OA\Delete(
 *      path="/v1.0/invoice-payments/{id}",
 *      operationId="deleteInvoicePaymentById",
 *      tags={"property_management.invoice_payment_management"},
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
 *      summary="This method is to delete invoice by id",
 *      description="This method is to delete invoice by id",
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

public function deleteInvoicePaymentById($id, Request $request)
{

    try {
        $this->storeActivity($request,"");

        $invoice_payment = InvoicePayment::where([
            "id" => $id
        ])
        ->first();

        if(!$invoice_payment) {
     return response()->json([
"message" => "no invoice found"
],404);
        }
        $invoice_payment->delete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}



}
