<?php

namespace App\Http\Controllers;

use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Landlord;
use App\Models\Property;
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
            //  if(empty($request->end_date)){
            //      $todayDate = Carbon::now();
            //      $request["end_date"] = $todayDate->format('Y-m-d');
            //  }




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
                $total_past_invoice_amount = Invoice::where([
                    "invoices.property_id" => $property->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<", $request->start_date);
                    })
                    ->sum("invoices.total_amount");
                $total_past_invoice_payment_amount = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->where([
                        "invoices.property_id" => $property->id,
                        "invoices.created_by" => $request->user()->id
                    ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoice_payments.payment_date', "<", $request->start_date);
                    })

                    ->sum("invoice_payments.amount");

                // opening balance end


                $invoiceQuery = Invoice::where([
                    "invoices.property_id" => $property->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<=", $request->end_date);
                    })

                    ->select('invoices.id', 'invoices.total_amount', 'invoices.invoice_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice' as type"), 'invoices.due_date as due_date');

                $invoicePaymentQuery = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->where([
                        "invoices.property_id" => $property->id,
                        "invoices.created_by" => $request->user()->id
                    ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoice_payments.payment_date', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('invoice_payments.payment_date', "<=", $request->end_date);
                    })

                    ->select('invoice_payments.invoice_id', 'invoice_payments.amount as total_amount', 'invoice_payments.payment_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice_payment' as type"), 'invoices.due_date as due_date');



                $activitiesQuery = $invoiceQuery
                    ->unionAll($invoicePaymentQuery)
                    ->orderBy('created_at', 'asc');


                $activities = $activitiesQuery->get();




                $section_1["invoice_payment_total_amount"] =   collect($activities)->filter(function ($item) {
                    return $item->type == 'invoice_payment';
                })->sum("total_amount");

                $section_1["invoice_total_amount"] =   collect($activities)->filter(function ($item) {
                    return $item->type == 'invoice';
                })->sum("total_amount");

                $section_1["start_date"] = $request->start_date;
                $section_1["end_date"] = $request->end_date;
                $section_1["property"] = $property;

                $data["table_1"] = [
                    "section_1" => $section_1,
                    "section_2" => $activities,
                    "opening_balance" => ($total_past_invoice_amount - $total_past_invoice_payment_amount)
                ];
                $data["table_2"] = Receipt::where([
                    "receipts.property_address" => $property->address,
                    "receipts.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('receipts.created_at', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('receipts.created_at', "<=", $request->end_date);
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
                            return $query->where('repairs.created_at', "<=", $request->end_date);
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
     * example="landlord_id"
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
            // if(empty($request->end_date)){
            //     $todayDate = Carbon::now();
            //     $request["end_date"] = $todayDate->format('Y-m-d');
            // }




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
                $total_past_invoice_amount = Invoice::where([
                    "invoices.landlord_id" => $landlord->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<", $request->start_date);
                    })
                    ->sum("invoices.total_amount");
                $total_past_invoice_payment_amount = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->where([
                        "invoices.landlord_id" => $landlord->id,
                        "invoices.created_by" => $request->user()->id
                    ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoice_payments.payment_date', "<", $request->start_date);
                    })
                    ->when(!empty($request->property_ids), function ($query) use ($request) {
                        $null_filter = collect(array_filter($request->property_ids))->values();
                        $property_ids =  $null_filter->all();
                        return $query->whereIn("invoices.property_id",$property_ids);
                    })
                    ->sum("invoice_payments.amount");

                // opening balance end


                $invoiceQuery = Invoice::where([
                    "invoices.landlord_id" => $landlord->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<=", $request->end_date);
                    })
                    ->when(!empty($request->property_ids), function ($query) use ($request) {
                        $null_filter = collect(array_filter($request->property_ids))->values();
                        $property_ids =  $null_filter->all();
                        return $query->whereIn("invoices.property_id",$property_ids);
                    })
                    ->select('invoices.id', 'invoices.total_amount', 'invoices.invoice_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice' as type"), 'invoices.due_date as due_date');

                $invoicePaymentQuery = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->where([
                        "invoices.landlord_id" => $landlord->id,
                        "invoices.created_by" => $request->user()->id
                    ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoice_payments.payment_date', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('invoice_payments.payment_date', "<=", $request->end_date);
                    })
                    ->when(!empty($request->property_ids), function ($query) use ($request) {
                        $null_filter = collect(array_filter($request->property_ids))->values();
                        $property_ids =  $null_filter->all();
                        return $query->whereIn("invoices.property_id",$property_ids);
                    })
                    ->select('invoice_payments.invoice_id', 'invoice_payments.amount as total_amount', 'invoice_payments.payment_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice_payment' as type"), 'invoices.due_date as due_date');



                $activitiesQuery = $invoiceQuery
                    ->unionAll($invoicePaymentQuery)
                    ->orderBy('created_at', 'asc');


                $activitiesPaginated = $activitiesQuery->paginate($perPage);




                $section_1["invoice_payment_total_amount"] =   collect($activitiesPaginated->items())->filter(function ($item) {
                    return $item->type == 'invoice_payment';
                })->sum("total_amount");

                $section_1["invoice_total_amount"] =   collect($activitiesPaginated->items())->filter(function ($item) {
                    return $item->type == 'invoice';
                })->sum("total_amount");

                $section_1["start_date"] = $request->start_date;
                $section_1["end_date"] = $request->end_date;
                $section_1["landlord"] = $landlord;
                return response()->json([
                    "section_1" => $section_1,
                    "section_2" => $activitiesPaginated,
                    "opening_balance" => ($total_past_invoice_amount - $total_past_invoice_payment_amount)
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
                $total_past_invoice_amount = Invoice::where([
                    "invoices.tenant_id" => $tenant->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<", $request->start_date);
                    })
                    ->when(!empty($request->property_ids), function ($query) use ($request) {
                        $null_filter = collect(array_filter($request->property_ids))->values();
                        $property_ids =  $null_filter->all();
                        return $query->whereIn("invoices.property_id",$property_ids);
                    })
                    ->sum("invoices.total_amount");
                $total_past_invoice_payment_amount = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->where([
                        "invoices.tenant_id" => $tenant->id,
                        "invoices.created_by" => $request->user()->id
                    ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoice_payments.payment_date', "<", $request->start_date);
                    })
                    ->when(!empty($request->property_ids), function ($query) use ($request) {
                        $null_filter = collect(array_filter($request->property_ids))->values();
                        $property_ids =  $null_filter->all();
                        return $query->whereIn("invoices.property_id",$property_ids);
                    })
                    ->sum("invoice_payments.amount");

                // opening balance end

                $invoiceQuery = Invoice::where([
                    "invoices.tenant_id" => $tenant->id,
                    "invoices.created_by" => $request->user()->id
                ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('invoices.invoice_date', "<=", $request->end_date);
                    })
                    ->when(!empty($request->property_ids), function ($query) use ($request) {
                        $null_filter = collect(array_filter($request->property_ids))->values();
                        $property_ids =  $null_filter->all();
                        return $query->whereIn("invoices.property_id",$property_ids);
                    })
                    ->select('invoices.id', 'invoices.total_amount', 'invoices.invoice_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice' as type"), 'invoices.due_date as due_date');

                $invoicePaymentQuery = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
                    ->where([
                        "invoices.tenant_id" => $tenant->id,
                        "invoices.created_by" => $request->user()->id
                    ])
                    ->when(!empty($request->start_date), function ($query) use ($request) {
                        return $query->where('invoice_payments.payment_date', ">=", $request->start_date);
                    })
                    ->when(!empty($request->end_date), function ($query) use ($request) {
                        return $query->where('invoice_payments.payment_date', "<=", $request->end_date);
                    })
                    ->when(!empty($request->property_ids), function ($query) use ($request) {
                        $null_filter = collect(array_filter($request->property_ids))->values();
                        $property_ids =  $null_filter->all();
                        return $query->whereIn("invoices.property_id",$property_ids);
                    })
                    ->select('invoice_payments.invoice_id', 'invoice_payments.amount as total_amount', 'invoice_payments.payment_date as created_at', 'invoices.invoice_reference', DB::raw("'invoice_payment' as type"), 'invoices.due_date as due_date');



                $activitiesQuery = $invoiceQuery
                    ->unionAll($invoicePaymentQuery)
                    ->orderBy('created_at', 'asc');


                $activitiesPaginated = $activitiesQuery->paginate($perPage);




                $section_1["invoice_payment_total_amount"] =   collect($activitiesPaginated->items())->filter(function ($item) {
                    return $item->type == 'invoice_payment';
                })->sum("total_amount");

                $section_1["invoice_total_amount"] =   collect($activitiesPaginated->items())->filter(function ($item) {
                    return $item->type == 'invoice';
                })->sum("total_amount");
                $section_1["start_date"] = $request->start_date;
                $section_1["end_date"] = $request->end_date;
                $section_1["tenant"] = $tenant;
                return response()->json([
                    "section_1" => $section_1,
                    "section_2" => $activitiesPaginated,
                    "opening_balance" => ($total_past_invoice_amount - $total_past_invoice_payment_amount)
                ], 200);
            } else {
                $error =  [
                    "message" => "The given data was invalid.",
                    "errors" => [
                        "property_id" => ["property must be selected if landlord or tenant is not selected."],
                        "tenant_id" => ["tenant must be selected if landlord or property is not selected."],
                        "landlord_id" => ["landlord must be selected if tenant or property is not selected."]
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


            $data["total_paid_amount"] = Invoice::leftJoin('invoice_payments', 'invoice_payments.invoice_id', '=', 'invoices.id')
                ->where([
                    "invoices.created_by" => $request->user()->id
                ])
                ->where("invoices.status", "!=", "draft")
                ->sum("invoice_payments.amount");

            $data["total_paid_invoice_count"] = Invoice::where([
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


            $data["total_invoice_amount"] = Invoice::where([
                "invoices.created_by" => $request->user()->id
            ])
                ->where("invoices.status", "!=", "draft")
                ->sum("invoices.total_amount");







            $data["total_due_invoice_count"] = Invoice::where([
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
            $data["total_due_invoice_amount"] =  $data["total_due_invoice_amount"]->sum("total_due");
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
            $data["total_overdue_invoice_amount"] =  $data["total_overdue_invoice_amount"]->sum("total_due");
            $currentDate = Carbon::now();
            $endDate = $currentDate->copy()->addDays(15);

            $data["next_15_days_invoice_due_dates"] = Invoice::where([
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
            $data["next_15_days_invoice_due_amounts"] = $data["next_15_days_invoice_due_amounts"]->sum('total_due');












            return response()->json($data, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }
}
