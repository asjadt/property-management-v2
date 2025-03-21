<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoicePaymentCreateRequest;
use App\Http\Requests\InvoicePaymentUpdateRequest;
use App\Http\Requests\SendInvoicePaymentReceiptRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\PaymentEmail;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\InvoicePaymentReceipt;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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
 *            @OA\Property(property="payment_date", type="string", format="string",example="2019-06-29"),
 *  *            @OA\Property(property="note", type="string", format="string",example="note"),
 *
 *            @OA\Property(property="invoice_id", type="number", format="number",example="1"),
 *    *            @OA\Property(property="receipt_by", type="string", format="string",example="receipt_by"),

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



            $request_data = $request->validated();
            $request_data["created_by"] = $request->user()->id;

            $invoiceDateWithTime = Carbon::createFromFormat('Y-m-d', $request_data["payment_date"]);
            $invoiceDateWithTime->setTime(Carbon::now()->hour, Carbon::now()->minute, Carbon::now()->second);
            $request_data["payment_date"] =  $invoiceDateWithTime;

            if(empty($request_data["receipt_by"])) {
                $request_data["receipt_by"] = $request->user()->first_Name . " " . $request->user()->last_Name;
            }

            $invoice = Invoice::where([
                "id" => $request_data["invoice_id"],
                "invoices.created_by" => $request->user()->id

            ])
            ->first();
            if(!$invoice) {
                 return response()->json(["message" => "no invoice found or you did not create the invoice"],404);
            }

            $sum_payment_amounts = $invoice->invoice_payments()->sum('amount');
            $invoice_due = $invoice->total_amount - $sum_payment_amounts;



            if($invoice_due < $request_data["amount"]) {
                $invoice->status = "overpaid";
                $invoice->invoice_reminder()->delete();
                $invoice->save();

            //     $error =  [
            //         "message" => "The given data was invalid.",
            //         "errors" => ["amount"=>["amount is more than total amount"]]
            //  ];
            //     throw new Exception(json_encode($error),422);
            }
           else if($invoice_due == $request_data["amount"]) {
               $invoice->status = "paid";
               $invoice->invoice_reminder()->delete();
               $invoice->save();
            }
            else {
                $invoice->status = "partial";
                $invoice->save();
             }



            $invoice_payment =  InvoicePayment::create($request_data);

            if(!$invoice_payment) {
                throw new Exception("something went wrong");
            }
            $invoice_payment->generated_id = Str::random(4) . $invoice_payment->id . Str::random(4);

            $invoice_payment->shareable_link = env("FRONT_END_URL_DASHBOARD")."/share/receipt/". Str::random(4) . "-". $invoice_payment->generated_id ."-" . Str::random(4);

            $invoice_payment->save();











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
 *            @OA\Property(property="payment_date", type="string", format="string",example="2019-06-29"),
 *  *  *            @OA\Property(property="note", type="string", format="string",example="note"),
 *            @OA\Property(property="invoice_id", type="number", format="number",example="1"),
 *    *            @OA\Property(property="receipt_by", type="string", format="string",example="receipt_by"),
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

            $request_data = $request->validated();

            $invoiceDateWithTime = Carbon::createFromFormat('Y-m-d', $request_data["payment_date"]);
            $invoiceDateWithTime->setTime(Carbon::now()->hour, Carbon::now()->minute, Carbon::now()->second);
            $request_data["payment_date"] =    $invoiceDateWithTime;

            if(empty($request_data["receipt_by"])) {
                $request_data["receipt_by"] = $request->user()->first_Name . " " . $request->user()->last_Name;
            }

            // $affiliationPrev = InvoicePayments::where([
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


            $invoice = Invoice::where([
                "id" => $request_data["invoice_id"],

            "invoices.created_by" => $request->user()->id

            ])
            ->first();
            if(!$invoice) {
                throw new Exception("something went wrong");
            }

            $sum_payment_amounts = $invoice->invoice_payments()->where('id', '!=', $request_data["id"])->sum('amount');

            $invoice_due = $invoice->total_amount - $sum_payment_amounts;


            if($invoice_due < $request_data["amount"]) {
                $invoice->status = "overpaid";
                $invoice->invoice_reminder()->delete();
                $invoice->save();

            //     $error =  [
            //         "message" => "The given data was invalid.",
            //         "errors" => ["amount"=>["amount is more than total amount"]]
            //  ];
            //     throw new Exception(json_encode($error),422);
            }
           else if($invoice_due == $request_data["amount"]) {
               $invoice->status = "paid";
               $invoice->invoice_reminder()->delete();
               $invoice->save();
            }
            else {
                $invoice->status = "partial";
                $invoice->save();
             }

             $invoice_payment = InvoicePayment::leftJoin('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
             ->where([
                 "invoice_payments.id" => $request_data["id"],
                 "invoices.created_by" => $request->user()->id
             ])
         ->update([
             "invoice_payments.amount" => $request_data["amount"],
             "invoice_payments.payment_method" => $request_data["payment_method"],
             "invoice_payments.payment_date" => $request_data["payment_date"],
             "invoice_payments.invoice_id" => $request_data["invoice_id"],
             "invoice_payments.note" => $request_data["note"], // Use an alias to specify the 'note' column
             "invoice_payments.receipt_by" => $request_data["receipt_by"]
         ]);

         $invoice_payment = InvoicePayment::find($request_data["id"]);

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
 *  *      * *  @OA\Parameter(
* name="invoice_id",
* in="query",
* description="invoice_id",
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
* name="min_amount",
* in="query",
* description="min_total_due",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="max_amount",
* in="query",
* description="max_total_due",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="payment_method",
* in="query",
* description="payment_method",
* required=true,
* example="1"
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

        $invoice_paymentQuery =  InvoicePayment::with("invoice.tenants","invoice.landlords","invoice.client","invoice.property")->leftJoin('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
        ->where([
            "invoices.created_by" => $request->user()->id
        ]);


        if (!empty($request->invoice_id)) {
            $invoice_paymentQuery = $invoice_paymentQuery->where('invoice_payments.invoice_id',  $request->invoice_id);
        }

        if (!empty($request->search_key)) {
            $invoice_paymentQuery = $invoice_paymentQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                $query->where("invoice_payments.payment_method", "like", "%" . $term . "%");
                // $query->orWhere("invoice_payments.payment_date", "like", "%" . $term . "%");
                $query->orWhere("invoice_payments.payment_method", "like", "%" . $term . "%");
                $query->orWhere("invoices.invoice_reference",  $term );
                 $query->orWhere("invoice_payments.amount", $term);
            });
        }
        if (!empty($request->payment_method)) {
            $invoice_paymentQuery = $invoice_paymentQuery->where('invoice_payments.payment_method', $request->payment_method);
        }
        if (!empty($request->start_date)) {
            $invoice_paymentQuery = $invoice_paymentQuery->where('invoice_payments.payment_date', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $invoice_paymentQuery = $invoice_paymentQuery->where('invoice_payments.payment_date', "<=", $request->end_date);
        }

        if (!empty($request->min_amount)) {
            $invoice_paymentQuery = $invoice_paymentQuery->where('invoice_payments.amount', ">=", $request->min_amount);
        }
        if (!empty($request->max_amount)) {
            $invoice_paymentQuery = $invoice_paymentQuery->where('invoice_payments.amount', "<=", $request->max_amount);
        }


        $invoice_payments = $invoice_paymentQuery
        ->select("invoice_payments.*","invoices.generated_id as invoice_generated_id")
        ->orderBy("invoice_payments.id",$request->order_by)
        ->paginate($perPage);

        return response()->json($invoice_payments, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}



/**
 *
 * @OA\Get(
 *      path="/v1.0/invoice-payments/get/single/{invoice_id}/{id}",
 *      operationId="getInvoicePaymentById",
 *      tags={"property_management.invoice_payment_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
*              @OA\Parameter(
 *         name="invoice_id",
 *         in="path",
 *         description="invoice_id",
 *         required=true,
 *  example="1"
 *      ),
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

public function getInvoicePaymentById($invoice_id,$id, Request $request)
{
    try {
        $this->storeActivity($request,"");


        $invoice_payment = InvoicePayment::with("invoice.tenants","invoice.landlords","invoice.client","invoice.property")
        ->leftJoin('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
        ->where([
            "invoice_payments.generated_id" => $id,
            "invoice_payments.invoice_id" => $invoice_id,
            "invoices.created_by" => $request->user()->id
        ])
        ->select("invoice_payments.*")

        ->first();

        if(!$invoice_payment) {
     return response()->json([
"message" => "no invoice payment found"
],404);
        }


        return response()->json($invoice_payment, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v2.0/invoice-payments/get/single/{id}",
 *      operationId="getInvoicePaymentByIdv2",
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

 public function getInvoicePaymentByIdv2($id, Request $request)
 {
     try {
         $this->storeActivity($request,"");


         $invoice_payment = InvoicePayment::with("invoice")
        //  ->leftJoin('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
         ->where([
             "invoice_payments.generated_id" => $id,

         ])
         ->select("invoice_payments.*")

         ->first();

         if(!$invoice_payment) {
      return response()->json([
 "message" => "no invoice payment found"
 ],404);
         }


        $sum_payment_amounts = $invoice_payment->invoice->invoice_payments()
        // ->where('id', '!=', $invoice_payment->id)
        ->sum('amount');

        $invoice_payment->invoice_due = $invoice_payment->invoice->total_amount - $sum_payment_amounts;


         return response()->json($invoice_payment, 200);
     } catch (Exception $e) {

         return $this->sendError($e, 500,$request);
     }
 }










/**
 *
 *     @OA\Delete(
 *      path="/v1.0/invoice-payments/{invoice_id}/{id}",
 *      operationId="deleteInvoicePaymentById",
 *      tags={"property_management.invoice_payment_management"},
 *       security={
 *           {"bearerAuth": {}},
 *            {"pin": {}}
 *       },
 * *              @OA\Parameter(
 *         name="invoice_id",
 *         in="path",
 *         description="invoice_id",
 *         required=true,
 *  example="1"
 *      ),
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

public function deleteInvoicePaymentById($invoice_id,$id, Request $request)
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

        $invoice_payment = InvoicePayment::leftJoin('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
        ->where([
            "invoice_payments.id" => $id,
            "invoice_payments.invoice_id" => $invoice_id,
            "invoices.created_by" => $request->user()->id
        ])
        ->select("invoice_payments.id","invoice_payments.invoice_id")
        ->first();

        if(!$invoice_payment) {
     return response()->json([
"message" => "no invoice payment found"
],404);
        }


        $invoice = Invoice::where([
            "id" => $invoice_payment->invoice_id,
            "invoices.created_by" => $request->user()->id

        ])
        ->first();
        if(!$invoice) {
             return response()->json(["message" => "no invoice found or you did not create the invoice" . $invoice_payment->invoice_id . " " . $request->user()->id ],404);
        }
        $invoice_payment->delete();
        $sum_payment_amounts = $invoice->invoice_payments()->sum('amount');
        $invoice_due = $invoice->total_amount - $sum_payment_amounts;



        if($invoice_due < 0) {
            $invoice->status = "overpaid";
            $invoice->invoice_reminder()->delete();
            $invoice->save();


        }
       else if($invoice_due == 0) {
           $invoice->status = "paid";
           $invoice->invoice_reminder()->delete();
           $invoice->save();
        }
        else  if ($invoice_due > 0 && $sum_payment_amounts > 0) {
            $invoice->status = "partial";
            $invoice->save();
         }
         else if($invoice->last_sent_date) {
            $invoice->status = "sent";
            $invoice->save();
         }
         else {
            $invoice->status = "unsent";
            $invoice->save();
         }



        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}





/**
 *
 *     @OA\Delete(
 *      path="/v1.0/invoice-payments/{id}",
 *      operationId="deleteInvoicePaymentByIdV2",
 *      tags={"property_management.invoice_payment_management"},
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

 public function deleteInvoicePaymentByIdV2($id, Request $request)
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

         $invoice_payment = InvoicePayment::leftJoin('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
         ->where([
             "invoice_payments.id" => $id,
             "invoices.created_by" => $request->user()->id
         ])
         ->select("invoice_payments.id","invoice_payments.invoice_id")
         ->first();

         if(!$invoice_payment) {
      return response()->json([
 "message" => "no invoice payment found"
 ],404);
         }


         $invoice = Invoice::where([
            "id" => $invoice_payment->invoice_id,
            "invoices.created_by" => $request->user()->id

        ])
        ->first();

        if(!$invoice) {
             return response()->json(["message" => "no invoice found or you did not create the invoice" . $invoice_payment->invoice_id . " " . $request->user()->id ],404);
        }
        $invoice_payment->delete();
        $sum_payment_amounts = $invoice->invoice_payments()->sum('amount');
        $invoice_due = $invoice->total_amount - $sum_payment_amounts;



        if($invoice_due < 0) {
            $invoice->status = "overpaid";
            $invoice->invoice_reminder()->delete();
            $invoice->save();

        //     $error =  [
        //         "message" => "The given data was invalid.",
        //         "errors" => ["amount"=>["amount is more than total amount"]]
        //  ];
        //     throw new Exception(json_encode($error),422);
        }
       else if($invoice_due == 0) {
           $invoice->status = "paid";
           $invoice->invoice_reminder()->delete();
           $invoice->save();
        }
        else  if ($invoice_due > 0 && $sum_payment_amounts > 0) {
            $invoice->status = "partial";
            $invoice->save();
         }
         else if($invoice->last_sent_date) {
            $invoice->status = "sent";
            $invoice->save();
         }
         else {
            $invoice->status = "unsent";
            $invoice->save();
         }



         return response()->json(["ok" => true], 200);
     } catch (Exception $e) {

         return $this->sendError($e, 500,$request);
     }
 }









 /**
 *
 * @OA\Post(
 *      path="/v1.0/invoice-payments/send-receipt-email",
 *      operationId="sendPaymentReceipt",
 *      tags={"property_management.invoice_payment_receipt_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to send invoice payment receipt",
 *      description="This method is to send invoice payment receipt",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"name","description","logo"},
  *     *             @OA\Property(property="invoice_id", type="number", format="number",example="1"),
   *     *             @OA\Property(property="invoice_payment_id", type="number", format="number",example="1"),
  *  *             @OA\Property(property="from", type="string", format="string",example="test@gmail.com"),
  *             @OA\Property(property="to", type="string", format="array",example={ "test1@gmail.com","test2@gmail.com" }),
 *            @OA\Property(property="subject", type="string", format="string",example="subject"),
 *
 *  *         *  *     *  * *  @OA\Property(property="message", type="string", format="string",example="message"),
 *
 *  *  *  *            @OA\Property(property="copy_to_myself", type="number", format="number",example="0"),
 *
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

 public function sendPaymentReceipt(SendInvoicePaymentReceiptRequest $request)
 {
     try {
         $this->storeActivity($request,"");
         return DB::transaction(function () use ($request) {



            $request_data = $request->validated();



            $invoice  =  Invoice::where([
                "invoices.id" => $request_data["invoice_id"],
                "invoices.created_by" => $request->user()->id
            ])
                ->first();


                if(!$invoice) {
                   return response()->json(["message" => "mo invoice found"],404);
                }

                $invoice_payment = InvoicePayment::where([
                    "invoice_id" =>  $invoice->id,
                    "id" => $request_data["invoice_payment_id"]
                ])
                    ->first();

                    if(!$invoice_payment) {
                        return response()->json(["message" => "mo invoice payment found"],404);
                    }


                $recipients = $request_data["to"];
                if($request_data["copy_to_myself"]) {

                   array_push($recipients,$request_data["from"]);

                }


          Mail::to($recipients)
          ->send(new PaymentEmail($invoice,$invoice_payment,$request_data));
             // end email section

             $request_data["to"] =  json_encode($request_data["to"]);

         $invoice_payment_receipt =  InvoicePaymentReceipt::create($request_data);



           $invoice_payment_receipt->shareable_link =  $invoice_payment->shareable_link;






           $invoice_payment_receipt->save();


             return response($invoice_payment, 201);





         });




     } catch (Exception $e) {

         return $this->sendError($e, 500,$request);
     }
 }
/**
 *
 * @OA\Get(
 *      path="/v1.0/invoice-payment-receipts/{perPage}",
 *      operationId="getInvoicePaymentReceipts",
 *      tags={"property_management.invoice_payment_receipt_management"},
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
 *  *      * *  @OA\Parameter(
* name="invoice_id",
* in="query",
* description="invoice_id",
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
 *      summary="This method is to get invoice payment receipt ",
 *      description="This method is to get invoice payment receipt",
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

 public function getInvoicePaymentReceipts($perPage, Request $request)
 {
     try {
         $this->storeActivity($request,"");

         // $automobilesQuery = AutomobileMake::with("makes");

         $invoice_payment_receiptQuery =  InvoicePaymentReceipt::with("invoice","invoice_payment")
         ->leftJoin('invoices', 'invoice_payment_receipts.invoice_id', '=', 'invoices.id')
         ->where([
             "invoices.created_by" => $request->user()->id
         ]);


         if (!empty($request->invoice_id)) {
             $invoice_payment_receiptQuery = $invoice_payment_receiptQuery->where('invoice_payment_receipts.invoice_id',  $request->invoice_id);
         }

         // if (!empty($request->search_key)) {
         //     $invoice_paymentQuery = $invoice_paymentQuery->where(function ($query) use ($request) {
         //         $term = $request->search_key;
         //         $query->where("name", "like", "%" . $term . "%");
         //     });
         // }

         if (!empty($request->start_date)) {
             $invoice_payment_receiptQuery = $invoice_payment_receiptQuery->where('invoice_payment_receipts.created_at', ">=", $request->start_date);
         }
         if (!empty($request->end_date)) {
             $invoice_payment_receiptQuery = $invoice_payment_receiptQuery->where('invoice_payment_receipts.created_at', "<=", $request->end_date);
         }

         $invoice_payment_receipts = $invoice_payment_receiptQuery
         ->select("invoice_payment_receipts.*")
         ->orderBy("invoice_payment_receipts.id",$request->order_by)->paginate($perPage);

         return response()->json($invoice_payment_receipts, 200);
     } catch (Exception $e) {

         return $this->sendError($e, 500,$request);
     }
 }


/**
 *
 * @OA\Get(
 *      path="/v1.0/invoice-payment-receipts/get/single/{id}",
 *      operationId="getInvoicePaymentReceiptById",
 *      tags={"property_management.invoice_payment_receipt_management"},
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

 *      summary="This method is to get invoice payment receipt by id",
 *      description="This method is to get invoice payment receipt by id",
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

 public function getInvoicePaymentReceiptById($id, Request $request)
 {
     try {
         $this->storeActivity($request,"");


         $invoice_payment_receipt = InvoicePaymentReceipt::with("invoice","invoice_payment")
         ->leftJoin('invoices', 'invoice_payment_receipts.invoice_id', '=', 'invoices.id')
         ->leftJoin('invoice_payments', 'invoice_payments.invoice_id', '=', 'invoice_payment_receipts.invoice_payment_id')
         ->where([
             "invoice_payments.generated_id" => $id,
             "invoices.created_by" => $request->user()->id
         ])
         ->select("invoice_payment_receipts.*")


         ->first();

         if(!$invoice_payment_receipt) {
      return response()->json([
 "message" => "no invoice payment found"
 ],404);
         }


         return response()->json($invoice_payment_receipt, 200);
     } catch (Exception $e) {

         return $this->sendError($e, 500,$request);
     }
 }



















}
