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
use App\Models\Landlord;
use App\Models\Tenant;
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

            $invoiceDateWithTime = Carbon::createFromFormat('Y-m-d', $insertableData["payment_date"]);
            $invoiceDateWithTime->setTime(Carbon::now()->hour, Carbon::now()->minute, Carbon::now()->second);
            $insertableData["payment_date"] =    $invoiceDateWithTime;

            $invoice = Invoice::where([
                "id" => $insertableData["invoice_id"],
                "invoices.created_by" => $request->user()->id

            ])
            ->first();
            if(!$invoice) {
                 return response()->json(["message" => "no invoice found or you did not create the invoice"]);
            }

            $sum_payment_amounts = $invoice->invoice_payments()->sum('amount');
            $invoice_due = $invoice->total_amount - $sum_payment_amounts;



            if($invoice_due < $insertableData["amount"]) {
                $error =  [
                    "message" => "The given data was invalid.",
                    "errors" => ["amount"=>["amount is more than total amount"]]
             ];
                throw new Exception(json_encode($error),422);
            }
           else if($invoice_due == $insertableData["amount"]) {
               $invoice->status = "paid";
               $invoice->invoice_reminder()->delete();
               $invoice->save();
            }
            else {
                $invoice->status = "partial";
                $invoice->save();
             }



            $invoice_payment =  InvoicePayment::create($insertableData);

            if(!$invoice_payment) {
                throw new Exception("something went wrong");
            }



        //     // email section
        //  $recipients = [$request->user()->email];

        //  $tenant =  Tenant::where(["id" => $invoice->tenant_id])->first();
        //  if($tenant) {
        //     array_push($recipients,$tenant->email);
        //  }
        //  $landlord =  Landlord::where(["id" => $invoice->tenant_id])->first();
        //  if($landlord) {
        //     array_push($recipients,$landlord->email);
        //  }

        //  Mail::to($recipients)
        //  ->send(new PaymentEmail($invoice,$invoice_payment));
        //     // end email section








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

            $invoiceDateWithTime = Carbon::createFromFormat('Y-m-d', $updatableData["payment_date"]);
            $invoiceDateWithTime->setTime(Carbon::now()->hour, Carbon::now()->minute, Carbon::now()->second);
            $updatableData["payment_date"] =    $invoiceDateWithTime;

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
                "id" => $updatableData["invoice_id"],

            "invoices.created_by" => $request->user()->id

            ])
            ->first();
            if(!$invoice) {
                throw new Exception("something went wrong");
            }

            $sum_payment_amounts = $invoice->invoice_payments()->where('id', '!=', $updatableData["id"])->sum('amount');

            $invoice_due = $invoice->total_amount - $sum_payment_amounts;


            if($invoice_due < $updatableData["amount"]) {
                $error =  [
                    "message" => "The given data was invalid.",
                    "errors" => ["amount"=>["amount is more than total amount"]]
             ];
                throw new Exception(json_encode($error),422);
            }
           else if($invoice_due == $updatableData["amount"]) {
               $invoice->status = "paid";
               $invoice->invoice_reminder()->delete();
               $invoice->save();
            }
            else {
                $invoice->status = "partial";
                $invoice->save();
             }

            $invoice_payment  =  tap(InvoicePayment::leftJoin('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
            ->where([
                "invoice_payments.id" => $updatableData["id"],
                "invoices.created_by" => $request->user()->id
            ])

            )->update(
                collect($updatableData)->only([
                    "amount",
                    "payment_method",
                    "payment_date",
                    "invoice_id",
                    "note"
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

        $invoice_paymentQuery =  InvoicePayment::leftJoin('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
        ->where([
            "invoices.created_by" => $request->user()->id
        ]);


        if (!empty($request->invoice_id)) {
            $invoice_paymentQuery = $invoice_paymentQuery->where('invoice_payments.invoice_id',  $request->invoice_id);
        }

        // if (!empty($request->search_key)) {
        //     $invoice_paymentQuery = $invoice_paymentQuery->where(function ($query) use ($request) {
        //         $term = $request->search_key;
        //         $query->where("name", "like", "%" . $term . "%");
        //     });
        // }

        if (!empty($request->start_date)) {
            $invoice_paymentQuery = $invoice_paymentQuery->where('invoice_payments.created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $invoice_paymentQuery = $invoice_paymentQuery->where('invoice_payments.created_at', "<=", $request->end_date);
        }

        $invoice_payments = $invoice_paymentQuery
        ->select("invoice_payments.*")
        ->orderByDesc("invoice_payments.id")
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


        $invoice_payment = InvoicePayment::leftJoin('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
        ->where([
            "invoice_payments.id" => $id,
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


         $invoice_payment = InvoicePayment::leftJoin('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
         ->where([
             "invoice_payments.id" => $id,
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
        ->select("invoice_payments.id")
        ->first();

        if(!$invoice_payment) {
     return response()->json([
"message" => "no invoice payment found"
],404);
        }
        $invoice_payment->delete();

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
         ->select("invoice_payments.id")
         ->first();

         if(!$invoice_payment) {
      return response()->json([
 "message" => "no invoice payment found"
 ],404);
         }
         $invoice_payment->delete();

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



            $updatableData = $request->validated();



            $invoice  =  Invoice::where([
                "invoices.id" => $updatableData["invoice_id"],
                "invoices.created_by" => $request->user()->id
            ])
                ->first();


                if(!$invoice) {
                   return response()->json(["message" => "mo invoice found"],404);
                }

                $invoice_payment = InvoicePayment::where([
                    "invoice_id" =>  $invoice->id,
                    "id" => $updatableData["invoice_payment_id"]
                ])
                    ->first();

                    if(!$invoice_payment) {
                        return response()->json(["message" => "mo invoice payment found"],404);
                    }


                $recipients = $updatableData["to"];
                if($updatableData["copy_to_myself"]) {

                   array_push($recipients,$updatableData["from"]);

                }


          Mail::to($recipients)
          ->send(new PaymentEmail($invoice,$invoice_payment,$updatableData));
             // end email section

             $updatableData["to"] =  json_encode($updatableData["to"]);

         $invoice_payment_receipt =  InvoicePaymentReceipt::create($updatableData);



           $invoice_payment_receipt->shareable_link =  env("FRONT_END_URL_DASHBOARD")."/share/receipt/". Str::random(4) . "-". $invoice->id ."-" . Str::random(4);






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
         ->leftJoin('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
         ->where([
             "invoices.created_by" => $request->user()->id
         ]);


         if (!empty($request->invoice_id)) {
             $invoice_payment_receiptQuery = $invoice_payment_receiptQuery->where('invoice_payments.invoice_id',  $request->invoice_id);
         }

         // if (!empty($request->search_key)) {
         //     $invoice_paymentQuery = $invoice_paymentQuery->where(function ($query) use ($request) {
         //         $term = $request->search_key;
         //         $query->where("name", "like", "%" . $term . "%");
         //     });
         // }

         if (!empty($request->start_date)) {
             $invoice_payment_receiptQuery = $invoice_payment_receiptQuery->where('invoice_payments.created_at', ">=", $request->start_date);
         }
         if (!empty($request->end_date)) {
             $invoice_payment_receiptQuery = $invoice_payment_receiptQuery->where('invoice_payments.created_at', "<=", $request->end_date);
         }

         $invoice_payment_receipts = $invoice_payment_receiptQuery
         ->select("invoice_payment_receipts.*")
         ->orderByDesc("invoice_payments.id")->paginate($perPage);

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
         ->leftJoin('invoices', 'invoice_payments.invoice_id', '=', 'invoices.id')
         ->where([
             "invoice_payments.id" => $id,
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
