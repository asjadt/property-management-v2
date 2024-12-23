<?php

namespace App\Http\Controllers;

use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Client;
use App\Models\DocumentType;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Landlord;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\Receipt;
use App\Models\Repair;
use App\Models\Tenant;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyBasicController extends Controller
{
    use ErrorUtil, UserActivityUtil;
 /**
     *
     * @OA\Get(
     *      path="/v1.0/property-report",
     *      operationId="propertyReport",
     *      tags={"property_management.basics"},
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
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=true,
     * example="search_key"
     * ),
*  @OA\Parameter(
*      name="property_id",
*      in="query",
*      description="property_id",
*      required=true,
*      example="1"
* ),
     * *  @OA\Parameter(
     * name="repair_category",
     * in="query",
     * description="repair_category",
     * required=true,
     * example="repair_category"
     * ),

     *      summary="This method is to get property report ",
     *      description="This method is to get property report",
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

     public function propertyReport( Request $request)
     {
         try {
             $this->storeActivity($request, "");


            //  if(empty($request->start_date)){
            //      $firstDayOfYear = Carbon::now()->startOfYear();
            //      $request["end_date"] = $firstDayOfYear->format('Y-m-d');
            //  }

             if(!empty($request->end_date)){
                //  $todayDate = Carbon::now();
                //  $request["end_date"] = $todayDate->format('Y-m-d');
                 $request['next_day'] = date('Y-m-d', strtotime($request->end_date) + 86400);
             }




             if (!empty($request->property_id)) {
                $property = Property::where([
                    "id" => $request->property_id,
                    "created_by" => $request->user()->id
                ])
                    ->first();
                if (!$property) {
                    return response()->json([
                        "message" => "no property found"
                    ], 404);
                }

                // opening balance calculate start
                // $total_past_invoice_amount = Invoice::where([
                //     "invoices.property_id" => $property->id,
                //     "invoices.created_by" => $request->user()->id
                // ])
                //     ->when(!empty($request->start_date), function ($query) use ($request) {
                //         return $query->where('invoices.invoice_date', "<", $request->start_date);
                //     })
                //     ->sum("invoices.total_amount");
                // $total_past_invoice_payment_amount = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                //     ->where([
                //         "invoices.property_id" => $property->id,
                //         "invoices.created_by" => $request->user()->id
                //     ])
                //     ->when(!empty($request->start_date), function ($query) use ($request) {
                //         return $query->where('invoice_payments.payment_date', "<", $request->start_date);
                //     })

                //     ->sum("invoice_payments.amount");
                $opening_balance_data = Invoice::where([
                    "invoices.property_id" => $property->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<", $request->start_date);
                    })
                    ->select(
                        DB::raw('
                        COALESCE(
                            invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                            invoices.total_amount
                        ) AS total_due
                    ')
                    )
                    ->get();

                    $opening_balance =  $opening_balance_data->sum("total_due");

                // opening balance end


                $invoiceQuery = Invoice::where([
                    "invoices.property_id" => $property->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<", $request["next_day"]);
                    })

                    ->select('invoices.id', 'invoices.total_amount', 'invoices.invoice_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice' as type"),
                    'invoices.due_date as due_date',
                    DB::raw(
                        '(SELECT COALESCE(SUM(invoice_payments.amount), 0) FROM invoice_payments WHERE invoices.id = invoice_payments.invoice_id) AS total_paid'
                    )
                );

                // $invoicePaymentQuery = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                //     ->where([
                //         "invoices.property_id" => $property->id,
                //         "invoices.created_by" => $request->user()->id
                //     ])
                //     ->when(!empty($request->start_date), function ($query) use ($request) {
                //         return $query->where('invoice_payments.payment_date', ">=", $request->start_date);
                //     })
                //     ->when(!empty($request->end_date), function ($query) use ($request) {
                //         return $query->where('invoice_payments.payment_date', "<", $request["next_day"]);
                //     })

                //     ->select('invoice_payments.invoice_id', 'invoice_payments.amount as total_amount', 'invoice_payments.payment_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice_payment' as type"), 'invoices.due_date as due_date');



                $activitiesQuery = $invoiceQuery
                    // ->unionAll($invoicePaymentQuery)
                    ->orderBy('created_at', 'asc');


                $activities = $activitiesQuery->get();

                foreach($activities as $key=>$item){
                    $activities[$key]->invoice_payments =  $activities[$key]->invoice_payments;
                }


                $section_1["invoice_payment_total_amount"] =   collect($activities)->filter(function ($item) {
                    return $item->type == 'invoice';
                })->sum("total_paid");

                $section_1["invoice_total_amount"] =   collect($activities)->filter(function ($item) {
                    return $item->type == 'invoice';
                })->sum("total_amount");

                $data["start_date"] = $request->start_date;
                $data["end_date"] = $request->end_date;
                $data["property"] = $property;

                $data["table_1"] = [
                    "section_1" => $section_1,
                    "section_2" => $activities,
                    // "opening_balance" => ($total_past_invoice_amount - $total_past_invoice_payment_amount)
                    "opening_balance" => $opening_balance
                ];
                $data["table_2"] = Receipt::where([
                    "receipts.property_address" => $property->address,
                    "receipts.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('receipts.created_at', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('receipts.created_at', "<", $request["next_day"]);
                    })
                    ->get();

                    $data["table_3"] = Repair::with("repair_category")->where([
                        "repairs.property_id" => $property->id,
                        "repairs.created_by" => $request->user()->id
                    ])
                        ->when(!empty($request->start_date), function ($query) use ($request) {
                            return $query->where('repairs.created_at', ">=", $request->start_date);
                        })
                        ->when(!empty($request->end_date), function ($query) use ($request) {
                            return $query->where('repairs.created_at', "<", $request["next_day"]);
                        })
                        ->when(!empty($request->repair_category), function ($query) use ($request) {
                          return  $query->whereHas('repair_category', function ($innerQuery)use($request) {
                                $innerQuery->where('name', $request->repair_category);
                            });

                        })
                        ->get();



                return response()->json($data, 200);
            }   else {
                 $error =  [
                     "message" => "The given data was invalid.",
                     "errors" => [
                         "property_id" => ["property must be selected."],


                     ]
                 ];
                 throw new Exception(json_encode($error), 422);
             }
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }
    /**
     *
     * @OA\Get(
     *      path="/v1.0/activities/{perPage}",
     *      operationId="showActivity",
     *      tags={"property_management.basics"},
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
*  @OA\Parameter(
*      name="property_ids[]",
*      in="query",
*      description="property_ids",
*      required=true,
*      example="1,2"
* ),
     * *  @OA\Parameter(
     * name="tenant_id",
     * in="query",
     * description="tenant_id",
     * required=true,
     * example="tenant_id"
     * ),
     * *  @OA\Parameter(
     * name="landlord_id",
     * in="query",
     * description="landlord_id",
     * required=true,
     * example="1"
     * ),
     *    * *  @OA\Parameter(
     * name="client_id",
     * in="query",
     * description="client_id",
     * required=true,
     * example="1"
     * ),
     *      summary="This method is to get activities ",
     *      description="This method is to get activities",
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

    public function showActivity($perPage, Request $request)
    {
        try {
            $this->storeActivity($request, "");


            // if(empty($request->start_date)){
            //     $firstDayOfYear = Carbon::now()->startOfYear();
            //     $request["end_date"] = $firstDayOfYear->format('Y-m-d');
            // }
            if(!empty($request->end_date)){
                //  $todayDate = Carbon::now();
                //  $request["end_date"] = $todayDate->format('Y-m-d');
                 $request['next_day'] = date('Y-m-d', strtotime($request->end_date) + 86400);
             }




            if (!empty($request->landlord_id)) {
                $landlord = Landlord::where([
                    "id" => $request->landlord_id,
                    "created_by" => $request->user()->id
                ])
                    ->first();
                if (!$landlord) {
                    return response()->json([
                        "message" => "no landlord found"
                    ], 404);
                }

                // opening balance calculate start
                // $total_past_invoice_amount = Invoice::where([
                //     "invoices.landlord_id" => $landlord->id,
                //     "invoices.created_by" => $request->user()->id
                // ])
                //     ->when(!empty($request->start_date), function ($query) use ($request) {
                //         return $query->where('invoices.invoice_date', "<", $request->start_date);
                //     })
                //     ->sum("invoices.total_amount");
                // $total_past_invoice_payment_amount = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                //     ->where([
                //         "invoices.landlord_id" => $landlord->id,
                //         "invoices.created_by" => $request->user()->id
                //     ])
                //     ->when(!empty($request->start_date), function ($query) use ($request) {
                //         return $query->where('invoice_payments.payment_date', "<", $request->start_date);
                //     })
                //     ->when(!empty($request->property_ids), function ($query) use ($request) {
                //         $null_filter = collect(array_filter($request->property_ids))->values();
                //         $property_ids =  $null_filter->all();
                //         return $query->whereIn("invoices.property_id",$property_ids);
                //     })
                //     ->sum("invoice_payments.amount");
                $opening_balance_data = Invoice::where([
                    "invoices.landlord_id" => $landlord->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<", $request->start_date);
                    })
                    ->select(
                        DB::raw('
                        COALESCE(
                            invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                            invoices.total_amount
                        ) AS total_due
                    ')
                    )
                    ->get();

                    $opening_balance =  $opening_balance_data->sum("total_due");

                // opening balance end


                $invoiceQuery = Invoice::with("invoice_payments")->where([
                    "invoices.landlord_id" => $landlord->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<",  $request['next_day'] );
                    })
                    ->when(!empty($request->property_ids), function ($query) use ($request) {
                        $null_filter = collect(array_filter($request->property_ids))->values();
                        $property_ids =  $null_filter->all();
                        return $query->whereIn("invoices.property_id",$property_ids);
                    })
                     ->select('invoices.id', 'invoices.total_amount', 'invoices.invoice_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice' as type"), 'invoices.due_date as due_date',

                     DB::raw(
                        '(SELECT COALESCE(SUM(invoice_payments.amount), 0) FROM invoice_payments WHERE invoices.id = invoice_payments.invoice_id) AS total_paid'
                    )
                    );

                // $invoicePaymentQuery = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                //     ->where([
                //         "invoices.landlord_id" => $landlord->id,
                //         "invoices.created_by" => $request->user()->id
                //     ])
                //     ->when(!empty($request->start_date), function ($query) use ($request) {
                //         return $query->where('invoice_payments.payment_date', ">=", $request->start_date);
                //     })
                //     ->when(!empty($request->end_date), function ($query) use ($request) {
                //         return $query->where('invoice_payments.payment_date', "<",  $request['next_day'] );
                //     })
                //     ->when(!empty($request->property_ids), function ($query) use ($request) {
                //         $null_filter = collect(array_filter($request->property_ids))->values();
                //         $property_ids =  $null_filter->all();
                //         return $query->whereIn("invoices.property_id",$property_ids);
                //     })
                //     ->select('invoice_payments.invoice_id', 'invoice_payments.amount as total_amount', 'invoice_payments.payment_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice_payment' as type"), 'invoices.due_date as due_date');



                $activitiesQuery = $invoiceQuery
                    // ->unionAll($invoicePaymentQuery)
                    ->orderBy('created_at', 'asc');


                $activitiesPaginated = $activitiesQuery->paginate($perPage);

                foreach($activitiesPaginated->items() as $key=>$item){
                    $activitiesPaginated->items()[$key]->invoice_payments = $activitiesPaginated->items()[$key]->invoice_payments;
                }


                $section_1["invoice_payment_total_amount"] =   collect($activitiesPaginated->items())->filter(function ($item) {
                    return $item->type == 'invoice';
                })->sum("total_paid");

                $section_1["invoice_total_amount"] =   collect($activitiesPaginated->items())->filter(function ($item) {
                    return $item->type == 'invoice';
                })->sum("total_amount");

                $section_1["start_date"] = $request->start_date;
                $section_1["end_date"] = $request->end_date;
                $section_1["landlord"] = $landlord;
                return response()->json([
                    "section_1" => $section_1,
                    "section_2" => $activitiesPaginated,
                    // "opening_balance" => ($total_past_invoice_amount - $total_past_invoice_payment_amount)
                    "opening_balance" => $opening_balance
                ], 200);
            } else   if (!empty($request->tenant_id)) {
                $tenant = Tenant::where([
                    "id" => $request->tenant_id,
                    "created_by" => $request->user()->id
                ])
                    ->first();
                if (!$tenant) {
                    return response()->json([
                        "message" => "no tenant found"
                    ], 404);
                }
                // opening balance calculate start
                // $total_past_invoice_amount = Invoice::where([
                //     "invoices.tenant_id" => $tenant->id,
                //     "invoices.created_by" => $request->user()->id
                // ])
                //     ->when(!empty($request->start_date), function ($query) use ($request) {
                //         return $query->where('invoices.invoice_date', "<", $request->start_date);
                //     })
                //     ->when(!empty($request->property_ids), function ($query) use ($request) {
                //         $null_filter = collect(array_filter($request->property_ids))->values();
                //         $property_ids =  $null_filter->all();
                //         return $query->whereIn("invoices.property_id",$property_ids);
                //     })
                //     ->sum("invoices.total_amount");
                // $total_past_invoice_payment_amount = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                //     ->where([
                //         "invoices.tenant_id" => $tenant->id,
                //         "invoices.created_by" => $request->user()->id
                //     ])
                //     ->when(!empty($request->start_date), function ($query) use ($request) {
                //         return $query->where('invoice_payments.payment_date', "<", $request->start_date);
                //     })
                //     ->when(!empty($request->property_ids), function ($query) use ($request) {
                //         $null_filter = collect(array_filter($request->property_ids))->values();
                //         $property_ids =  $null_filter->all();
                //         return $query->whereIn("invoices.property_id",$property_ids);
                //     })
                //     ->sum("invoice_payments.amount");
                $opening_balance_data = Invoice::where([
                    "invoices.tenant_id" => $tenant->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<", $request->start_date);
                    })
                    ->select(
                        DB::raw('
                        COALESCE(
                            invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                            invoices.total_amount
                        ) AS total_due
                    ')
                    )
                    ->get();

                    $opening_balance =  $opening_balance_data->sum("total_due");

                // opening balance end

                $invoiceQuery = Invoice::where([
                    "invoices.tenant_id" => $tenant->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<",  $request['next_day'] );
                    })
                    ->when(!empty($request->property_ids), function ($query) use ($request) {
                        $null_filter = collect(array_filter($request->property_ids))->values();
                        $property_ids =  $null_filter->all();
                        return $query->whereIn("invoices.property_id",$property_ids);
                    })
                     ->select('invoices.id', 'invoices.total_amount', 'invoices.invoice_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice' as type"), 'invoices.due_date as due_date',
                     DB::raw(
                        '(SELECT COALESCE(SUM(invoice_payments.amount), 0) FROM invoice_payments WHERE invoices.id = invoice_payments.invoice_id) AS total_paid'
                    )
                    );

                // $invoicePaymentQuery = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                //     ->where([
                //         "invoices.tenant_id" => $tenant->id,
                //         "invoices.created_by" => $request->user()->id
                //     ])
                //     ->when(!empty($request->start_date), function ($query) use ($request) {
                //         return $query->where('invoice_payments.payment_date', ">=", $request->start_date);
                //     })
                //     ->when(!empty($request->end_date), function ($query) use ($request) {
                //         return $query->where('invoice_payments.payment_date', "<",  $request['next_day'] );
                //     })
                //     ->when(!empty($request->property_ids), function ($query) use ($request) {
                //         $null_filter = collect(array_filter($request->property_ids))->values();
                //         $property_ids =  $null_filter->all();
                //         return $query->whereIn("invoices.property_id",$property_ids);
                //     })
                //     ->select('invoice_payments.invoice_id', 'invoice_payments.amount as total_amount', 'invoice_payments.payment_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice_payment' as type"), 'invoices.due_date as due_date');



                $activitiesQuery = $invoiceQuery
                    // ->unionAll($invoicePaymentQuery)
                    ->orderBy('created_at', 'asc');


                $activitiesPaginated = $activitiesQuery->paginate($perPage);


                foreach($activitiesPaginated->items() as $key=>$item){
                    $activitiesPaginated->items()[$key]->invoice_payments = $activitiesPaginated->items()[$key]->invoice_payments;
                }

                $section_1["invoice_payment_total_amount"] =   collect($activitiesPaginated->items())->filter(function ($item) {
                    return $item->type == 'invoice';
                })->sum("total_paid");

                $section_1["invoice_total_amount"] =   collect($activitiesPaginated->items())->filter(function ($item) {
                    return $item->type == 'invoice';
                })->sum("total_amount");
                $section_1["start_date"] = $request->start_date;
                $section_1["end_date"] = $request->end_date;
                $section_1["tenant"] = $tenant;
                return response()->json([
                    "section_1" => $section_1,
                    "section_2" => $activitiesPaginated,
                    // "opening_balance" => ($total_past_invoice_amount - $total_past_invoice_payment_amount)
                    "opening_balance" => $opening_balance
                ], 200);
            } else if ($request->client_id) {
                $client = Client::where([
                    "id" => $request->client_id,
                    "created_by" => $request->user()->id
                ])
                    ->first();
                if (!$client) {
                    return response()->json([
                        "message" => "no client found"
                    ], 404);
                }

                // opening balance calculate start
                // $total_past_invoice_amount = Invoice::where([
                //     "invoices.client_id" => $client->id,
                //     "invoices.created_by" => $request->user()->id
                // ])
                //     ->when(!empty($request->start_date), function ($query) use ($request) {
                //         return $query->where('invoices.invoice_date', "<", $request->start_date);
                //     })
                //     ->sum("invoices.total_amount");
                // $total_past_invoice_payment_amount = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                //     ->where([
                //         "invoices.client_id" => $client->id,
                //         "invoices.created_by" => $request->user()->id
                //     ])
                //     ->when(!empty($request->start_date), function ($query) use ($request) {
                //         return $query->where('invoice_payments.payment_date', "<", $request->start_date);
                //     })
                //     ->when(!empty($request->property_ids), function ($query) use ($request) {
                //         $null_filter = collect(array_filter($request->property_ids))->values();
                //         $property_ids =  $null_filter->all();
                //         return $query->whereIn("invoices.property_id",$property_ids);
                //     })
                //     ->sum("invoice_payments.amount");
                $opening_balance_data = Invoice::where([
                    "invoices.client_id" => $client->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<", $request->start_date);
                    })
                    ->select(
                        DB::raw('
                        COALESCE(
                            invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                            invoices.total_amount
                        ) AS total_due
                    ')
                    )
                    ->get();

                    $opening_balance =  $opening_balance_data->sum("total_due");

                // opening balance end


                $invoiceQuery = Invoice::where([
                    "invoices.client_id" => $client->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<",  $request['next_day'] );
                    })
                    ->when(!empty($request->property_ids), function ($query) use ($request) {
                        $null_filter = collect(array_filter($request->property_ids))->values();
                        $property_ids =  $null_filter->all();
                        return $query->whereIn("invoices.property_id",$property_ids);
                    })
                     ->select('invoices.id', 'invoices.total_amount', 'invoices.invoice_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice' as type"), 'invoices.due_date as due_date',
                     DB::raw(
                        '(SELECT COALESCE(SUM(invoice_payments.amount), 0) FROM invoice_payments WHERE invoices.id = invoice_payments.invoice_id) AS total_paid'
                    )
                    );

                // $invoicePaymentQuery = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                //     ->where([
                //         "invoices.client_id" => $client->id,
                //         "invoices.created_by" => $request->user()->id
                //     ])
                //     ->when(!empty($request->start_date), function ($query) use ($request) {
                //         return $query->where('invoice_payments.payment_date', ">=", $request->start_date);
                //     })
                //     ->when(!empty($request->end_date), function ($query) use ($request) {
                //         return $query->where('invoice_payments.payment_date', "<",  $request['next_day'] );
                //     })
                //     ->when(!empty($request->property_ids), function ($query) use ($request) {
                //         $null_filter = collect(array_filter($request->property_ids))->values();
                //         $property_ids =  $null_filter->all();
                //         return $query->whereIn("invoices.property_id",$property_ids);
                //     })
                //     ->select('invoice_payments.invoice_id', 'invoice_payments.amount as total_amount', 'invoice_payments.payment_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice_payment' as type"), 'invoices.due_date as due_date');



                $activitiesQuery = $invoiceQuery
                    // ->unionAll($invoicePaymentQuery)
                    ->orderBy('created_at', 'asc');


                $activitiesPaginated = $activitiesQuery->paginate($perPage);

foreach($activitiesPaginated->items() as $key=>$item){
    $activitiesPaginated->items()[$key]->invoice_payments = $activitiesPaginated->items()[$key]->invoice_payments;
}


                $section_1["invoice_payment_total_amount"] =   collect($activitiesPaginated->items())->filter(function ($item) {
                    return $item->type == 'invoice';
                })->sum("total_paid");

                $section_1["invoice_total_amount"] =   collect($activitiesPaginated->items())->filter(function ($item) {
                    return $item->type == 'invoice';
                })->sum("total_amount");

                $section_1["start_date"] = $request->start_date;
                $section_1["end_date"] = $request->end_date;
                $section_1["client"] = $client;
                return response()->json([
                    "section_1" => $section_1,
                    "section_2" => $activitiesPaginated,
                    // "opening_balance" => ($total_past_invoice_amount - $total_past_invoice_payment_amount)
                    "opening_balance" => $opening_balance
                ], 200);
            }

            else {
                $error =  [
                    "message" => "The given data was invalid.",
                    "errors" => [
                        "property_id" => ["property must be selected if landlord or tenant is not selected."],
                        "tenant_id" => ["tenant must be selected if landlord or property is not selected."],
                        "landlord_id" => ["landlord must be selected if tenant or property is not selected."],
                        "client_id" => ["client must be selected if business is other"]

                    ]
                ];
                throw new Exception(json_encode($error), 422);
            }
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Get(
     *      path="/v1.0/dashboard",
     *      operationId="getDashboardData",
     *      tags={"property_management.basics"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *      summary="get all dashboard data combined",
     *      description="get all dashboard data combined",
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

    public function getDashboardData(Request $request)
    {
        try {
            $this->storeActivity($request, "");


            $data["total_paid_amount"] = (int) Invoice::leftJoin('invoice_payments', 'invoice_payments.invoice_id', '=', 'invoices.id')
                ->where([
                    "invoices.created_by" => $request->user()->id
                ])
                ->where("invoices.status", "!=", "draft")
                ->sum("invoice_payments.amount");

            $data["total_paid_invoice_count"] = (int) Invoice::where([
                "invoices.created_by" => $request->user()->id
            ])
                ->where("invoices.status", "!=", "draft")
                ->select(

                    DB::raw('
            COALESCE(
                invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                invoices.total_amount
            ) AS total_due
        ')
                )
                ->havingRaw('total_due = 0')
                ->count();


            $data["total_invoice_amount"] = (int) Invoice::where([
                "invoices.created_by" => $request->user()->id
            ])
                ->where("invoices.status", "!=", "draft")
                ->sum("invoices.total_amount");







            $data["total_due_invoice_count"] = (int) Invoice::where([
                "invoices.created_by" => $request->user()->id
            ])
                ->where("invoices.status", "!=", "draft")

                ->select(
                    DB::raw('
        COALESCE(
            invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
            invoices.total_amount
        ) AS total_due
    ')
                )
                ->havingRaw('total_due > 0')
                ->count();
            $data["total_due_invoice_amount"] = Invoice::where([
                "invoices.created_by" => $request->user()->id
            ])
                ->where("invoices.status", "!=", "draft")

                ->select(
                    DB::raw('
        COALESCE(
            invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
            invoices.total_amount
        ) AS total_due
    ')
                )
                ->havingRaw('total_due > 0')
                ->get();
            $data["total_due_invoice_amount"] =  (int) $data["total_due_invoice_amount"]->sum("total_due");
            $data["total_overdue_invoice_count"] = Invoice::where([
                "invoices.created_by" => $request->user()->id
            ])

                ->where('invoices.status',  'overdue')
                ->select(
                    DB::raw('
        COALESCE(
            invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
            invoices.total_amount
        ) AS total_due
    ')
                )
                ->havingRaw('total_due > 0')
                ->count();
            $data["total_overdue_invoice_amount"] = Invoice::where([
                "invoices.created_by" => $request->user()->id
            ])
            ->where('invoices.status',  'overdue')

                ->select(
                    DB::raw('
        COALESCE(
            invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
            invoices.total_amount
        ) AS total_due
    ')
                )
                ->havingRaw('total_due > 0')
                ->get();
            $data["total_overdue_invoice_amount"] =  (int) $data["total_overdue_invoice_amount"]->sum("total_due");
            $currentDate = Carbon::now();
            $endDate = $currentDate->copy()->addDays(15);

            $data["next_15_days_invoice_due_dates"] = (int) Invoice::where([
                "invoices.created_by" => $request->user()->id
            ])
                ->where("invoices.status", "!=", "draft")
                ->whereDate('invoices.due_date', '>=', $currentDate)
                ->whereDate('invoices.due_date', '<=', $endDate)
                ->select(
                    DB::raw('
    COALESCE(
        invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
        invoices.total_amount
    ) AS total_due
')
                )
                ->havingRaw('total_due > 0')
                ->count();




            $data["next_15_days_invoice_due_amounts"] = Invoice::where([
                "invoices.created_by" => $request->user()->id
            ])
                ->where("invoices.status", "!=", "draft")
                ->whereDate('invoices.due_date', '>=', $currentDate)
                ->whereDate('invoices.due_date', '<=', $endDate)
                ->select(
                    DB::raw('
COALESCE(
    invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
    invoices.total_amount
) AS total_due
')
                )

                ->havingRaw('total_due > 0')
                ->get();

            $data["next_15_days_invoice_due_amounts"] = (int)$data["next_15_days_invoice_due_amounts"]->sum('total_due');



            $document_types = DocumentType::where([
                "created_by" => auth()->user()->id
            ])->get();

            $document_report = [];
            foreach ($document_types as $document_type) {
                $documents = PropertyDocument::whereHas("property", function ($query) {
                    $query->where("properties.created_by", auth()->user()->id);
                })
                ->where("document_type_id", $document_type->id);

                // Count documents for different expiration periods
                $document_report[$document_type->name] = [
                    'today_expiry' => $documents->whereDate('gas_end_date', Carbon::today())->count(),
                    'expires_in_15_days' => $documents->whereBetween('gas_end_date', [Carbon::today(), Carbon::today()->addDays(15)])->count(),
                    'expires_in_30_days' => $documents->whereBetween('gas_end_date', [Carbon::today(), Carbon::today()->addDays(30)])->count(),
                    'expires_in_45_days' => $documents->whereBetween('gas_end_date', [Carbon::today(), Carbon::today()->addDays(45)])->count(),
                    'expires_in_60_days' => $documents->whereBetween('gas_end_date', [Carbon::today(), Carbon::today()->addDays(60)])->count(),
                ];
            }

            $data["document_report"] = $document_report;



            return response()->json($data, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }
}
