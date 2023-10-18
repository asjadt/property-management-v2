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
use PDF;

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
   *    *  *             @OA\Property(property="payment_date", type="string", format="string",example="2019-06-29"),
   *
    *             @OA\Property(property="property_id", type="number", format="number",example="1"),
   *            @OA\Property(property="landlord_id", type="number", format="number",example="1"),
   *
   *
   *  *  *            @OA\Property(property="payment_mode", type="string", format="string",example="card"),
   *
   *
   *            @OA\Property(property="payabble_amount", type="number", format="number",example="10.10"),
   *  *            @OA\Property(property="deduction", type="number", format="number",example="10.10"),
   *
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
              $invoicePaymentDateWithTime = Carbon::createFromFormat('Y-m-d', $insertableData["payment_date"]);
              $invoicePaymentDateWithTime->setTime(Carbon::now()->hour, Carbon::now()->minute, Carbon::now()->second);
              $insertableData["payment_date"] =    $invoicePaymentDateWithTime;


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
          "invoice_title"=> (!empty($business->invoice_title)?$business->invoice_title:"Invoice"),

          "invoice_reference" => $invoice_reference,
          "business_name"=>$business->name,
          "business_address"=>$business->address_line_1,

          "invoice_date"=>$bill->create_date,
          "due_date" => $bill->create_date,
          "footer_text"=>(!empty($business->footer_text)?$business->footer_text:"Thanks for business with us"),


          "property_id"=>$bill->property_id,

          "status"=>"paid",

          "landlord_id" =>  $bill->landlord_id,

          "sub_total"=>$bill->deduction,
          "total_amount"=>$bill->deduction,

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

        $invoice_items_data = $sale_items->merge($repair_items);

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


      $invoice_payment =  InvoicePayment::create([
            "amount" => $bill->deduction,
            "payment_method" => "Bill Adjustment",
            "payment_date" => $bill->payment_date ,
            "note" => "Invoice cleared against BiLL ID " . $bill->id,
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
    *  *             @OA\Property(property="create_date", type="string", format="string",example="2019-06-29"),
        *  *             @OA\Property(property="payment_date", type="string", format="string",example="2019-06-29"),

    *             @OA\Property(property="property_id", type="number", format="number",example="1"),
   *            @OA\Property(property="landlord_id", type="number", format="number",example="1"),
   *
   *
   *  *  *            @OA\Property(property="payment_mode", type="string", format="string",example="card"),
   *
   *
   *            @OA\Property(property="payabble_amount", type="number", format="number",example="10.10"),
   *    *            @OA\Property(property="deduction", type="number", format="number",example="10.10"),
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

  public function updateBill(BillUpdateRequest $request)
  {
      try {
          $this->storeActivity($request,"");
          return  DB::transaction(function () use ($request) {
            $business = Business::where([
                "owner_id" => $request->user()->id
              ])->first();

              $updatableData = $request->validated();

              $bill  =  tap(Bill::where([
                "bills.id" => $updatableData["id"],
                "bills.created_by" => $request->user()->id
            ]))->update(
                collect($updatableData)->only([
                    'create_date',
                    "payment_date",
                    'property_id',
                    'landlord_id',
                    'payment_mode',
                    "payabble_amount",
                    "deduction",
                    "remarks",
                ])->toArray()
            )
                ->first();

                $bill->bill_bill_items()->delete();
                $bill_items = collect($updatableData["bill_items"])->map(function ($item)use ($bill) {

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

            $bill->bill_sale_items()->delete();
            $sale_items = collect($updatableData["sale_items"])->map(function ($item)use ($bill) {

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

 $bill->bill_repair_items()->delete();
        $repair_items = collect($updatableData["repair_items"])->map(function ($item)use ($bill) {

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
          "invoice_title"=> (!empty($business->invoice_title)?$business->invoice_title:"Invoice"),

          "invoice_reference" => $invoice_reference,

          "business_name"=>$business->name,
          "business_address"=>$business->address_line_1,

          "invoice_date"=>$bill->create_date,
          "due_date" => $bill->create_date,
          "footer_text"=>(!empty($business->footer_text)?$business->footer_text:"Thanks for business with us"),


          "property_id"=>$bill->property_id,

          "status"=>"paid",

          "landlord_id" =>  $bill->landlord_id,

          "sub_total"=>$bill->deduction,
          "total_amount"=>$bill->deduction,

          "bill_id" => $bill->id,
          'created_by' => $request->user()->id

                    ];
$invoice_prev = Invoice::where([
    "invoices.bill_id" => $updatableData["id"],
    "invoices.created_by" => $request->user()->id
])->first();

           if($invoice_prev) {
            $invoice  =  tap(Invoice::where([
                "invoices.bill_id" => $updatableData["id"],
                "invoices.created_by" => $request->user()->id
            ]))->update(
                collect($invoice_data)->only([
                    "logo",
                    "invoice_title",
                    "invoice_summary",
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
           } else {
            $invoice  = Invoice::create($invoice_data);
            $invoice->generated_id = Str::random(4) . $invoice->id . Str::random(4);
            $invoice->shareable_link =  env("FRONT_END_URL_DASHBOARD")."/share/invoice/". Str::random(4) . "-". $invoice->generated_id ."-" . Str::random(4);

            $invoice->save();
           }




                     $invoice_items_data = $sale_items->merge($repair_items);

                     $invoice->invoice_items()->delete();
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

                    InvoicePayment::where(
                        [
                            "invoice_id" => $invoice->id
                        ]
                    )
                    ->delete();
                  $invoice_payment =  InvoicePayment::create([
                        "amount" => $bill->deduction,
                        "payment_method" => "Bill Adjustment",
                        "payment_date" => $bill->payment_date ,
                        "note" => "Invoice cleared against BiLL ID " . $bill->id,
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
                else {
                    Invoice::where([
                        "invoices.bill_id" => $updatableData["id"],
                        "invoices.created_by" => $request->user()->id
                    ])->delete();
                }




















              return response($invoice, 200);
          });
      } catch (Exception $e) {
          error_log($e->getMessage());
          return $this->sendError($e, 500,$request);
      }
  }

  public function billQueryTest(Request $request) {
    // $automobilesQuery = AutomobileMake::with("makes");

    $billQuery = Bill::with("bill_bill_items","bill_sale_items","bill_repair_items","landlord","property")
    ->leftJoin('invoices', 'invoices.bill_id', '=', 'bills.id')

  ;

  if (!empty($request->landlord_id)) {
   $billQuery =   $billQuery->where("bills.landlord_id", $request->landlord_id);
}


if (!empty($request->start_date)) {
   $billQuery = $billQuery->whereDate('bills.create_date', ">=", $request->start_date);
}

if (!empty($request->end_date)) {
   $billQuery = $billQuery->whereDate('bills.create_date', "<=", $request->end_date);
}


if (!empty($request->min_amount)) {
   $billQuery = $billQuery->where('bills.payabble_amount', ">=", $request->min_amount);
}

if (!empty($request->max_amount)) {
   $billQuery = $billQuery->where('bills.payabble_amount', "<=", $request->max_amount);
}

if(!empty($request->search_key)) {
   $billQuery = $billQuery->where(function($query) use ($request){
       $term = $request->search_key;
       $query->whereHas('bill_bill_items', function ($query) use ($request) {
           $query->where('item', 'like', '%' . $request->search_key . '%');
       });
       $query->orWhereHas('bill_sale_items', function ($query) use ($request) {
           $query->where('item', 'like', '%' . $request->search_key . '%');
       });
       $query->orWhereHas('bill_repair_items', function ($query) use ($request) {
           $query->where('item', 'like', '%' . $request->search_key . '%');
       });
   });

}





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




    $billQuery = $billQuery
    ->select(
       "bills.*",
       "invoices.id",
       "invoices.generated_id",
       "invoices.invoice_reference"
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
   public function billQuery(Request $request) {
     // $automobilesQuery = AutomobileMake::with("makes");

     $billQuery = Bill::with("bill_bill_items","bill_sale_items","bill_repair_items","landlord","property")
     ->leftJoin('invoices', 'invoices.bill_id', '=', 'bills.id')
     ->where([
          "bills.created_by" => $request->user()->id
     ])
   ;

   if (!empty($request->landlord_id)) {
    $billQuery =   $billQuery->where("bills.landlord_id", $request->landlord_id);
}


if (!empty($request->start_date)) {
    $billQuery = $billQuery->whereDate('bills.create_date', ">=", $request->start_date);
}

if (!empty($request->end_date)) {
    $billQuery = $billQuery->whereDate('bills.create_date', "<=", $request->end_date);
}


if (!empty($request->min_amount)) {
    $billQuery = $billQuery->where('bills.payabble_amount', ">=", $request->min_amount);
}

if (!empty($request->max_amount)) {
    $billQuery = $billQuery->where('bills.payabble_amount', "<=", $request->max_amount);
}

if(!empty($request->search_key)) {
    $billQuery = $billQuery->where(function($query) use ($request){
        $term = $request->search_key;
        $query->whereHas('bill_bill_items', function ($query) use ($request) {
            $query->where('item', 'like', '%' . $request->search_key . '%');
        });
        $query->orWhereHas('bill_sale_items', function ($query) use ($request) {
            $query->where('item', 'like', '%' . $request->search_key . '%');
        });
        $query->orWhereHas('bill_repair_items', function ($query) use ($request) {
            $query->where('item', 'like', '%' . $request->search_key . '%');
        });
    });

}





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




     $billQuery = $billQuery
     ->select(
        "bills.*",
        "invoices.id",
        "invoices.generated_id",
        "invoices.invoice_reference"
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
  * name="min_amount",
  * in="query",
  * description="min_amount",
  * required=true,
  * example="1"
  * ),
   * *  @OA\Parameter(
  * name="max_amount",
  * in="query",
  * description="max_amount",
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
   *      path="/v1.0/bills/get/all",
   *      operationId="getAllBillsPdfTest",
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
  * name="min_amount",
  * in="query",
  * description="min_amount",
  * required=true,
  * example="1"
  * ),
   * *  @OA\Parameter(
  * name="max_amount",
  * in="query",
  * description="max_amount",
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

   public function getAllBillsPdfTest( Request $request)
   {
       try {
           $this->storeActivity($request,"");
            $bills = $this->billQueryTest($request)->get();
            $pdf = PDF::loadView('pdf.bills', ["bills"=>$bills]);

            return $pdf->stream(); // Stream the PDF content



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


          $bill = Bill::with("bill_bill_items","bill_sale_items","bill_repair_items","landlord","property")
          ->where([
              "generated_id" => $id,
              "bills.created_by" => $request->user()->id
          ])
          ->select(
            "bills.*",
            "invoices.id",
            "invoices.generated_id",
            "invoices.invoice_reference"
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
