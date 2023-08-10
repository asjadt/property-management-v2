<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\InvoiceCreateRequest;
use App\Http\Requests\InvoiceMarkSendRequest;
use App\Http\Requests\InvoiceSendRequest;
use App\Http\Requests\InvoiceStatusUpdateRequest;
use App\Http\Requests\InvoiceUpdateRequest;

use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\SendInvoiceEmail;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceReminder;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
class InvoiceController extends Controller
{
    use ErrorUtil, UserActivityUtil;

  /**
    *
 * @OA\Post(
 *      path="/v1.0/invoice-image",
 *      operationId="createInvoiceImage",
 *      tags={"property_management.invoice_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store invoice logo",
 *      description="This method is to store invoice logo",
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

public function createInvoiceImage(ImageUploadRequest $request)
{
    try{
        $this->storeActivity($request,"");

        $insertableData = $request->validated();

        $location =  config("setup-config.invoice_image");

        $new_file_name = time() . '_' . str_replace(' ', '_', $insertableData["image"]->getClientOriginalName());

        $insertableData["image"]->move(public_path($location), $new_file_name);


        return response()->json(["image" => $new_file_name,"location" => $location,"full_location"=>("/".$location."/".$new_file_name)], 200);


    } catch(Exception $e){

        return $this->sendError($e,500,$request);
    }
}
/**
 *
 * @OA\Post(
 *      path="/v1.0/invoices",
 *      operationId="createInvoice",
 *      tags={"property_management.invoice_management"},
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
 *  *             @OA\Property(property="logo", type="string", format="string",example="image.jpg"),
  *             @OA\Property(property="invoice_title", type="string", format="string",example="invoice_title"),
 *            @OA\Property(property="invoice_summary", type="string", format="string",example="invoice_summary"),
 *

 *  *     *  * *  @OA\Property(property="reminder_dates", type="string", format="array",example={"0","15","30"
 * }),
 *
 *  *  *            @OA\Property(property="send_reminder", type="number", format="number",example="0"),
 *
 *
 *            @OA\Property(property="business_name", type="string", format="string",example="business_name"),
 *  * *  @OA\Property(property="business_address", type="string", format="string",example="business_address"),
 *
 *  *  * *  @OA\Property(property="sub_total", type="number", format="number",example="900"),
 *  * *  @OA\Property(property="total_amount", type="number", format="number",example="900"),
 *  * *  @OA\Property(property="invoice_date", type="string", format="string",example="2019-06-29"),
 *  *  * *  @OA\Property(property="invoice_reference", type="string", format="string",example="57856465"),
 *
 *
 *
 *  *  *  * *  @OA\Property(property="discount_description", type="string", format="string",example="description"),
 *  *  *  * *  @OA\Property(property="discound_type", type="string", format="string",example="fixed"),
 *  *  *  * *  @OA\Property(property="discount_amount", type="number", format="number",example="10"),
 *  *  *  * *  @OA\Property(property="due_date", type="string", format="string",example="2019-06-29"),
    *  *  * *  @OA\Property(property="status", type="string", format="string",example="draft"),
 *  * *  @OA\Property(property="footer_text", type="string", format="string",example="footer_text"),
 *  *  * *  @OA\Property(property="shareable_link", type="string", format="string",example="shareable_link"),

 *
 * *  *  @OA\Property(property="note", type="string", format="string",example="note"),
 *  *  * *  @OA\Property(property="landlord_id", type="number", format="number",example="1"),
 *  * *  @OA\Property(property="property_id", type="number", format="number",example="1"),
 *  * *  @OA\Property(property="tenant_id", type="number", format="number",example="1"),

 *     *  * *  @OA\Property(property="invoice_items", type="string", format="array",example={
 *{"name":"name","description":"description","quantity":"1","price":"1.1","tax":"20","amount":"300"},
  *{"name":"name","description":"description","quantity":"1","price":"1.1","tax":"20","amount":"300"},
    *{"name":"name","description":"description","quantity":"1","price":"1.1","tax":"20","amount":"300","repair_id":1},

 *
 * }),
 *
 *  *     *  * *  @OA\Property(property="invoice_payments", type="string", format="array",example={
 *{"amount":"10","payment_method":"payment_method","payment_date":"2019-06-29"},
 *{"amount":"10","payment_method":"payment_method","payment_date":"2019-06-29"}
 *
 * }),
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

public function createInvoice(InvoiceCreateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return DB::transaction(function () use ($request) {


            $insertableData = $request->validated();
            $insertableData["created_by"] = $request->user()->id;

            $invoiceDateWithTime = Carbon::createFromFormat('Y-m-d', $insertableData["invoice_date"]);
            $invoiceDateWithTime->setTime(Carbon::now()->hour, Carbon::now()->minute, Carbon::now()->second);
            $insertableData["invoice_date"] =    $invoiceDateWithTime;


            $reference_no_exists =  DB::table( 'invoices' )->where([
                'invoice_reference'=> $insertableData['invoice_reference'],
                "created_by" => $request->user()->id
             ]
             )->exists();
             if ($reference_no_exists) {
                $error =  [
                       "message" => "The given data was invalid.",
                       "errors" => ["invoice_reference"=>["The invoice reference has already been taken."]]
                ];
                   throw new Exception(json_encode($error),422);
               }


            $invoice =  Invoice::create($insertableData);
            if(!$invoice) {
                throw new Exception("something went wrong");
            }

            $invoice->generated_id = Str::random(4) . $invoice->id . Str::random(4);
            $invoice->shareable_link =  env("FRONT_END_URL_DASHBOARD")."/share/invoice/". Str::random(4) . "-". $invoice->generated_id ."-" . Str::random(4);

            $invoice->save();

            $invoiceItems = collect($insertableData["invoice_items"])->map(function ($item)use ($invoice) {
                if(!empty($item["repair_id"])) {
                    $invoice_item_exists =    InvoiceItem::where([
                            "repair_id" => $item["repair_id"]
                        ])
                       ->whereNotIn("invoice_id",[$invoice->id])
                        ->first();
                        if($invoice_item_exists) {
                            $error =  [
                                "message" => "The given data was invalid.",
                                "errors" => ["automobile_make_id"=>["This garage does not support this make"]]
                         ];
                            throw new Exception(json_encode($error),422);
                        }

            }

                return [
                    "name" => $item["name"],
                    "description" => $item["description"],
                    "quantity" => $item["quantity"],
                    "price" => $item["price"],
                    "tax" => $item["tax"],
                    "amount" => $item["amount"],
                    "repair_id" => !empty($item["repair_id"])?$item["repair_id"]:NULL,

                ];
            });

            $invoice->invoice_items()->createMany($invoiceItems->all());


            // $invoicePayments = collect($insertableData["invoice_payments"])->map(function ($item) {
            //     return [
            //         "amount" => $item["amount"],
            //         "payment_method" => $item["payment_method"],
            //         "payment_date" => $item["payment_date"],
            //     ];
            // });
            // $sum_payment_amounts = $invoicePayments->sum('amount');

            // if($sum_payment_amounts > $invoice->total_amount) {
            //     $error =  [
            //         "message" => "The given data was invalid.",
            //         "errors" => ["invoice_payments"=>["payment is more than total amount"]]
            //  ];
            //     throw new Exception(json_encode($error),422);
            // }



            // $invoice->invoice_payments()->createMany($invoicePayments->all());

            // if($sum_payment_amounts == $invoice->total_amount) {
            //     $invoice->status = "paid";
            //     $invoice->invoice_reminder()->delete();
            //     $invoice->save();
            //  }
            //  else {

            //  }



             if(!empty($insertableData["reminder_dates"]) &&  $invoice->status != "paid") {

                InvoiceReminder::where([
                    "invoice_id" => $invoice->id
                ])
                ->delete();
                foreach($insertableData["reminder_dates"] as $reminder_date_amount) {


                    $due_date = DateTime::createFromFormat('Y-m-d', $insertableData["due_date"]);
                    if ($due_date !== false) {
                        $due_date->modify(($reminder_date_amount . ' days'));
                        $reminder_date = $due_date->format('Y-m-d');
                    } else {
                        // Handle invalid input date format
                        // You can throw an exception, log an error, or provide a default value
                        $reminder_date = null; // or set a default value
                    }

     InvoiceReminder::create([
        "reminder_date_amount" => $reminder_date_amount,
        "reminder_status" => "not_sent",
        "send_reminder" => !empty($insertableData["send_reminder"])?$insertableData["send_reminder"]:0,
        "reminder_date" =>$reminder_date,
        "invoice_id" => $invoice->id,
        "created_by" => $invoice->created_by
    ]);


                }


            }







            $invoice = Invoice::with("invoice_items","invoice_payments","invoice_reminder")
            ->where([
                "id" => $invoice->id,
                "invoices.created_by" => $request->user()->id
            ])
            ->select("invoices.*",
            DB::raw('
                COALESCE(
                    (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                    0
                ) AS total_paid
            '),
            DB::raw('
                COALESCE(
                    invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                    invoices.total_amount
                ) AS total_due
            ')
        )

            ->first();

            if(!$invoice) {
         return response()->json([
    "message" => "no invoice found"
    ],404);
            }








            return response($invoice, 201);





        });




    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 * @OA\Put(
 *      path="/v1.0/invoices",
 *      operationId="updateInvoice",
 *      tags={"property_management.invoice_management"},
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
  *  *             @OA\Property(property="logo", type="string", format="string",example="image.jpg"),
  *             @OA\Property(property="invoice_title", type="string", format="string",example="invoice_title"),
 *            @OA\Property(property="invoice_summary", type="string", format="string",example="invoice_summary"),
 *
 *  *         *  *     *  * *  @OA\Property(property="reminder_dates", type="string", format="array",example={"0","15","30"
 * }),
 *
 *  *  *  *            @OA\Property(property="send_reminder", type="number", format="number",example="0"),
 *
 *            @OA\Property(property="business_name", type="string", format="string",example="business_name"),
 *  * *  @OA\Property(property="business_address", type="string", format="string",example="business_address"),
 *  *  * *  @OA\Property(property="sub_total", type="number", format="number",example="900"),
 *  * *  @OA\Property(property="total_amount", type="number", format="number",example="900"),
 *  * *  @OA\Property(property="invoice_date", type="string", format="string",example="2019-06-29"),

 *
 *
 *  *  *  *  * *  @OA\Property(property="discount_description", type="string", format="string",example="description"),
 *  *  *  * *  @OA\Property(property="discound_type", type="string", format="string",example="fixed"),
 *  *  *  * *  @OA\Property(property="discount_amount", type="number", format="number",example="10"),
 *  *  *  * *  @OA\Property(property="due_date", type="string", format="string",example="2019-06-29"),
 *
 *
 *
 *
 *  *  *  * *  @OA\Property(property="invoice_reference", type="string", format="string",example="57856465"),
 *     *  *  * *  @OA\Property(property="status", type="string", format="string",example="draft"),
 *  * *  @OA\Property(property="footer_text", type="string", format="string",example="footer_text"),
 *  *  *  * *  @OA\Property(property="shareable_link", type="string", format="string",example="shareable_link"),
 *  *  @OA\Property(property="note", type="string", format="string",example="note"),
 *
 *  * *  @OA\Property(property="property_id", type="number", format="number",example="1"),
 *  *  *  * *  @OA\Property(property="landlord_id", type="number", format="number",example="1"),
 *  * *  @OA\Property(property="tenant_id", type="number", format="number",example="1"),

 *     *  * *  @OA\Property(property="invoice_items", type="string", format="array",example={
 *{"id":"1","name":"name","description":"description","quantity":"1","price":"1.1","tax":"20","amount":"300"},
  *{"id":"","name":"name","description":"description","quantity":"1","price":"1.1","tax":"20","amount":"300"}
 *
 * }),
 *
 *
 *  *  *     *  * *  @OA\Property(property="invoice_payments", type="string", format="array",example={
 *{"id":"1","amount":"10","payment_method":"payment_method","payment_date":"2019-06-29"},
 *{"id":"","amount":"10","payment_method":"payment_method","payment_date":"2019-06-29"}
 *
 * }),
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

public function updateInvoice(InvoiceUpdateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return  DB::transaction(function () use ($request) {

            $updatableData = $request->validated();

            // $invoiceDateWithTime = Carbon::createFromFormat('Y-m-d', $updatableData["invoice_date"]);
            // $invoiceDateWithTime->setTime(Carbon::now()->hour, Carbon::now()->minute, Carbon::now()->second);
            // $updatableData["invoice_date"] =    $invoiceDateWithTime;
            $reference_no_exists =  DB::table( 'invoices' )->where([
                'invoice_reference'=> $updatableData['invoice_reference'],
                "created_by" => $request->user()->id
             ]
             )
             ->whereNotIn('id', [$updatableData["id"]])->exists();
             if ($reference_no_exists) {
                $error =  [
                       "message" => "The given data was invalid.",
                       "errors" => ["invoice_reference"=>["The invoice reference has already been taken."]]
                ];
                   throw new Exception(json_encode($error),422);
               }


            $invoice  =  tap(Invoice::where([
                "invoices.id" => $updatableData["id"],
                "invoices.created_by" => $request->user()->id
            ]))->update(
                collect($updatableData)->only([
                    "logo",
                    "invoice_title",
                    "invoice_summary",
                    "invoice_reference",
                    "business_name",
                    "business_address",
                    "sub_total",
                    "total_amount",
                    "invoice_date",
                    "footer_text",

                    "note",
                    "property_id",
                    "landlord_id",
                    "tenant_id",
                    "discount_description",
                    "discound_type",
                    "discount_amount",
                    "due_date",




                ])->toArray()
            )


                ->first();


                if(!$invoice) {
                    throw new Exception("something went wrong");
                }
                if(!empty($updatableData["status"])) {
                    if(($invoice->status == "draft" || $invoice->status == "unsent") && ( $updatableData["status"] == "draft" || $updatableData["status"] == "unsent")) {
                        $invoice->status = $updatableData["status"];
                        $invoice->save();
                    }

                }
                $invoice->invoice_items()->delete();
                $invoiceItemsData = collect($updatableData["invoice_items"])->map(function ($item)use ($invoice) {
                    if(!empty($item["repair_id"])) {
                    $invoice_item_exists =    InvoiceItem::where([
                            "repair_id" => $item["repair_id"]
                        ])
                       ->whereNotIn("invoice_id",[$invoice->id])
                        ->first();
                        if($invoice_item_exists) {
                            $error =  [
                                "message" => "The given data was invalid.",
                                "errors" => ["automobile_make_id"=>["This garage does not support this make"]]
                         ];
                            throw new Exception(json_encode($error),422);
                        }

                    }

                    return [
                        // "id" => $item["id"],
                        "name" => $item["name"],
                        "description" => $item["description"],
                        "quantity" => $item["quantity"],
                        "price" => $item["price"],
                        "tax" => $item["tax"],
                        "amount" => $item["amount"],
                        "invoice_id" => $invoice->id,
                        "repair_id" => !empty($item["repair_id"])?$item["repair_id"]:NULL,
                    ];
                });


                $invoice->invoice_items()->upsert($invoiceItemsData->all(), ['id',"invoice_id"], ['name', 'description', 'quantity', 'price', 'tax', 'amount',"invoice_id"]);


                // $invoicePayments = collect($updatableData["invoice_payments"])->map(function ($item)use ($invoice) {
                //     return [
                //         "id" => $item["id"],
                //         "amount" => $item["amount"],
                //         "payment_method" => $item["payment_method"],
                //         "payment_date" => $item["payment_date"],
                //         "invoice_id" => $invoice->id
                //     ];
                // });
                // $sum_payment_amounts = $invoicePayments->sum('amount');

                // if($sum_payment_amounts > $invoice->total_amount) {
                //     $error =  [
                //         "message" => "The given data was invalid.",
                //         "errors" => ["invoice_payments"=>["payment is more than total amount"]]
                //  ];
                //     throw new Exception(json_encode($error),422);
                // }


                // $invoice->invoice_payments()->upsert($invoicePayments->all(), ['id',"invoice_id"], ['amount', 'payment_method', 'payment_date', 'invoice_id']);


                // if($sum_payment_amounts == $invoice->total_amount) {
                //    $invoice->status = "paid";
                //    $invoice->invoice_reminder()->delete();
                //    $invoice->save();
                // }
                // else {

                //  }


                 if(!empty($updatableData["reminder_dates"]) &&  $invoice->status != "paid") {

                    InvoiceReminder::where([
                        "invoice_id" => $invoice->id
                    ])
                    ->delete();
                    foreach($updatableData["reminder_dates"] as $reminder_date_amount) {

                        $due_date = DateTime::createFromFormat('Y-m-d', $updatableData["due_date"]);
                        if ($due_date !== false) {
                            $due_date->modify(($reminder_date_amount . ' days'));
                            $reminder_date = $due_date->format('Y-m-d');
                        } else {
                            // Handle invalid input date format
                            // You can throw an exception, log an error, or provide a default value
                            $reminder_date = null; // or set a default value
                        }

         InvoiceReminder::create([
            "reminder_date_amount" => $reminder_date_amount,
            "reminder_status" => "not_sent",
            "send_reminder" => !empty($updatableData["send_reminder"])?$updatableData["send_reminder"]:0,
            "reminder_date" =>$reminder_date,
            "invoice_id" => $invoice->id,
            "created_by" => $invoice->created_by
        ]);

                    }


                }





                $invoice = Invoice::with("invoice_items","invoice_payments","invoice_reminder")
                ->where([
                    "id" => $invoice->id,
                    "invoices.created_by" => $request->user()->id
                ])
                ->select("invoices.*",
                DB::raw('
                    COALESCE(
                        (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                        0
                    ) AS total_paid
                '),
                DB::raw('
                    COALESCE(
                        invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                        invoices.total_amount
                    ) AS total_due
                ')
            )

                ->first();

                if(!$invoice) {
             return response()->json([
        "message" => "no invoice found"
        ],404);
                }





            return response($invoice, 200);
        });
    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Put(
 *      path="/v1.0/invoices/change/status",
 *      operationId="updateInvoiceStatus",
 *      tags={"property_management.invoice_management"},
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
 *     *  *  * *  @OA\Property(property="status", type="string", format="string",example="draft"),

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

 public function updateInvoiceStatus(InvoiceStatusUpdateRequest $request)
 {
     try {
         $this->storeActivity($request,"");
         return  DB::transaction(function () use ($request) {

             $updatableData = $request->validated();





             $invoice  =  Invoice::where([
                 "invoices.id" => $updatableData["id"],
                 "invoices.created_by" => $request->user()->id
             ])
                 ->first();


                 if(!$invoice) {
                    return response()->json([
               "message" => "no invoice found"
               ],404);
                       }

             if(($invoice->status == "draft" || $invoice->status == "unsent")) {
                            $invoice->status = $updatableData["status"];
                            $invoice->save();
            } else {
                return response()->json([
                    "message" => "you can only update status of a draft and unsent invoice"
                    ],409);
            }






                 $invoice = Invoice::with("invoice_items","invoice_payments","invoice_reminder")
                 ->where([
                     "id" => $invoice->id,
                     "invoices.created_by" => $request->user()->id
                 ])
                 ->select("invoices.*",
                 DB::raw('
                     COALESCE(
                         (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                         0
                     ) AS total_paid
                 '),
                 DB::raw('
                     COALESCE(
                         invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                         invoices.total_amount
                     ) AS total_due
                 ')
             )

                 ->first();

                 if(!$invoice) {
              return response()->json([
         "message" => "no invoice found"
         ],404);
                 }





             return response($invoice, 200);
         });
     } catch (Exception $e) {
         error_log($e->getMessage());
         return $this->sendError($e, 500,$request);
     }
 }
 /**
 *
 * @OA\Put(
 *      path="/v1.0/invoices/mark/send",
 *      operationId="invoiceMarkSend",
 *      tags={"property_management.invoice_management"},
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

 public function invoiceMarkSend(InvoiceMarkSendRequest $request)
 {
     try {
         $this->storeActivity($request,"");
         return  DB::transaction(function () use ($request) {

             $updatableData = $request->validated();



             $invoice  =  Invoice::where([
                 "invoices.id" => $updatableData["id"],
                 "invoices.created_by" => $request->user()->id
             ])
                 ->first();


                 if(!$invoice) {
                    return response()->json([
               "message" => "no invoice found"
               ],404);
                       }

                       $invoice->last_sent_date = now();


                       if($invoice->status == "unsent" || $invoice->status == "draft") {
                        $invoice->status = "sent";
                       }




                       $invoice->save();





                 $invoice = Invoice::with("invoice_items","invoice_payments","invoice_reminder")
                 ->where([
                     "id" => $invoice->id,
                     "invoices.created_by" => $request->user()->id
                 ])
                 ->select("invoices.*",
                 DB::raw('
                     COALESCE(
                         (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                         0
                     ) AS total_paid
                 '),
                 DB::raw('
                     COALESCE(
                         invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                         invoices.total_amount
                     ) AS total_due
                 ')
             )

                 ->first();

                 if(!$invoice) {
              return response()->json([
         "message" => "no invoice found"
         ],404);
                 }





             return response($invoice, 200);
         });
     } catch (Exception $e) {
         error_log($e->getMessage());
         return $this->sendError($e, 500,$request);
     }
 }

/**
 *
 * @OA\Put(
 *      path="/v1.0/invoices/send",
 *      operationId="sendInvoice",
 *      tags={"property_management.invoice_management"},
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
  *  *             @OA\Property(property="from", type="string", format="string",example="test@gmail.com"),
  *             @OA\Property(property="to", type="string", format="array",example={ "test1@gmail.com","test2@gmail.com" }),
 *            @OA\Property(property="subject", type="string", format="string",example="subject"),
 *
 *  *         *  *     *  * *  @OA\Property(property="message", type="string", format="string",example="message"),
 *
 *  *  *  *            @OA\Property(property="copy_to_myself", type="number", format="number",example="0"),
 *
 *            @OA\Property(property="attach_pdf", type="number", format="number",example="1"),
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

 public function sendInvoice(InvoiceSendRequest $request)
 {
     try {
         $this->storeActivity($request,"");
         return  DB::transaction(function () use ($request) {

             $updatableData = $request->validated();



             $invoice  =  Invoice::where([
                 "invoices.id" => $updatableData["id"],
                 "invoices.created_by" => $request->user()->id
             ])
                 ->first();


                 if(!$invoice) {
                     throw new Exception("something went wrong");
                 }


                 if($invoice->status == "draft" || $invoice->status == "unsent" ) {
                    $invoice->status = "sent";
                 }

                 $invoice->last_sent_date = now();
                 $invoice->save();


                 $recipients = $updatableData["to"];
                 if($updatableData["copy_to_myself"]) {

                    array_push($recipients,$updatableData["from"]);

                 }

                 Mail::to($recipients)
                 ->send(new SendInvoiceEmail($updatableData,$invoice));



             return response($invoice->refresh(), 200);
         });
     } catch (Exception $e) {
         error_log($e->getMessage());
         return $this->sendError($e, 500,$request);
     }
 }

 public function invoiceQuery(Request $request) {
   // $automobilesQuery = AutomobileMake::with("makes");

   $invoiceQuery = Invoice::with("invoice_items","invoice_payments","invoice_reminder","tenant","landlord")
   ->where([
        "invoices.created_by" => $request->user()->id
   ]);
   // ->leftJoin('users', 'invoices.created_by', '=', 'users.id')






   if(!empty($request->status)) {
       if($request->status == "unpaid") {
           $invoiceQuery =      $invoiceQuery->whereNotIn("status", ['draft','paid']);
       }
      else if($request->status == "next_15_days_invoice_due") {
           $currentDate = Carbon::now();
           $endDate = $currentDate->copy()->addDays(15);
           $invoiceQuery =      $invoiceQuery->whereNotIn("status", ['draft','paid']);
           $invoiceQuery =      $invoiceQuery->whereDate('invoices.due_date', '>=', $currentDate);
           $invoiceQuery =      $invoiceQuery->whereDate('invoices.due_date', '<=', $endDate);
       }
       else {
           $invoiceQuery =      $invoiceQuery->where("status", $request->status);
       }

    }







   if (!empty($request->invoice_reference)) {
       $invoiceQuery =   $invoiceQuery->where("invoices.invoice_reference", "like", "%" . $request->invoice_reference . "%");
   }

   if (!empty($request->landlord_id)) {
       $invoiceQuery =   $invoiceQuery->where("invoices.landlord_id", $request->landlord_id);
   }
   if (!empty($request->tenant_id)) {
       $invoiceQuery =   $invoiceQuery->where("invoices.tenant_id", $request->tenant_id);
   }

   if (!empty($request->property_id)) {
       $invoiceQuery =   $invoiceQuery->where("invoices.property_id", $request->property_id);
   }


   if(!empty($request->property_ids)) {
       $null_filter = collect(array_filter($request->property_ids))->values();
   $property_ids =  $null_filter->all();
       if(count($property_ids)) {
           $invoiceQuery =   $invoiceQuery->whereIn("invoices.property_id",$property_ids);
       }

   }


   // if (!empty($request->search_key)) {
   //     $invoiceQuery = $invoiceQuery->where(function ($query) use ($request) {
   //         $term = $request->search_key;
   //         $query->where("name", "like", "%" . $term . "%");
   //     });
   // }

   if (!empty($request->start_date)) {
       $invoiceQuery = $invoiceQuery->where('invoices.created_at', ">=", $request->start_date);
   }

   if (!empty($request->end_date)) {
       $invoiceQuery = $invoiceQuery->where('invoices.created_at', "<=", $request->end_date);
   }

   $invoiceQuery = $invoiceQuery
   ->select("invoices.*",
   DB::raw('
       COALESCE(
           (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
           0
       ) AS total_paid
   '),
   DB::raw('
       COALESCE(
           invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
           invoices.total_amount
       ) AS total_due
   ')
)


   ->orderBy("invoices.id",$request->order_by);
   return $invoiceQuery;

 }
/**
 *
 * @OA\Get(
 *      path="/v1.0/invoices/{perPage}",
 *      operationId="getInvoices",
 *      tags={"property_management.invoice_management"},
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
 * *  @OA\Parameter(
* name="status",
* in="query",
* description="status",
* required=true,
* example="status"
* ),

 * *  @OA\Parameter(
* name="invoice_reference",
* in="query",
* description="invoice_reference",
* required=true,
* example="1374"
* ),

 * *  @OA\Parameter(
* name="landlord_id",
* in="query",
* description="landlord_id",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="tenant_id",
* in="query",
* description="tenant_id",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="property_id",
* in="query",
* description="property_id",
* required=true,
* example="1"
* ),

*  @OA\Parameter(
*      name="property_ids[]",
*      in="query",
*      description="property_ids",
*      required=true,
*      example="1,2"
* ),
 *      summary="This method is to get invoices ",
 *      description="This method is to get invoices",
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

public function getInvoices($perPage, Request $request)
{
    try {
        $this->storeActivity($request,"");

        $invoices = $this->invoiceQuery($request)->paginate($perPage);
        return response()->json($invoices, 200);

    //   $invoices = $this->invoiceQuery($request)->paginate($perPage);


    //     $invoiceQuery = Invoice::with("invoice_items","invoice_payments","invoice_reminder")
    //     ->where([
    //          "invoices.created_by" => $request->user()->id
    //     ]);
    //     // ->leftJoin('users', 'invoices.created_by', '=', 'users.id')






    //     if(!empty($request->status)) {
    //         if($request->status == "unpaid") {
    //             $invoiceQuery =      $invoiceQuery->whereNotIn("status", ['draft','paid']);
    //         }
    //        else if($request->status == "next_15_days_invoice_due") {
    //             $currentDate = Carbon::now();
    //             $endDate = $currentDate->copy()->addDays(15);
    //             $invoiceQuery =      $invoiceQuery->whereNotIn("status", ['draft','paid']);
    //             $invoiceQuery =      $invoiceQuery->whereDate('invoices.due_date', '>=', $currentDate);
    //             $invoiceQuery =      $invoiceQuery->whereDate('invoices.due_date', '<=', $endDate);
    //         }
    //         else {
    //             $invoiceQuery =      $invoiceQuery->where("status", $request->status);
    //         }

    //      }







    //     if (!empty($request->invoice_reference)) {
    //         $invoiceQuery =   $invoiceQuery->where("invoices.invoice_reference", "like", "%" . $request->invoice_reference . "%");
    //     }

    //     if (!empty($request->landlord_id)) {
    //         $invoiceQuery =   $invoiceQuery->where("invoices.landlord_id", $request->landlord_id);
    //     }
    //     if (!empty($request->tenant_id)) {
    //         $invoiceQuery =   $invoiceQuery->where("invoices.tenant_id", $request->tenant_id);
    //     }

    //     if (!empty($request->property_id)) {
    //         $invoiceQuery =   $invoiceQuery->where("invoices.property_id", $request->property_id);
    //     }


    //     if(!empty($request->property_ids)) {
    //         $null_filter = collect(array_filter($request->property_ids))->values();
    //     $property_ids =  $null_filter->all();
    //         if(count($property_ids)) {
    //             $invoiceQuery =   $invoiceQuery->whereIn("invoices.property_id",$property_ids);
    //         }

    //     }


    //     // if (!empty($request->search_key)) {
    //     //     $invoiceQuery = $invoiceQuery->where(function ($query) use ($request) {
    //     //         $term = $request->search_key;
    //     //         $query->where("name", "like", "%" . $term . "%");
    //     //     });
    //     // }

    //     if (!empty($request->start_date)) {
    //         $invoiceQuery = $invoiceQuery->where('invoices.created_at', ">=", $request->start_date);
    //     }

    //     if (!empty($request->end_date)) {
    //         $invoiceQuery = $invoiceQuery->where('invoices.created_at', "<=", $request->end_date);
    //     }

    //     $invoices = $invoiceQuery
    //     ->select("invoices.*",
    //     DB::raw('
    //         COALESCE(
    //             (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
    //             0
    //         ) AS total_paid
    //     '),
    //     DB::raw('
    //         COALESCE(
    //             invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
    //             invoices.total_amount
    //         ) AS total_due
    //     ')
    // )


    //     ->orderByDesc("invoices.id")->paginate($perPage);

    //      return response()->json($invoices, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v1.0/invoices/get/all",
 *      operationId="getAllInvoices",
 *      tags={"property_management.invoice_management"},
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
 * *  @OA\Parameter(
* name="status",
* in="query",
* description="status",
* required=true,
* example="status"
* ),

 * *  @OA\Parameter(
* name="invoice_reference",
* in="query",
* description="invoice_reference",
* required=true,
* example="1374"
* ),

 * *  @OA\Parameter(
* name="landlord_id",
* in="query",
* description="landlord_id",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="tenant_id",
* in="query",
* description="tenant_id",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="property_id",
* in="query",
* description="property_id",
* required=true,
* example="1"
* ),

*  @OA\Parameter(
*      name="property_ids[]",
*      in="query",
*      description="property_ids",
*      required=true,
*      example="1,2"
* ),


 *      summary="This method is to get invoices ",
 *      description="This method is to get invoices",
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

 public function getAllInvoices( Request $request)
 {
     try {
         $this->storeActivity($request,"");
          $invoices = $this->invoiceQuery($request)->get();
          return response()->json($invoices, 200);


    //      // $automobilesQuery = AutomobileMake::with("makes");

    //      $invoiceQuery = Invoice::with("invoice_items","invoice_payments","invoice_reminder")
    //      ->where([
    //           "invoices.created_by" => $request->user()->id
    //      ]);
    //      // ->leftJoin('users', 'invoices.created_by', '=', 'users.id')


    //      if(!empty($request->status)) {
    //         if($request->status == "unpaid") {
    //             $invoiceQuery =      $invoiceQuery->whereNotIn("status", ['draft','paid']);
    //         }
    //        else if($request->status == "next_15_days_invoice_due") {
    //             $currentDate = Carbon::now();
    //             $endDate = $currentDate->copy()->addDays(15);
    //             $invoiceQuery =      $invoiceQuery->whereNotIn("status", ['draft','paid']);
    //             $invoiceQuery =      $invoiceQuery->whereDate('invoices.due_date', '>=', $currentDate);
    //             $invoiceQuery =      $invoiceQuery->whereDate('invoices.due_date', '<=', $endDate);
    //         }
    //         else {
    //             $invoiceQuery =      $invoiceQuery->where("status", $request->status);
    //         }

    //      }




    //      if (!empty($request->invoice_reference)) {
    //          $invoiceQuery =   $invoiceQuery->where("invoices.invoice_reference", "like", "%" . $request->invoice_reference . "%");
    //      }


    //      if(!empty($request->property_ids)) {
    //         $null_filter = collect(array_filter($request->property_ids))->values();
    //     $property_ids =  $null_filter->all();
    //         if(count($property_ids)) {
    //             $invoiceQuery =   $invoiceQuery->whereIn("invoices.property_id",$property_ids);
    //         }

    //     }

    //      // if (!empty($request->search_key)) {
    //      //     $invoiceQuery = $invoiceQuery->where(function ($query) use ($request) {
    //      //         $term = $request->search_key;
    //      //         $query->where("name", "like", "%" . $term . "%");
    //      //     });
    //      // }

    //      if (!empty($request->start_date)) {
    //          $invoiceQuery = $invoiceQuery->where('invoices.created_at', ">=", $request->start_date);
    //      }

    //      if (!empty($request->end_date)) {
    //          $invoiceQuery = $invoiceQuery->where('invoices.created_at', "<=", $request->end_date);
    //      }

    //      $invoices = $invoiceQuery
    //      ->select("invoices.*",
    //      DB::raw('
    //          COALESCE(
    //              (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
    //              0
    //          ) AS total_paid
    //      '),
    //      DB::raw('
    //          COALESCE(
    //              invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
    //              invoices.total_amount
    //          ) AS total_due
    //      ')
    //  )


    //      ->orderByDesc("invoices.id")->get();

    //      return response()->json($invoices, 200);
     } catch (Exception $e) {

         return $this->sendError($e, 500,$request);
     }
 }



/**
 *
 * @OA\Get(
 *      path="/v1.0/invoices/get/single/{id}",
 *      operationId="getInvoiceById",
 *      tags={"property_management.invoice_management"},
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

public function getInvoiceById($id, Request $request)
{
    try {
        $this->storeActivity($request,"");


        $invoice = Invoice::with("invoice_items","invoice_payments","invoice_reminder","tenant","landlord","property")
        ->where([
            "generated_id" => $id,
            "invoices.created_by" => $request->user()->id
        ])
        ->select("invoices.*",
        DB::raw('
            COALESCE(
                (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                0
            ) AS total_paid
        '),
        DB::raw('
            COALESCE(
                invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                invoices.total_amount
            ) AS total_due
        ')
    )

        ->first();

        if(!$invoice) {
     return response()->json([
"message" => "no invoice found"
],404);
        }


        return response()->json($invoice, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v1.0/invoices/get/single-by-reference/{reference}",
 *      operationId="getInvoiceByReference",
 *      tags={"property_management.invoice_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },

 *              @OA\Parameter(
 *         name="reference",
 *         in="path",
 *         description="reference",
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

 public function getInvoiceByReference($reference, Request $request)
 {
     try {
         $this->storeActivity($request,"");


         $invoice = Invoice::with("invoice_items","invoice_payments","invoice_reminder","tenant","landlord","property")
         ->where([
             "invoice_reference" => $reference,
             "invoices.created_by" => $request->user()->id
         ])
         ->select("invoices.*",
         DB::raw('
             COALESCE(
                 (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                 0
             ) AS total_paid
         '),
         DB::raw('
             COALESCE(
                 invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                 invoices.total_amount
             ) AS total_due
         ')
     )

         ->first();

         if(!$invoice) {
      return response()->json([
 "message" => "no invoice found"
 ],404);
         }


         return response()->json($invoice, 200);
     } catch (Exception $e) {

         return $this->sendError($e, 500,$request);
     }
 }











/**
 *
 *     @OA\Delete(
 *      path="/v1.0/invoices/{id}",
 *      operationId="deleteInvoiceById",
 *      tags={"property_management.invoice_management"},
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

public function deleteInvoiceById($id, Request $request)
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



        $invoice = Invoice::where([
            "id" => $id,
            "invoices.created_by" => $request->user()->id
        ])
        ->first();

        if(!$invoice) {
     return response()->json([
"message" => "no invoice found"
],404);
        }
        $invoice->delete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 *     @OA\Delete(
 *      path="/v1.0/invoice-items/{invoice_id}/{id}",
 *      operationId="deleteInvoiceItemById",
 *      tags={"property_management.invoice_management"},
 *       security={
 *           {"bearerAuth": {}},
 *            {"pin": {}}
 *       },
 *  *              @OA\Parameter(
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
 *      summary="This method is to delete invoice item by id",
 *      description="This method is to delete invoice item by id",
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

public function deleteInvoiceItemById($invoice_id,$id, Request $request)
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



        $invoice_item = InvoiceItem::
        leftJoin('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
        ->where([
            "invoice_items.invoice_id" => $invoice_id,
            "invoice_items.id" => $id,
            "invoices.created_by" => $request->user()->id
        ])
        ->first();

        if(!$invoice_item) {
     return response()->json([
"message" => "no invoice item found"
],404);
        }
        $invoice_item->forceDelete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}








/**
 *
 * @OA\Get(
 *      path="/v1.0/invoices/generate/invoice-reference",
 *      operationId="generateInvoiceReference",
 *      tags={"property_management.invoice_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },



 *      summary="This method is to generate invoice reference",
 *      description="This method is to generate invoice reference",
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
 public function generateInvoiceReference(Request $request)
 {
     try {
         $this->storeActivity($request,"");

        //  do {
        //     $invoice_reference = mt_rand( 1000000000, 9999999999 );
        //  } while (
        //     DB::table( 'invoices' )->where( [
        //     'invoice_reference'=> $invoice_reference,
        //     "created_by" => $request->user()->id
        //  ]
        //  )->exists()
        // );

        // do {
        //     $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-_/';
        //     $invoice_reference = '';
        //     $length = 10; // adjust the length as needed

        //     for ($i = 0; $i < $length; $i++) {
        //         $invoice_reference .= $characters[rand(0, strlen($characters) - 1)];
        //     }
        // } while (
        //     DB::table('invoices')->where([
        //         'invoice_reference' => $invoice_reference,
        //         'created_by' => $request->user()->id
        //     ])->exists()
        // );

        $current_number = 1; // Start from 0001

        do {
            $invoice_reference = str_pad($current_number, 4, '0', STR_PAD_LEFT);
            $current_number++; // Increment the current number for the next iteration
        } while (
            DB::table('invoices')->where([
                'invoice_reference' => $invoice_reference,
                'created_by' => $request->user()->id
            ])->exists()
        );
return response()->json(["invoice_reference" => $invoice_reference],200);

     } catch (Exception $e) {
         error_log($e->getMessage());
         return $this->sendError($e, 500,$request);
     }
 }



 /**
 *
 * @OA\Get(
 *      path="/v1.0/invoices/validate/invoice-reference/{invoice_reference}",
 *      operationId="validateInvoiceReference",
 *      tags={"property_management.invoice_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },

 *              @OA\Parameter(
 *         name="invoice_reference",
 *         in="path",
 *         description="invoice_reference",
 *         required=true,
 *  example="1"
 *      ),

 *      summary="This method is to validate invoice number",
 *      description="This method is to validate invoice number",
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
public function validateInvoiceReference($invoice_reference, Request $request)
{
    try {
        $this->storeActivity($request,"");

        $invoice_reference_exists =  DB::table( 'invoices' )->where( [
           'invoice_reference'=> $invoice_reference,
           "created_by" => $request->user()->id
        ]
        )->exists();



return response()->json(["invoice_reference_exists" => $invoice_reference_exists],200);

    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
    }
}






}
