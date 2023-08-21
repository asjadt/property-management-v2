<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillCreateRequest;
use App\Http\Requests\BillUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\SendInvoiceEmail;
use App\Models\Bill;
use App\Models\BillBillItem;
use App\Models\BillRepairItem;
use App\Models\BillSaleItem;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\InvoiceReminder;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BillController extends Controller
{
    use ErrorUtil, UserActivityUtil;


  /**
   *
   * @OA\Post(
   *      path="/v1.0/bills",
   *      operationId="createBill",
   *      tags={"property_management.bill_management"},
   *       security={
   *           {"bearerAuth": {}}
   *       },
   *      summary="This method is to store bill",
   *      description="This method is to store bill",
   *
   *  @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *            required={"name","description","logo"},
   *  *             @OA\Property(property="create_date", type="string", format="string",example="2019-06-29"),
    *             @OA\Property(property="property_id", type="number", format="number",example="1"),
   *            @OA\Property(property="landlord_id", type="number", format="number",example="1"),
   *
   *
   *  *  *            @OA\Property(property="payment_mode", type="string", format="string",example="card"),
   *
   *
   *            @OA\Property(property="payabble_amount", type="number", format="number",example="10.10"),
   *
   *  * *  @OA\Property(property="remarks", type="string", format="string",example="remarks"),
   *
   *
   *
   *
   *
   *     *  * *  @OA\Property(property="bill_items", type="string", format="array",example={
   *{"bill_item_id":"1","item":"item","description":"description","amount":"10.1"},
    *{"bill_item_id":"2","item":"item","description":"description","amount":"10.1"},
   * }),
   *
   *     *  * *  @OA\Property(property="sale_items", type="string", format="array",example={
   *{"sale_id":"1","item":"item","description":"description","amount":"10.1"},
    *{"sale_id":"2","item":"item","description":"description","amount":"10.1"},
   * }),
   *
   *    *     *  * *  @OA\Property(property="repair_items", type="string", format="array",example={
   *{"repair_id":"1","item":"item","description":"description","amount":"10.1"},
    *{"repair_id":"2","item":"item","description":"description","amount":"10.1"},
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

  public function createBill(BillCreateRequest $request)
  {
      try {
          $this->storeActivity($request,"");
          return DB::transaction(function () use ($request) {
            $business = Business::where([
                "owner_id" => $request->user()->id
              ])->first();

              $insertableData = $request->validated();
              $insertableData["created_by"] = $request->user()->id;

              $invoiceDateWithTime = Carbon::createFromFormat('Y-m-d', $insertableData["create_date"]);
              $invoiceDateWithTime->setTime(Carbon::now()->hour, Carbon::now()->minute, Carbon::now()->second);
              $insertableData["create_date"] =    $invoiceDateWithTime;

              $bill =  Bill::create($insertableData);
              if(!$bill) {
                throw new Exception("something went wrong");
            }

            $bill->generated_id = Str::random(4) . $bill->id . Str::random(4);
            $bill->shareable_link =  env("FRONT_END_URL_DASHBOARD")."/share/invoice/". Str::random(4) . "-". $bill->generated_id ."-" . Str::random(4);

            $bill->save();

              $bill_items = collect($insertableData["bill_items"])->map(function ($item)use ($bill) {

                    // $bill_item_exists =    BillBillItem::where([
                    //         "bill_item_id" => $item["bill_item_id"]
                    //     ])
                    //    ->whereNotIn("bill_id",[$bill->id])
                    //     ->first();
                    //     if($bill_item_exists) {
                    //         $error =  [
                    //             "message" => "The given data was invalid.",
                    //             "errors" => ["bill_items"=>["invalid item"]]
                    //      ];
                    //         throw new Exception(json_encode($error),422);
                    //     }



                return [
                    "item" => $item["item"],
                    "description" => $item["description"],
                    "amount" => $item["amount"],
                    "bill_item_id" => $item["bill_item_id"],

                ];
            });

            $bill->bill_bill_items()->createMany($bill_items->all());

            $sale_items = collect($insertableData["sale_items"])->map(function ($item)use ($bill) {

                // $sale_items_exists =    BillSaleItem::where([
                //         "sale_id" => $item["sale_id"]
                //     ])
                //    ->whereNotIn("bill_id",[$bill->id])
                //     ->first();
                //     if($sale_items_exists) {
                //         $error =  [
                //             "message" => "The given data was invalid.",
                //             "errors" => ["sale_items"=>["invalid item"]]
                //      ];
                //         throw new Exception(json_encode($error),422);
                //     }



            return [
                "item" => $item["item"],
                "description" => $item["description"],
                "amount" => $item["amount"],
                "sale_id" => $item["sale_id"],

            ];
        });

        $bill->bill_sale_items()->createMany($sale_items->all());

        $repair_items = collect($insertableData["repair_items"])->map(function ($item)use ($bill) {

            $repair_items_exists =    BillRepairItem::where([
                    "repair_id" => $item["repair_id"]
                ])
               ->whereNotIn("bill_id",[$bill->id])
                ->first();
                if($repair_items_exists) {
                    $error =  [
                        "message" => "The given data was invalid.",
                        "errors" => ["repair_items"=>["invalid item"]]
                 ];
                    throw new Exception(json_encode($error),422);
                }



        return [
            "item" => $item["item"],
            "description" => $item["description"],
            "amount" => $item["amount"],
            "repair_id" => $item["repair_id"],

        ];
    });

    $bill->bill_repair_items()->createMany($repair_items->all());


    if(!empty($repair_items->all()) || !empty($sale_items->all())) {

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


        $invoice_data = [
          "logo"=> $business->logo,
          "invoice_title"=> $business->invoice_title,

          "invoice_reference" => $invoice_reference,
          "business_name"=>$business->name,
          "business_address"=>$business->address_line_1,

          "invoice_date"=>$bill->create_date,

          "footer_text"=>$business->footer_text,


          "property_id"=>$bill->property_id,

          "status"=>"paid",

          "landlord_id" =>  $bill->landlord_id,

          "sub_total"=>$bill->payabble_amount,
          "total_amount"=>$bill->payabble_amount,

          "bill_id" => $bill->id,
          'created_by' => $request->user()->id

      ];

      // Bill Adjustment
        $invoice =  Invoice::create($invoice_data);
        if(!$invoice) {
            throw new Exception("something went wrong");
        }

        $invoice->generated_id = Str::random(4) . $invoice->id . Str::random(4);
        $invoice->shareable_link =  env("FRONT_END_URL_DASHBOARD")."/share/invoice/". Str::random(4) . "-". $invoice->generated_id ."-" . Str::random(4);

        $invoice->save();

         $invoice_items_data = $repair_items->merge($sale_items);

        $invoiceItems = $invoice_items_data->map(function ($item)use ($invoice) {
            if(!empty($item["repair_id"])) {
                $invoice_item_exists =    InvoiceItem::where([
                        "repair_id" => $item["repair_id"]
                    ])
                   ->whereNotIn("invoice_id",[$invoice->id])
                    ->first();
                    if($invoice_item_exists) {
                        $error =  [
                            "message" => "The given data was invalid.",
                            "errors" => ["invoice_items"=>["invalid repair item"]]
                     ];
                        throw new Exception(json_encode($error),422);
                    }

        }
    //     if(!empty($item["sale_id"])) {
    //         $invoice_item_exists =    InvoiceItem::where([
    //                 "sale_id" => $item["sale_id"]
    //             ])
    //            ->whereNotIn("invoice_id",[$invoice->id])
    //             ->first();
    //             if($invoice_item_exists) {
    //                 $error =  [
    //                     "message" => "The given data was invalid.",
    //                     "errors" => ["invoice_items"=>["invalid sale item"]]
    //              ];
    //                 throw new Exception(json_encode($error),422);
    //             }

    // }


            return [
                "name" => $item["item"],
                "description" => $item["description"],
                "quantity" => 1,
                "price" => $item["amount"],
                "tax" => 0,
                "amount" => $item["amount"],
                "repair_id" => !empty($item["repair_id"])?$item["repair_id"]:NULL,
                "sale_id" => !empty($item["sale_id"])?$item["sale_id"]:NULL,
            ];
        });

        $invoice->invoice_items()->createMany($invoiceItems->all());

        $invoice_data = [
            "logo"=> $business->logo,
            "invoice_title"=> $business->invoice_title,

            "invoice_reference" => $invoice_reference,
            "business_name"=>$business->name,
            "business_address"=>$business->address_line_1,

            "invoice_date"=>$bill->create_date,

            "footer_text"=>$business->footer_text,


            "property_id"=>$bill->property_id,

            "status"=>"paid",

            "landlord_id" =>  $bill->landlord_id,

            "sub_total"=>$bill->payabble_amount,
            "total_amount"=>$bill->payabble_amount,

            "bill_id" => $bill->id,

        ];
      $invoice_payment =  InvoicePayment::create([
            "amount" => $bill->payabble_amount,
            "payment_method" => "Bill Adjustment",
            "payment_date" => $bill->create_date ,
            "note" => "note",
            "invoice_id" => $invoice->id,
            "receipt_by" => $request->user()->id
        ]);

        if(!$invoice_payment) {
            throw new Exception("something went wrong");
        }
        $invoice_payment->generated_id = Str::random(4) . $invoice_payment->id . Str::random(4);

        $invoice_payment->shareable_link = env("FRONT_END_URL_DASHBOARD")."/share/receipt/". Str::random(4) . "-". $invoice_payment->generated_id ."-" . Str::random(4);

        $invoice_payment->save();



    }



              return response($bill, 201);





          });




      } catch (Exception $e) {

          return $this->sendError($e, 500,$request);
      }
  }


  /**
   *
   * @OA\Put(
   *      path="/v1.0/bills",
   *      operationId="updateBill",
   *      tags={"property_management.bill_management"},
   *       security={
   *           {"bearerAuth": {}}
   *       },
   *      summary="This method is to update bill",
   *      description="This method is to update bill",
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
   *   *  * *  *  @OA\Property(property="business_type", type="string", format="string",example="note"),
   *  * *  @OA\Property(property="property_id", type="number", format="number",example="1"),
   *  *  *  * *  @OA\Property(property="landlord_id", type="number", format="number",example="1"),
   *  * *  @OA\Property(property="tenant_id", type="number", format="number",example="1"),
   *  *  * *  @OA\Property(property="client_id", type="number", format="number",example="1"),
   *

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

  public function updateBill(BillUpdateRequest $request)
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
                      "client_id",
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


   public function billQuery(Request $request) {
     // $automobilesQuery = AutomobileMake::with("makes");

     $billQuery = Bill::with("bill_bill_items","bill_sale_items","bill_repair_items")
     ->leftJoin('invoices', 'invoices.bill_id', '=', 'bills.id')
     ->where([
          "bills.created_by" => $request->user()->id
     ])
   ;






     if(!empty($request->status)) {
         if($request->status == "unpaid") {
             $billQuery =      $billQuery->whereNotIn("invoices.status", ['draft','paid']);
         }
        else if($request->status == "next_15_days_invoice_due") {
             $currentDate = Carbon::now();
             $endDate = $currentDate->copy()->addDays(15);
             $billQuery =      $billQuery->whereNotIn("invoices.status", ['draft','paid']);
             $billQuery =      $billQuery->whereDate('invoices.due_date', '>=', $currentDate);
             $billQuery =      $billQuery->whereDate('invoices.due_date', '<=', $endDate);
         }
         else {
             $billQuery =      $billQuery->where("status", $request->status);
         }

      }







     if (!empty($request->invoice_reference)) {
         $billQuery =   $billQuery->where("invoices.invoice_reference", "like", "%" . $request->invoice_reference . "%");
     }

     if (!empty($request->landlord_id)) {
         $billQuery =   $billQuery->where("bills.landlord_id", $request->landlord_id);
     }
     if (!empty($request->tenant_id)) {
         $billQuery =   $billQuery->where("invoices.tenant_id", $request->tenant_id);
     }
     if (!empty($request->client_id)) {
      $billQuery =   $billQuery->where("invoices.client_id", $request->client_id);
  }


     if (!empty($request->property_id)) {
         $billQuery =   $billQuery->where("bills.property_id", $request->property_id);
     }


     if(!empty($request->property_ids)) {
         $null_filter = collect(array_filter($request->property_ids))->values();
     $property_ids =  $null_filter->all();
         if(count($property_ids)) {
             $billQuery =   $billQuery->whereIn("bills.property_id",$property_ids);
         }

     }



     if (!empty($request->start_date)) {
         $billQuery = $billQuery->where('bills.created_at', ">=", $request->start_date);
     }

     if (!empty($request->end_date)) {
         $billQuery = $billQuery->where('bills.created_at', "<=", $request->end_date);
     }

     $billQuery = $billQuery
     ->select(
        "bills.*",
        // "invoices.*",
    //  DB::raw('
    //      COALESCE(
    //          (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
    //          0
    //      ) AS total_paid
    //  '),
    //  DB::raw('
    //      COALESCE(
    //          invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
    //          invoices.total_amount
    //      ) AS total_due
    //  ')
  );
//   if(!empty($request->min_total_due)) {
//       $billQuery = $billQuery->havingRaw("total_due >= " . $request->min_total_due . "");
//   }
//   if(!empty($request->max_total_due)) {
//       $billQuery = $billQuery->havingRaw("total_due <= " . $request->max_total_due . "");
//   }
  $billQuery = $billQuery->orderBy("bills.id",$request->order_by);
     return $billQuery;

   }
  /**
   *
   * @OA\Get(
   *      path="/v1.0/bills/{perPage}",
   *      operationId="getBills",
   *      tags={"property_management.bill_management"},
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
  * name="client_id",
  * in="query",
  * description="client_id",
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
   * *  @OA\Parameter(
  * name="min_total_due",
  * in="query",
  * description="min_total_due",
  * required=true,
  * example="1"
  * ),
   * *  @OA\Parameter(
  * name="max_total_due",
  * in="query",
  * description="max_total_due",
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
   *      summary="This method is to get bills ",
   *      description="This method is to get bills",
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

  public function getBills($perPage, Request $request)
  {
      try {
          $this->storeActivity($request,"");

          $bills = $this->billQuery($request)->paginate($perPage);
          return response()->json($bills, 200);


      } catch (Exception $e) {

          return $this->sendError($e, 500,$request);
      }
  }
  /**
   *
   * @OA\Get(
   *      path="/v1.0/bills/get/all",
   *      operationId="getAllBills",
   *      tags={"property_management.bill_management"},
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
  * name="client_id",
  * in="query",
  * description="client_id",
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
   * *  @OA\Parameter(
  * name="min_total_due",
  * in="query",
  * description="min_total_due",
  * required=true,
  * example="1"
  * ),
   * *  @OA\Parameter(
  * name="total_due_max",
  * in="query",
  * description="total_due",
  * required=true,
  * example="1"
  * ),


   *      summary="This method is to get bills ",
   *      description="This method is to get bills",
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

   public function getAllBills( Request $request)
   {
       try {
           $this->storeActivity($request,"");
            $bills = $this->billQuery($request)->get();
            return response()->json($bills, 200);



       } catch (Exception $e) {

           return $this->sendError($e, 500,$request);
       }
   }



  /**
   *
   * @OA\Get(
   *      path="/v1.0/bills/get/single/{id}",
   *      operationId="getBillById",
   *      tags={"property_management.bill_management"},
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

   *      summary="This method is to get bill by id",
   *      description="This method is to get bill by id",
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

  public function getBillById($id, Request $request)
  {
      try {
          $this->storeActivity($request,"");


          $bill = Bill::with("bill_bill_items","bill_sale_items","bill_repair_items")
          ->where([
              "generated_id" => $id,
              "bills.created_by" => $request->user()->id
          ])
          ->select(
            "bills.*"
        //     "invoices.*",
        //   DB::raw('
        //       COALESCE(
        //           (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
        //           0
        //       ) AS total_paid
        //   '),
        //   DB::raw('
        //       COALESCE(
        //           invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
        //           invoices.total_amount
        //       ) AS total_due
        //   ')
      )

          ->first();

          if(!$bill) {
       return response()->json([
  "message" => "no bill found"
  ],404);
          }


          return response()->json($bill, 200);
      } catch (Exception $e) {

          return $this->sendError($e, 500,$request);
      }
  }










  /**
   *
   *     @OA\Delete(
   *      path="/v1.0/bills/{id}",
   *      operationId="deleteBillById",
   *      tags={"property_management.bill_management"},
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
   *      summary="This method is to delete bill by id",
   *      description="This method is to delete bill by id",
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

  public function deleteBillById($id, Request $request)
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


          $bill = Bill::where([
              "id" => $id,
              "bills.created_by" => $request->user()->id
          ])
          ->first();

          if(!$bill) {
       return response()->json([
  "message" => "no invoice found"
  ],404);
          }
          $bill->delete();

          return response()->json(["ok" => true], 200);
      } catch (Exception $e) {

          return $this->sendError($e, 500,$request);
      }
  }
















}
