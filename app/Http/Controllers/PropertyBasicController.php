<?php

namespace App\Http\Controllers;

use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
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
use App\Models\Repair;
use App\Models\RepairCategory;
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




            if (!empty($request->landlord_id)) {
                $landlord_ids = explode(',', request()->input("landlord_ids"));

                $landlords = Landlord::where([
                    "created_by" => $request->user()->id
                ])
                ->whereIn("id",$landlord_ids)
                    ->get();

                if (empty($landlords)) {
                    return response()->json([
                        "message" => "no landlord found"
                    ], 404);
                }


                $opening_balance_data = Invoice::where([
                    "invoices.created_by" => $request->user()->id
                ])
                ->whereHas("landlords", function ($query) use($landlord_ids) {
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
                ->whereHas("landlords", function ($query) use($landlord_ids) {
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
                $tenant_ids = request()->filled("tenant_ids")?explode(',', request()->input("tenant_ids")):explode(',', request()->input("tenant_id"));

                $tenants = Tenant::where([
                    "created_by" => $request->user()->id
                ])
                ->whereIn("id",$tenant_ids)
                    ->get();

                if (empty($tenants)) {
                    return response()->json([
                        "message" => "no tenant found"
                    ], 404);
                }


                $opening_balance_data = Invoice::where([

                    "invoices.created_by" => $request->user()->id
                ])
                ->whereHas("tenants", function ($query) use($tenant_ids) {
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
                ->whereHas("tenants", function ($query) use($tenant_ids) {
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

        $repair_category_ids = Repair::where("created_by",auth()->user()->id)->distinct()->pluck('repair_category_id');

        $repair_categories = RepairCategory::whereIn("id",$repair_category_ids->toArray())->get();

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
        $statuses = Repair::where("created_by",auth()->user()->id)
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
                            ->whereDate('property_documents.gas_end_date', ">", Carbon::today())
                            ->whereDate('property_documents.gas_end_date', "<=", Carbon::today()->addDays(30));
                    })
                    ->count(),
                'expires_in_45_days' => (clone $base_documents_query)
                    ->whereHas("latest_documents", function ($subQuery) use ($document_type) {
                        $subQuery
                            ->where("property_documents.document_type_id", $document_type->id)
                            ->whereDate('property_documents.gas_end_date', ">", Carbon::today())
                            ->whereDate('property_documents.gas_end_date', "<=", Carbon::today()->addDays(45));
                    })
                    ->count(),
                'expires_in_60_days' => (clone $base_documents_query)
                    ->whereHas("latest_documents", function ($subQuery) use ($document_type) {
                        $subQuery
                            ->where("property_documents.document_type_id", $document_type->id)
                            ->whereDate('property_documents.gas_end_date', ">", Carbon::today())
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
