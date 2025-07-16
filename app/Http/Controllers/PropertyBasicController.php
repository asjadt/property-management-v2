<?php

namespace App\Http\Controllers;

use App\Http\Utils\BasicUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Bill;
use App\Models\Client;
use App\Models\DocumentType;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Landlord;
use App\Models\MaintenanceItem;
use App\Models\MaintenanceItemType;
use App\Models\Property;
use App\Models\PropertyDocument;
use App\Models\Receipt;
use App\Models\Rent;
use App\Models\Repair;
use App\Models\RepairCategory;
use App\Models\TenancyAgreement;
use App\Models\Tenant;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PropertyBasicController extends Controller
{
    use ErrorUtil, UserActivityUtil, BasicUtil;
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

    public function propertyReport(Request $request)
    {
        try {
            $this->storeActivity($request, "");


            //  if(empty($request->start_date)){
            //      $firstDayOfYear = Carbon::now()->startOfYear();
            //      $request["end_date"] = $firstDayOfYear->format('Y-m-d');
            //  }

            if (!empty($request->end_date)) {
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

                    ->select(
                        'invoices.id',
                        'invoices.total_amount',
                        'invoices.invoice_date as created_at',
                        'invoices.invoice_reference',
                        DB::raw("'invoice' as type"),
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

                foreach ($activities as $key => $item) {
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
                        return  $query->whereHas('repair_category', function ($innerQuery) use ($request) {
                            $innerQuery->where('name', $request->repair_category);
                        });
                    })
                    ->get();


                return response()->json($data, 200);
            } else {
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
     * name="tenant_ids",
     * in="query",
     * description="tenant_ids",
     * required=true,
     * example="tenant_ids"
     * ),
     * *  @OA\Parameter(
     * name="landlord_ids",
     * in="query",
     * description="landlord_ids",
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
            if (!empty($request->end_date)) {
                //  $todayDate = Carbon::now();
                //  $request["end_date"] = $todayDate->format('Y-m-d');
                $request['next_day'] = date('Y-m-d', strtotime($request->end_date) + 86400);
            }




            if (!empty($request->landlord_ids) || !empty($request->landlord_id)) {
                $landlord_ids = request()->filled("landlord_ids") ? explode(',', request()->input("landlord_ids")) : explode(',', request()->input("landlord_id"));

                $landlords = Landlord::where([
                    "created_by" => $request->user()->id
                ])
                    ->whereIn("id", $landlord_ids)
                    ->get();

                if (empty($landlords)) {
                    return response()->json([
                        "message" => "no landlord found"
                    ], 404);
                }


                $opening_balance_data = Invoice::where([
                    "invoices.created_by" => $request->user()->id
                ])
                    ->whereHas("landlords", function ($query) use ($landlord_ids) {
                        return $query
                            ->whereIn("landlords.id", $landlord_ids);
                    })
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

                    "invoices.created_by" => $request->user()->id
                ])
                    ->whereHas("landlords", function ($query) use ($landlord_ids) {
                        return $query
                            ->whereIn("landlords.id", $landlord_ids);
                    })
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<",  $request['next_day']);
                    })
                    ->when(!empty($request->property_ids), function ($query) use ($request) {
                        $null_filter = collect(array_filter($request->property_ids))->values();
                        $property_ids =  $null_filter->all();
                        return $query->whereIn("invoices.property_id", $property_ids);
                    })
                    ->select(
                        'invoices.id',
                        'invoices.total_amount',
                        'invoices.invoice_date as created_at',
                        'invoices.invoice_reference',
                        DB::raw("'invoice' as type"),
                        'invoices.due_date as due_date',

                        DB::raw(
                            '(SELECT COALESCE(SUM(invoice_payments.amount), 0) FROM invoice_payments WHERE invoices.id = invoice_payments.invoice_id) AS total_paid'
                        )
                    );

                $activitiesQuery = $invoiceQuery
                    // ->unionAll($invoicePaymentQuery)
                    ->orderBy('created_at', 'asc');


                $activitiesPaginated = $activitiesQuery->paginate($perPage);

                foreach ($activitiesPaginated->items() as $key => $item) {
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
                $section_1["landlords"] = $landlords;
                return response()->json([
                    "section_1" => $section_1,
                    "section_2" => $activitiesPaginated,
                    // "opening_balance" => ($total_past_invoice_amount - $total_past_invoice_payment_amount)
                    "opening_balance" => $opening_balance
                ], 200);
            } else   if (!empty($request->tenant_ids) || !empty($request->tenant_id)) {
                $tenant_ids = request()->filled("tenant_ids") ? explode(',', request()->input("tenant_ids")) : explode(',', request()->input("tenant_id"));

                $tenants = Tenant::where([
                    "created_by" => $request->user()->id
                ])
                    ->whereIn("id", $tenant_ids)
                    ->get();

                if (empty($tenants)) {
                    return response()->json([
                        "message" => "no tenant found"
                    ], 404);
                }


                $opening_balance_data = Invoice::where([

                    "invoices.created_by" => $request->user()->id
                ])
                    ->whereHas("tenants", function ($query) use ($tenant_ids) {
                        $query
                            ->whereIn("tenants.id", $tenant_ids);
                    })
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
                    "invoices.created_by" => $request->user()->id
                ])
                    ->whereHas("tenants", function ($query) use ($tenant_ids) {
                        $query
                            ->whereIn("tenants.id", $tenant_ids);
                    })
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<",  $request['next_day']);
                    })
                    ->when(!empty($request->property_ids), function ($query) use ($request) {
                        $null_filter = collect(array_filter($request->property_ids))->values();
                        $property_ids =  $null_filter->all();
                        return $query->whereIn("invoices.property_id", $property_ids);
                    })
                    ->select(
                        'invoices.id',
                        'invoices.total_amount',
                        'invoices.invoice_date as created_at',
                        'invoices.invoice_reference',
                        DB::raw("'invoice' as type"),
                        'invoices.due_date as due_date',
                        DB::raw(
                            '(SELECT COALESCE(SUM(invoice_payments.amount), 0) FROM invoice_payments WHERE invoices.id = invoice_payments.invoice_id) AS total_paid'
                        )
                    );





                $activitiesQuery = $invoiceQuery
                    // ->unionAll($invoicePaymentQuery)
                    ->orderBy('created_at', 'asc');


                $activitiesPaginated = $activitiesQuery->paginate($perPage);


                foreach ($activitiesPaginated->items() as $key => $item) {
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
                $section_1["tenants"] = $tenants;
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
                        return $query->where('invoices.invoice_date', "<",  $request['next_day']);
                    })
                    ->when(!empty($request->property_ids), function ($query) use ($request) {
                        $null_filter = collect(array_filter($request->property_ids))->values();
                        $property_ids =  $null_filter->all();
                        return $query->whereIn("invoices.property_id", $property_ids);
                    })
                    ->select(
                        'invoices.id',
                        'invoices.total_amount',
                        'invoices.invoice_date as created_at',
                        'invoices.invoice_reference',
                        DB::raw("'invoice' as type"),
                        'invoices.due_date as due_date',
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

                foreach ($activitiesPaginated->items() as $key => $item) {
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
            } else {
                $error =  [
                    "message" => "The given data was invalid.",
                    "errors" => [
                        "property_id" => ["property must be selected if landlord or tenant is not selected."],
                        "tenant_ids" => ["tenant must be selected if landlord or property is not selected."],
                        "landlord_ids" => ["landlord must be selected if tenant or property is not selected."],
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
     *      path="/v2.0/activities/{perPage}",
     *      operationId="showActivityV2",
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
     *      name="property_ids",
     *      in="query",
     *      description="property_ids",
     *      required=true,
     *      example="1,2"
     * ),
     * *  @OA\Parameter(
     * name="tenant_ids",
     * in="query",
     * description="tenant_ids",
     * required=true,
     * example="tenant_ids"
     * ),
     * *  @OA\Parameter(
     * name="landlord_ids",
     * in="query",
     * description="landlord_ids",
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


    public function showActivityV2($perPage, Request $request)
    {
        try {
            $this->storeActivity($request, "");

            if (!empty($request->end_date)) {
                $request['next_day'] = date('Y-m-d', strtotime($request->end_date) + 86400);
            }

            // Determine entity type and corresponding IDs
            $entityType = '';
            $entityIds = [];

            if ($request->has('landlord_ids') || $request->has('landlord_id')) {
                $entityType = 'landlord';
                $entityIds = $request->filled('landlord_ids') ? explode(',', $request->landlord_ids) : explode(',', $request->landlord_id);
            } elseif ($request->has('tenant_ids') || $request->has('tenant_id')) {
                $entityType = 'tenant';
                $entityIds = $request->filled('tenant_ids') ? explode(',', $request->tenant_ids) : explode(',', $request->tenant_id);
            } elseif ($request->has('client_ids') || $request->has('client_id')) {
                $entityType = 'client';
                $entityIds = $request->filled('client_ids') ? explode(',', $request->client_ids) : explode(',', $request->client_id);
            }

            if (!$entityType) {
                return response()->json(['message' => 'Invalid entity type'], 400);
            }



            // Invoice query based on entity type
            $invoices = Invoice::where("invoices.created_by", $request->user()->id)

                ->when($entityType === 'landlord', function ($query) use ($entityIds) {
                    return $query->whereHas('landlords', function ($query) use ($entityIds) {
                        return $query->whereIn('landlords.id', $entityIds);
                    });
                })
                ->when($entityType === 'tenant', function ($query) use ($entityIds) {
                    return $query->whereHas('tenants', function ($query) use ($entityIds) {
                        return $query->whereIn('tenants.id', $entityIds);
                    });
                })
                ->when($entityType === 'client', function ($query) use ($entityIds) {
                    return $query->whereHas('clients', function ($query) use ($entityIds) {
                        return $query->whereIn('clients.id', $entityIds);
                    });
                })
                ->when(!empty($request->start_date), function ($query) use ($request) {
                    return $query->where('invoices.invoice_date', ">=", $request->start_date);
                })
                ->when(!empty($request->end_date), function ($query) use ($request) {
                    return $query->where('invoices.invoice_date', "<", $request['next_day']);
                })
                ->when(!empty($request->property_ids), function ($query) use ($request) {

                    return $query->whereIn("invoices.property_id", explode(',', request()->input("property_ids")));
                })
                ->select(
                    'invoices.id',
                    'invoices.total_amount',
                    'invoices.invoice_date',
                    'invoices.invoice_reference',
                    DB::raw("'invoice' as type"),
                    'invoices.due_date',
                    DB::raw('(SELECT COALESCE(SUM(invoice_payments.amount), 0) FROM invoice_payments WHERE invoices.id = invoice_payments.invoice_id) AS total_paid')
                )
                ->orderBy('created_at', 'asc')
                ->get();



            $rents = Rent::where('rents.created_by', $request->user()->id)

                ->when($entityType === 'landlord', function ($query) use ($entityIds) {
                    return $query->whereHas('tenancy_agreement.property.property_landlords', function ($query) use ($entityIds) {
                        return $query->whereIn('landlords.id', $entityIds);
                    });
                })
                ->when($entityType === 'tenant', function ($query) use ($entityIds) {
                    return $query->whereHas('tenancy_agreement.tenants', function ($query) use ($entityIds) {
                        return $query->whereIn('tenants.id', $entityIds);
                    });
                })

                ->when(!empty($request->start_date), fn($query) => $query->where('rents.payment_date', '>=', $request->start_date))
                ->when(!empty($request->end_date), fn($query) => $query->where('rents.payment_date', '<', $request['next_day']))
                ->select(
                    'rents.id',
                    'rents.rent_reference',
                    'rents.payment_date',
                    'rents.paid_amount',
                    'rents.arrear',
                    DB::raw("'rent' as type"),
                )
                ->get();



            $bills =  Bill::where('bills.created_by', $request->user()->id)
                ->when($entityType === 'landlord', function ($query) use ($entityIds) {
                    return $query->whereHas('landlords', function ($query) use ($entityIds) {
                        return $query->whereIn('landlords.id', $entityIds);
                    });
                })

                ->when(!empty($request->start_date), fn($query) => $query->where('bills.payment_date', '>=', $request->start_date))
                ->when(!empty($request->end_date), fn($query) => $query->where('bills.payment_date', '<', $request['next_day']))
                ->select(
                    'bills.id',
                    'bills.create_date',
                    'bills.payment_date',
                    'bills.payabble_amount',
                    'bills.deduction',
                    DB::raw("'bill' as type"),

                )
                ->get();

            $activities = $invoices->merge($rents)->merge($bills)->sortBy(fn($item) => $item->invoice_date ?? $item->payment_date ?? $item->create_date);



            $section_1["start_date"] = $request->start_date;
            $section_1["end_date"] = $request->end_date;


            return response()->json([
                "section_1" => $section_1,
                "section_2" => $activities,

            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }




    //  public function showActivityV2($perPage, Request $request)
    //  {
    //      try {
    //          if (!empty($request->end_date)) {
    //              $request['next_day'] = date('Y-m-d', strtotime($request->end_date) + 86400);
    //          }

    //          $entityTypes = [
    //              'landlord' => Landlord::class,
    //              'tenant' => Tenant::class,
    //              'client' => Client::class
    //          ];

    //          foreach ($entityTypes as $type => $model) {
    //              $idsKey = "{$type}_ids";
    //              $idKey = "{$type}_id";

    //              if (!empty($request->$idsKey) || !empty($request->$idKey)) {
    //                  $ids = request()->filled($idsKey) ? explode(',', request()->input($idsKey)) : explode(',', request()->input($idKey));

    //                  $entities = $model::where('created_by', $request->user()->id)->whereIn('id', $ids)->get();
    //                  if ($entities->isEmpty()) {
    //                      return response()->json(["message" => "no {$type} found"], 404);
    //                  }

    //                  // Perform the queries for all types (invoice, rent, bill) based on $type
    //                  $invoiceResults = Invoice::with('invoice_payments')
    //                      ->where('invoices.created_by', $request->user()->id)
    //                      ->whereHas("{$type}s", fn($query) => $query->whereIn("{$type}s.id", $ids))
    //                      ->when(!empty($request->start_date), fn($query) => $query->where('invoices.invoice_date', '>=', $request->start_date))
    //                      ->when(!empty($request->end_date), fn($query) => $query->where('invoices.invoice_date', '<', $request['next_day']))
    //                      ->select(
    //                          'invoices.id', 'invoices.total_amount', 'invoices.invoice_date as created_at',
    //                          'invoices.invoice_reference', DB::raw("'invoice' as type"),
    //                          'invoices.due_date as due_date',
    //                          DB::raw('(SELECT COALESCE(SUM(invoice_payments.amount), 0) FROM invoice_payments WHERE invoices.id = invoice_payments.invoice_id) AS total_paid')
    //                      )
    //                      ->get();

    //                  $rentResults = $type === 'tenant' ? Rent::where('rents.created_by', $request->user()->id)
    //                      ->whereHas("tenancy_agreement.agreement_tenants", fn($query) => $query->whereIn("agreement_tenants.tenant_id", $ids))
    //                      ->when(!empty($request->start_date), fn($query) => $query->where('rents.payment_date', '>=', $request->start_date))
    //                      ->when(!empty($request->end_date), fn($query) => $query->where('rents.payment_date', '<', $request['next_day']))
    //                      ->select(
    //                          'rents.id', 'rents.arrear as total_amount', 'rents.payment_date as created_at',
    //                          DB::raw("'rent' as type"), DB::raw('NULL as due_date'),
    //                          'rents.paid_amount as total_paid'
    //                      )
    //                      ->get() : collect();

    //                  $billResults = $type === 'landlord' ? Bill::where('bills.created_by', $request->user()->id)
    //                      ->whereHas("landlords", fn($query) => $query->whereIn("landlords.id", $ids))
    //                      ->when(!empty($request->start_date), fn($query) => $query->where('bills.create_date', '>=', $request->start_date))
    //                      ->when(!empty($request->end_date), fn($query) => $query->where('bills.create_date', '<', $request['next_day']))
    //                      ->select(
    //                          'bills.id', 'bills.payabble_amount', 'bills.create_date as created_at',
    //                          'bills.payment_mode as invoice_reference', DB::raw("'bill' as type"),
    //                          DB::raw('NULL as due_date'),
    //                          'bills.payabble_amount - bills.deduction as total_paid'
    //                      )
    //                      ->get() : collect();

    //                  // Merge results and sort by created_at
    //                  $allResults = $invoiceResults->merge($rentResults)->merge($billResults)->sortBy('created_at');

    //                  // Paginate merged results
    //                  $activitiesPaginated = new \Illuminate\Pagination\LengthAwarePaginator(
    //                      $allResults->forPage(request('page', 1), $perPage),
    //                      $allResults->count(),
    //                      $perPage
    //                  );

    //                  // Section data for the response
    //                  $section_1 = [
    //                      'invoice_payment_total_amount' => $allResults->where('type', 'invoice')->sum('total_paid'),
    //                      'rent_payment_total_amount' => $allResults->where('type', 'rent')->sum('total_paid'),
    //                      'bill_payment_total_amount' => $allResults->where('type', 'bill')->sum('total_paid'),
    //                      'invoice_total_amount' => $allResults->where('type', 'invoice')->sum('total_amount'),
    //                      'rent_total_amount' => $allResults->where('type', 'rent')->sum('total_amount'),
    //                      'bill_total_amount' => $allResults->where('type', 'bill')->sum('total_amount'),
    //                      'start_date' => $request->start_date,
    //                      'end_date' => $request->end_date,
    //                      "{$type}s" => $entities
    //                  ];

    //                  return response()->json([
    //                      'section_1' => $section_1,
    //                      'section_2' => $activitiesPaginated,
    //                  ], 200);
    //              }
    //          }
    //      } catch (Exception $e) {
    //          return response()->json(['error' => $e->getMessage()], 500);
    //      }
    //  }






    // public function rent_report()
    // {
    //     $user_id = auth()->id();
    //     $now = Carbon::now();
    //     $today = $now->copy()->startOfDay();

    //     // Date markers
    //     $start_of_month = $now->copy()->startOfMonth();
    //     $end_of_month = $now->copy()->endOfMonth();
    //     $next_month_start = $now->copy()->addMonth()->startOfMonth();

    //     // Get all rents created by this user
    //     $rents_query = Rent::where('created_by', $user_id);

    //     // Total rent collected
    //     $total_collected = (clone $rents_query)->sum('paid_amount');

    //     // Total rent collected this month
    //     $total_collected_this_month = (clone $rents_query)
    //         ->where('year', $now->year)
    //         ->where('month', $now->month)
    //         ->sum('paid_amount');

    //     // Total due this month (arrear + rent - paid)
    //     $total_due_this_month = (clone $rents_query)
    //         ->where('year', $now->year)
    //         ->where('month', $now->month)
    //         ->selectRaw('SUM(arrear + rent_amount - paid_amount) as total_due')
    //         ->value('total_due') ?? 0;

    //     // Get tenancy agreements and related data
    //     $agreements = TenancyAgreement::with([
    //         'property',
    //         'tenants',
    //         'rents' => function ($q) {
    //             $q->select('tenancy_agreement_id', 'month', 'year', 'paid_amount');
    //         }
    //     ])->whereHas('property', function ($q) use ($user_id) {
    //         $q->where('created_by', $user_id);
    //     })->get();

    //     $total_upcoming_rent_next_month = 0;

    //     $alerts_summary = [
    //         'due_in_15_days' => 0,
    //         'due_in_30_days' => 0,
    //         'due_in_45_days' => 0,
    //     ];

    //     foreach ($agreements as $agreement) {
    //         $rent_due_day = $agreement->rent_due_day;

    //         // Skip if rent_due_day or expired contract not set
    //         if (!$rent_due_day || !$agreement->tenant_contact_expired_date) {
    //             continue;
    //         }

    //         // Contract expiration check
    //         $expiredDate = Carbon::parse($agreement->tenant_contact_expired_date)->endOfMonth();
    //         if ($expiredDate->lt($today)) {
    //             continue;
    //         }

    //         $rent_amount = $agreement->agreed_rent;

    //         // Build a map of paid rents by month-year
    //         $rent_map = collect($agreement->rents)->mapToGroups(function ($item) {
    //             return [Carbon::createFromDate($item->year, $item->month, 1)->format('Y-m') => $item->paid_amount];
    //         });

    //         $currentMonthKey = $start_of_month->format('Y-m');
    //         $nextMonthKey = $next_month_start->format('Y-m');

    //         $dueDateCurrentMonth = Carbon::create($now->year, $now->month, $rent_due_day);
    //         $dueDateNextMonth = Carbon::create($now->copy()->addMonth()->year, $now->copy()->addMonth()->month, $rent_due_day);

    //         $isPaidCurrent = $rent_map->has($currentMonthKey);
    //         $isPaidNext = $rent_map->has($nextMonthKey);

    //         // Check if rent due for current month is passed and unpaid
    //         if ($dueDateCurrentMonth->lte($today) && !$isPaidCurrent && $dueDateCurrentMonth->lte($expiredDate)) {
    //             $total_due_this_month += $rent_amount;
    //         }

    //         // Check if next month's due is coming and unpaid
    //         if (!$isPaidNext && $dueDateNextMonth->lte($expiredDate)) {
    //             $total_upcoming_rent_next_month += $rent_amount;
    //         }

    //         // Alerts for 15, 30, 45 days from today
    //         $alert_ranges = [
    //             'due_in_15_days' => $today->copy()->addDays(15),
    //             'due_in_30_days' => $today->copy()->addDays(30),
    //             'due_in_45_days' => $today->copy()->addDays(45),
    //         ];

    //         foreach ($alert_ranges as $key => $alertDate) {
    //             $alertDueDate = Carbon::create($alertDate->year, $alertDate->month, $rent_due_day);
    //             $alertMonthKey = $alertDueDate->format('Y-m');

    //             if ($alertDueDate->lte($expiredDate)) {
    //                 $isPaidAlert = $rent_map->has($alertMonthKey);
    //                 if (!$isPaidAlert) {
    //                     $alerts_summary[$key] += $rent_amount;
    //                 }
    //             }
    //         }
    //     }

    //     return [
    //         'total_collected' => $total_collected,
    //         'total_collected_this_month' => $total_collected_this_month,
    //         'total_due_this_month' => $total_due_this_month,
    //         'total_upcoming_rent_next_month' => $total_upcoming_rent_next_month,
    //         'alerts_summary' => $alerts_summary,
    //     ];
    // }



    public function rent_report()
    {
        Log::info('------------------------------------------------------');
        $user_id = auth()->id();
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();



        // Rents query
        $rents_query = Rent::where('created_by', $user_id);

        // Total collected
        $total_collected = (clone $rents_query)->sum('paid_amount');

        // Collected this month
        $total_collected_this_month = (clone $rents_query)
            ->where('year', $now->year)
            ->where('month', $now->month)
            ->sum('paid_amount');



        // Agreements + rents
        $agreements = TenancyAgreement::with([
            'property',
            'tenants',
            'rents' => function ($q) {
                $q->select('tenancy_agreement_id', 'month', 'year', 'paid_amount');
            }
        ])->whereHas('property', function ($q) use ($user_id) {
            $q->where('created_by', $user_id)
                ->whereNull('deleted_at');
        })
            ->whereNotNull('rent_due_day')
            ->whereNotNull('tenant_contact_expired_date')
            ->whereDate('tenant_contact_expired_date', '>=', $today)
            ->get();

        // PROPERTY IDS
        $property_ids = $agreements->pluck('property_id')->unique()->toArray();

        // Total due this month
        $total_due_this_month = 0;

        //  TOTAL SUMMARY
        $alerts_summary = [
            'due_in_15_days' => 0,
            'due_in_30_days' => 0,
            'due_in_45_days' => 0
        ];

 foreach ($agreements as $agreement) {


    $total_due_this_month += $this->calculatePayments($agreement, today())['total_arrears'];

      $alerts_summary['due_in_15_days'] += $this->calculatePayments($agreement, today()->addDays(15))['total_arrears'];

      $alerts_summary['due_in_30_days'] += $this->calculatePayments($agreement, today()->addDays(30))['total_arrears'];

      $alerts_summary['due_in_45_days'] += $this->calculatePayments($agreement, today()->addDays(45))['total_arrears'];





}

        return [
            'total_collected' => $total_collected,
            'total_collected_this_month' => $total_collected_this_month,
            'total_due_this_month' => $total_due_this_month,
            'total_upcoming_rent_next_month'=> $alerts_summary['due_in_30_days'],
            'alerts_summary' => $alerts_summary,
            'property_ids' => $property_ids,
        ];
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


            $data["repair_report"] = $this->getRepairReport();

            $data["document_report"] = $this->getDocumentReport();
            $data["maintainance_report"] = $this->getMaintainanceReport();
            $data["overall_maintainance_report"] = $this->getOverallMaintainanceReport();

            $now = Carbon::now();



            // GET RENT REPORT
            $data["rent_report"] = $this->rent_report();

            return response()->json($data, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/inspection-reports",
     *      operationId="getInspectionReportData",
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

    public function getInspectionReportData(Request $request)
    {
        try {
            $this->storeActivity($request, "");

            $data["maintainance_report"] = $this->getMaintainanceReport();

            return response()->json($data, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }
    public function getRepairReport()
    {

        $repair_category_ids = Repair::where("created_by", auth()->user()->id)->distinct()->pluck('repair_category_id');

        $repair_categories = RepairCategory::whereIn("id", $repair_category_ids->toArray())->get();

        $repair_report = [];
        $repair_report['category_wise'] = [];
        $repair_report['status_wise'] = [];
        // Category-wise report
        foreach ($repair_categories as $repair_category) {
            $base_documents_query = Repair::where("repairs.created_by", auth()->user()->id)
                ->where("repairs.repair_category_id", $repair_category->id);

            // Expiry report only for status "pending"
            $pending_expiry_query = (clone $base_documents_query)
                ->where("repairs.status", "pending");

            $repair_report['category_wise'][$repair_category->name] = [
                'total_data' => (clone $base_documents_query)

                    ->count(),

                'total_expired' => (clone $pending_expiry_query)
                    ->whereDate('repairs.create_date', "<", Carbon::today())
                    ->count(),

                'today_expiry' => (clone $pending_expiry_query)
                    ->whereDate('repairs.create_date', Carbon::today())
                    ->count(),

                'expires_in_15_days' => (clone $pending_expiry_query)
                    ->whereDate('repairs.create_date', ">", Carbon::today())
                    ->whereDate('repairs.create_date', "<=", Carbon::today()->addDays(15))
                    ->count(),

                'expires_in_30_days' => (clone $pending_expiry_query)
                    ->whereDate('repairs.create_date', ">", Carbon::today())
                    ->whereDate('repairs.create_date', "<=", Carbon::today()->addDays(30))
                    ->count(),



                'next_month' => (clone $base_documents_query)

                    ->whereBetween('repairs.create_date', [
                        Carbon::now()->addMonth()->startOfMonth(),
                        Carbon::now()->addMonth()->endOfMonth()
                    ])
                    ->count(),

                'last_week' => (clone $base_documents_query)
                    ->whereBetween('repairs.create_date', [
                        Carbon::now()->subWeek()->startOfWeek(),
                        Carbon::now()->subWeek()->endOfWeek()
                    ])
                    ->count(),

                'this_week' => (clone $base_documents_query)
                    ->whereBetween('repairs.create_date', [
                        Carbon::now()->startOfWeek(),
                        Carbon::now()->endOfWeek()
                    ])
                    ->count(),

                'next_week' => (clone $base_documents_query)
                    ->whereBetween('repairs.create_date', [
                        Carbon::now()->addWeek()->startOfWeek(),
                        Carbon::now()->addWeek()->endOfWeek()
                    ])
                    ->count(),
            ];
        }


        // Get distinct statuses
        $statuses = Repair::where("created_by", auth()->user()->id)
            ->whereNotNull("status")->distinct()->pluck('status');

        foreach ($statuses as $status) {

            $base_status_query = Repair::where("repairs.created_by", auth()->user()->id)
                ->where("repairs.status", $status);

            // Expiry report only for "pending" status
            $pending_expiry_query = Repair::where("repairs.created_by", auth()->user()->id)
                ->where("repairs.status", "pending");

            $repair_report['status_wise'][$status] = [
                'total_data' => $base_status_query->count(),

                // Expiry data only if the status is "pending"
                'total_expired' => $status === "pending" ? (clone $pending_expiry_query)
                    ->whereDate('repairs.create_date', "<", Carbon::today())
                    ->count() : null,

                'today_expiry' => $status === "pending" ? (clone $pending_expiry_query)
                    ->whereDate('repairs.create_date', Carbon::today())
                    ->count() : null,

                'expires_in_15_days' => $status === "pending" ? (clone $pending_expiry_query)
                    ->whereDate('repairs.create_date', ">", Carbon::today())
                    ->whereDate('repairs.create_date', "<=", Carbon::today()->addDays(15))
                    ->count() : null,

                'this_month' => (clone $base_status_query)
                    ->whereBetween('repairs.create_date', [
                        Carbon::now()->startOfMonth(),
                        Carbon::now()->endOfMonth()
                    ])
                    ->count(),

                'next_month' => (clone $base_status_query)
                    ->whereBetween('repairs.create_date', [
                        Carbon::now()->addMonth()->startOfMonth(),
                        Carbon::now()->addMonth()->endOfMonth()
                    ])
                    ->count(),
            ];
        }



        return $repair_report;
    }

    public function getDocumentReport()
    {

        $document_types = DocumentType::where([
            "created_by" => auth()->user()->id
        ])->get();

        $document_report = [];
        foreach ($document_types as $document_type) {
            $base_documents_query = Property::where("properties.created_by", auth()->user()->id);

            // Count documents for different expiration periods
            $document_report[$document_type->name] = [
                'total_data' => (clone $base_documents_query)
                    ->whereHas("latest_documents", function ($subQuery) use ($document_type) {
                        $subQuery
                            ->where("property_documents.document_type_id", $document_type->id);
                    })
                    ->count(),
                'total_expired' => (clone $base_documents_query)
                    ->whereHas("latest_documents", function ($subQuery) use ($document_type) {
                        $subQuery
                            ->where("property_documents.document_type_id", $document_type->id)
                            ->whereDate('property_documents.gas_end_date', "<", Carbon::today());
                    })
                    ->count(),

                'today_expiry' => (clone $base_documents_query)

                    ->whereHas("latest_documents", function ($subQuery) use ($document_type) {
                        $subQuery
                            ->where("property_documents.document_type_id", $document_type->id)
                            ->whereDate('property_documents.gas_end_date', Carbon::today());
                    })

                    ->count(),

                'expires_in_15_days' => (clone $base_documents_query)
                    ->whereHas("latest_documents", function ($subQuery) use ($document_type) {
                        $subQuery
                            ->where("property_documents.document_type_id", $document_type->id)
                            ->whereDate('property_documents.gas_end_date', ">", Carbon::today())
                            ->whereDate('property_documents.gas_end_date', "<=", Carbon::today()->addDays(15));
                    })
                    ->count(),

                'expires_in_30_days' => (clone $base_documents_query)

                    ->whereHas("latest_documents", function ($subQuery) use ($document_type) {
                        $subQuery
                            ->where("property_documents.document_type_id", $document_type->id)
                            ->whereDate('property_documents.gas_end_date', ">", Carbon::today()->addDays(15))
                            ->whereDate('property_documents.gas_end_date', "<=", Carbon::today()->addDays(30));
                    })
                    ->count(),
                'expires_in_45_days' => (clone $base_documents_query)
                    ->whereHas("latest_documents", function ($subQuery) use ($document_type) {
                        $subQuery
                            ->where("property_documents.document_type_id", $document_type->id)
                            ->whereDate('property_documents.gas_end_date', ">", Carbon::today()->addDays(30))
                            ->whereDate('property_documents.gas_end_date', "<=", Carbon::today()->addDays(45));
                    })
                    ->count(),
                'expires_in_60_days' => (clone $base_documents_query)
                    ->whereHas("latest_documents", function ($subQuery) use ($document_type) {
                        $subQuery
                            ->where("property_documents.document_type_id", $document_type->id)
                            ->whereDate('property_documents.gas_end_date', ">", Carbon::today()->addDays(45))
                            ->whereDate('property_documents.gas_end_date', "<=", Carbon::today()->addDays(60));
                    })
                    ->count(),
            ];
        }

        return $document_report;
    }




    public function getOverallMaintainanceReport()
    {


        $base_maintance_query = Property::when(request()->filled("property_id"), function ($query) {
            $query->where("properties.id", request()->input("property_id"));
        })
            ->where("properties.created_by", auth()->user()->id);


        // Count documents for different expiration periods
        $maintainance_report = [

            'total_data' => (clone $base_maintance_query)->count(),
            'total_expired' => (clone $base_maintance_query)
                ->whereHas("latest_inspection", function ($subQuery) {
                    $subQuery->whereDate('tenant_inspections.next_inspection_date', '<', Carbon::today());
                })

                ->count(),
            'today_expiry' => (clone $base_maintance_query)
                ->whereHas("latest_inspection", function ($subQuery) {
                    $subQuery->whereDate('tenant_inspections.next_inspection_date', Carbon::today());
                })
                ->count(),

            'expires_in_15_days' => (clone $base_maintance_query)
                ->whereHas("latest_inspection", function ($subQuery) {
                    $subQuery->whereDate('tenant_inspections.next_inspection_date', ">", Carbon::today())
                        ->whereDate('tenant_inspections.next_inspection_date', "<=", Carbon::today()->addDays(15));
                })

                ->count(),

            'expires_in_30_days' => (clone $base_maintance_query)
                ->whereHas("latest_inspection", function ($subQuery) {
                    $subQuery->whereDate('tenant_inspections.next_inspection_date', ">", Carbon::today())
                        ->whereDate('tenant_inspections.next_inspection_date', "<=", Carbon::today()->addDays(30));
                })

                ->count(),

            'expires_in_45_days' => (clone $base_maintance_query)
                ->whereHas("latest_inspection", function ($subQuery) {
                    $subQuery->whereDate('tenant_inspections.next_inspection_date', ">", Carbon::today())
                        ->whereDate('tenant_inspections.next_inspection_date', "<=", Carbon::today()->addDays(45));
                })

                ->count(),

            'expires_in_60_days' => (clone $base_maintance_query)
                ->whereHas("latest_inspection", function ($subQuery) {
                    $subQuery->whereDate('tenant_inspections.next_inspection_date', ">", Carbon::today())
                        ->whereDate('tenant_inspections.next_inspection_date', "<=", Carbon::today()->addDays(60));
                })

                ->count()

        ];

        return $maintainance_report;
    }



    public function getMaintainanceReport()
    {

        $maintainance_item_types = MaintenanceItemType::get();

        $maintainance_report = [];

        foreach ($maintainance_item_types as $maintainance_item_type) {

            $base_maintance_query = Property::when(request()->filled("property_id"), function ($query) {
                $query->where("properties.id", request()->input("property_id"));
            })
                ->where("properties.created_by", auth()->user()->id);

            // Count documents for different expiration periods
            $maintainance_report[$maintainance_item_type->id] = [


                "maintainance_item_type" => $maintainance_item_type->name,
                'total_data' => (clone $base_maintance_query)
                    ->whereHas("latest_inspection.maintenance_item", function ($subQuery) use ($maintainance_item_type) {
                        $subQuery
                            ->where("maintenance_items.maintenance_item_type_id", $maintainance_item_type->id)
                            ->where("maintenance_items.status", "work_required")
                        ;
                    })

                    ->count(),


                'total_expired' => (clone $base_maintance_query)
                    ->whereHas("latest_inspection.maintenance_item", function ($subQuery) use ($maintainance_item_type) {
                        $subQuery
                            ->where("maintenance_items.maintenance_item_type_id", $maintainance_item_type->id)
                            ->where("maintenance_items.status", "work_required")
                            ->whereDate('maintenance_items.next_follow_up_date', "<", today());
                    })

                    ->count(),
                'today_expiry' => (clone $base_maintance_query)
                    ->whereHas("latest_inspection.maintenance_item", function ($subQuery) use ($maintainance_item_type) {
                        $subQuery
                            ->where("maintenance_items.maintenance_item_type_id", $maintainance_item_type->id)
                            ->where("maintenance_items.status", "work_required")
                            ->whereDate('maintenance_items.next_follow_up_date', today());
                    })
                    ->count(),

                'expires_in_15_days' => (clone $base_maintance_query)
                    ->whereHas("latest_inspection.maintenance_item", function ($subQuery) use ($maintainance_item_type) {
                        $subQuery
                            ->where("maintenance_items.maintenance_item_type_id", $maintainance_item_type->id)
                            ->where("maintenance_items.status", "work_required")
                            ->whereDate('maintenance_items.next_follow_up_date', ">", Carbon::today())
                            ->whereDate('maintenance_items.next_follow_up_date', "<=", Carbon::today()->addDays(15));
                    })


                    ->count(),
                'expires_in_30_days' => (clone $base_maintance_query)
                    ->whereHas("latest_inspection.maintenance_item", function ($subQuery) use ($maintainance_item_type) {
                        $subQuery
                            ->where("maintenance_items.maintenance_item_type_id", $maintainance_item_type->id)
                            ->where("maintenance_items.status", "work_required")
                            ->whereDate('maintenance_items.next_follow_up_date', ">", Carbon::today())
                            ->whereDate('maintenance_items.next_follow_up_date', "<=", Carbon::today()->addDays(30));
                    })
                    ->count(),

                'expires_in_45_days' => (clone $base_maintance_query)
                    ->whereHas("latest_inspection.maintenance_item", function ($subQuery) use ($maintainance_item_type) {
                        $subQuery
                            ->where("maintenance_items.maintenance_item_type_id", $maintainance_item_type->id)
                            ->where("maintenance_items.status", "work_required")
                            ->whereDate('maintenance_items.next_follow_up_date', ">", Carbon::today())
                            ->whereDate('maintenance_items.next_follow_up_date', "<=", Carbon::today()->addDays(45));
                    })
                    ->count(),

                'expires_in_60_days' => (clone $base_maintance_query)
                    ->whereHas("latest_inspection.maintenance_item", function ($subQuery) use ($maintainance_item_type) {
                        $subQuery
                            ->where("maintenance_items.maintenance_item_type_id", $maintainance_item_type->id)
                            ->where("maintenance_items.status", "work_required")
                            ->whereDate('maintenance_items.next_follow_up_date', ">", Carbon::today())
                            ->whereDate('maintenance_items.next_follow_up_date', "<=", Carbon::today()->addDays(60));
                    })
                    ->count()

            ];
        }


        return $maintainance_report;
    }
}
