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
use ErrorUtil,UserActivityUtil;

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
 * *  @OA\Parameter(
* name="property_id",
* in="query",
* description="property_id",
* required=true,
* example="property_id"
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
         $this->storeActivity($request,"");




        //  $propertyQuery =  Property::where(["created_by" => $request->user()->id]);

        //  if (!empty($request->search_key)) {
        //      $propertyQuery = $propertyQuery->where(function ($query) use ($request) {
        //          $term = $request->search_key;
        //          $query->where("name", "like", "%" . $term . "%");
        //          $query->orWhere("address", "like", "%" . $term . "%");
        //      });
        //  }

        //  if (!empty($request->address)) {
        //      $propertyQuery =  $propertyQuery->orWhere("address", "like", "%" . $request->address . "%");
        //  }

        //  if (!empty($request->start_date)) {
        //      $propertyQuery = $propertyQuery->where('created_at', ">=", $request->start_date);
        //  }
        //  if (!empty($request->end_date)) {
        //      $propertyQuery = $propertyQuery->where('created_at', "<=", $request->end_date);
        //  }

        //  $properties = $propertyQuery->orderByDesc("id")->paginate($perPage);


        // if(!empty($request->property_id)) {
        //     $property = Property::where([
        //         "id" => $request->property_id
        //     ])
        //     ->first();
        //     if(!$property) {
        //           return response()->json([
        //             "message" => "no property found"
        //           ],404);
        //     }
        //     $repairQuery = Repair::where([
        //         "repairs.property_id" => $property->id
        //     ])
        //     ->when(!empty($request->start_date), function ($query) use ($request) {
        //         return $query->where('repairs.created_at', ">=", $request->start_date);
        //     })
        //     ->when(!empty($request->end_date), function ($query) use ($request) {
        //         return $query->where('repairs.created_at', "<=", $request->end_date);
        //     })
        //     ->select('repairs.price', 'repairs.created_at', DB::raw("'repair' as type"));

        //     $invoiceQuery = Invoice::where([
        //         "invoices.property_id" => $property->id
        //     ])
        //     ->when(!empty($request->start_date), function ($query) use ($request) {
        //         return $query->where('invoices.created_at', ">=", $request->start_date);
        //     })
        //     ->when(!empty($request->end_date), function ($query) use ($request) {
        //         return $query->where('invoices.created_at', "<=", $request->end_date);
        //     })
        //     ->select('invoices.total_amount', 'invoices.created_at', DB::raw("'invoice' as type"));


        //     $receiptQuery = Receipt::where([
        //         "receipts.property_address" => $property->address
        //     ])
        //     ->when(!empty($request->start_date), function ($query) use ($request) {
        //         return $query->where('receipts.created_at', ">=", $request->start_date);
        //     })
        //     ->when(!empty($request->end_date), function ($query) use ($request) {
        //         return $query->where('receipts.created_at', "<=", $request->end_date);
        //     })
        //     ->select('receipts.amount', 'receipts.created_at', DB::raw("'receipt' as type"));

        //     $activitiesQuery = $repairQuery
        //         ->unionAll($invoiceQuery)
        //         ->unionAll($receiptQuery)
        //         ->orderBy('created_at', 'asc');
        // }



        if(!empty($request->property_id)) {
            $property = Property::where([
                "id" => $request->property_id
            ])
            ->first();
            if(!$property) {
                  return response()->json([
                    "message" => "no property found"
                  ],404);
            }


            $invoiceQuery = Invoice::where([
                "invoices.property_id" => $property->id
            ])
            ->when(!empty($request->start_date), function ($query) use ($request) {
                return $query->where('invoices.created_at', ">=", $request->start_date);
            })
            ->when(!empty($request->end_date), function ($query) use ($request) {
                return $query->where('invoices.created_at', "<=", $request->end_date);
            })
            ->select('invoices.id','invoices.total_amount', 'invoices.created_at', DB::raw("'invoice' as type"));

            $invoicePaymentQuery = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->where([
                "invoices.property_id" => $property->id
            ])
            ->when(!empty($request->start_date), function ($query) use ($request) {
                return $query->where('invoice_payments.created_at', ">=", $request->start_date);
            })
            ->when(!empty($request->end_date), function ($query) use ($request) {
                return $query->where('invoice_payments.created_at', "<=", $request->end_date);
            })
            ->select('invoice_payments.invoice_id','invoice_payments.amount', 'invoice_payments.created_at', DB::raw("'invoice_payment' as type"));



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
                $section_1["property"] = $property;

                return response()->json([
                    "section_1"=>$section_1,
                    "section_2"=>$activitiesPaginated,
                ], 200);

        }
     else   if(!empty($request->landlord_id)) {
            $landlord = Landlord::where([
                "id" => $request->landlord_id
            ])
            ->first();
            if(!$landlord) {
                  return response()->json([
                    "message" => "no landlord found"
                  ],404);
            }


            $invoiceQuery = Invoice::where([
                "invoices.landlord_id" => $landlord->id
            ])
            ->when(!empty($request->start_date), function ($query) use ($request) {
                return $query->where('invoices.created_at', ">=", $request->start_date);
            })
            ->when(!empty($request->end_date), function ($query) use ($request) {
                return $query->where('invoices.created_at', "<=", $request->end_date);
            })
            ->select('invoices.id','invoices.total_amount', 'invoices.created_at', DB::raw("'invoice' as type"));

            $invoicePaymentQuery = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->where([
                "invoices.landlord_id" => $landlord->id
            ])
            ->when(!empty($request->start_date), function ($query) use ($request) {
                return $query->where('invoice_payments.created_at', ">=", $request->start_date);
            })
            ->when(!empty($request->end_date), function ($query) use ($request) {
                return $query->where('invoice_payments.created_at', "<=", $request->end_date);
            })
            ->select('invoice_payments.invoice_id','invoice_payments.amount', 'invoice_payments.created_at', DB::raw("'invoice_payment' as type"));



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
                    "section_1"=>$section_1,
                    "section_2"=>$activitiesPaginated,
                ], 200);



        }
        else   if(!empty($request->tenant_id)) {
            $tenant = Tenant::where([
                "id" => $request->tenant_id
            ])
            ->first();
            if(!$tenant) {
                  return response()->json([
                    "message" => "no tenant found"
                  ],404);
            }


            $invoiceQuery = Invoice::where([
                "invoices.tenant_id" => $tenant->id
            ])
            ->when(!empty($request->start_date), function ($query) use ($request) {
                return $query->where('invoices.created_at', ">=", $request->start_date);
            })
            ->when(!empty($request->end_date), function ($query) use ($request) {
                return $query->where('invoices.created_at', "<=", $request->end_date);
            })
            ->select('invoices.id','invoices.total_amount', 'invoices.created_at', DB::raw("'invoice' as type"));

            $invoicePaymentQuery = InvoicePayment::leftJoin('invoices', 'invoices.id', '=', 'invoice_payments.invoice_id')
            ->where([
                "invoices.tenant_id" => $tenant->id
            ])
            ->when(!empty($request->start_date), function ($query) use ($request) {
                return $query->where('invoice_payments.created_at', ">=", $request->start_date);
            })
            ->when(!empty($request->end_date), function ($query) use ($request) {
                return $query->where('invoice_payments.created_at', "<=", $request->end_date);
            })
            ->select('invoice_payments.invoice_id','invoice_payments.amount', 'invoice_payments.created_at', DB::raw("'invoice_payment' as type"));



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
                    "section_1"=>$section_1,
                    "section_2"=>$activitiesPaginated,
                ], 200);



        }
    //    else if(!empty($request->landlord_id)) {
    //         $landlord = Property::where([
    //             "id" => $request->landlord_id
    //         ])
    //         ->first();
    //         if(!$landlord) {
    //               return response()->json([
    //                 "message" => "no landlord found"
    //               ],404);
    //         }
    //         $activitiesQuery = Invoice::where([
    //             "invoices.landlord_id" => $landlord->id
    //         ])
    //             ->when(!empty($request->start_date), function ($query) use ($request) {
    //                 return $query->where('invoices.created_at', ">=", $request->start_date);
    //             })
    //             ->when(!empty($request->end_date), function ($query) use ($request) {
    //                 return $query->where('invoices.created_at', "<=", $request->end_date);
    //             })
    //             ->select('invoices.total_amount', 'invoices.created_at', DB::raw("'invoice' as type"));

    //         // $activitiesQuery = $activitiesQuery->unionAll(
    //         //     Invoice::where([
    //         //         "invoices.property_id" => $request->landlord_id
    //         //     ])
    //         //     ->when(!empty($request->start_date), function ($query) use ($request) {
    //         //         return $query->where('invoices.created_at', ">=", $request->start_date);
    //         //     })
    //         //     ->when(!empty($request->end_date), function ($query) use ($request) {
    //         //         return $query->where('invoices.created_at', "<=", $request->end_date);
    //         //     })
    //         //     ->select('invoices.*')
    //         // );



    //     }
        // else if(!empty($request->tenant_id)) {
        //     $tenant = Property::where([
        //         "id" => $request->tenant_id
        //     ])
        //     ->first();
        //     if(!$tenant) {
        //           return response()->json([
        //             "message" => "no tenant found"
        //           ],404);
        //     }
        //     $invoiceQuery = Invoice::where([
        //         "invoices.tenant_id" => $tenant->id
        //     ])
        //     ->when(!empty($request->start_date), function ($query) use ($request) {
        //         return $query->where('invoices.created_at', ">=", $request->start_date);
        //     })
        //     ->when(!empty($request->end_date), function ($query) use ($request) {
        //         return $query->where('invoices.created_at', "<=", $request->end_date);
        //     })
        //     ->select('invoices.total_amount', 'invoices.created_at', DB::raw("'invoice' as type"));

        //     $receiptQuery = Receipt::where([
        //         "receipts.tenant_id" => $tenant->id
        //     ])
        //     ->when(!empty($request->start_date), function ($query) use ($request) {
        //         return $query->where('receipts.created_at', ">=", $request->start_date);
        //     })
        //     ->when(!empty($request->end_date), function ($query) use ($request) {
        //         return $query->where('receipts.created_at', "<=", $request->end_date);
        //     })
        //     ->select('receipts.amount', 'receipts.created_at', DB::raw("'receipt' as type"));

        //     $activitiesQuery = $invoiceQuery->unionAll($receiptQuery)
        //         ->orderBy('created_at', 'asc');



        // }

        else {
            $error =  [
                "message" => "The given data was invalid.",
                "errors" => [
                    "property_id"=>["property must be selected if landlord or tenant is not selected."],
                    "tenant_id"=>["tenant must be selected if landlord or property is not selected."],
                    "landlord_id"=>["landlord must be selected if tenant or property is not selected."]
                    ]
         ];
            throw new Exception(json_encode($error),422);
        }






     } catch (Exception $e) {

         return $this->sendError($e, 500,$request);
     }
 }
   /**
     *
     * @OA\Get(
     *      path="/v1.0//dashboard",
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

     public function getDashboardData( Request $request)
     {
         try{
             $this->storeActivity($request,"");


             $data["total_paid_amount"] = Invoice::leftJoin('invoice_payments', 'invoice_payments.invoice_id', '=', 'invoices.id')
             ->where([
                "invoices.created_by" => $request->user()->id
             ])
             ->sum("invoice_payments.amount");

             $data["total_paid_invoice"] = Invoice::where([
                "invoices.created_by" => $request->user()->id
             ])
          ->select(

            DB::raw('
            COALESCE(
                invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
                invoices.total_amount
            ) AS total_due
        ')
          )
          ->havingRaw('total_due = 0')
          ->count()
          ;

          $data["total_due_invoice"] = Invoice::where([
            "invoices.created_by" => $request->user()->id
         ])
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


$currentDate = Carbon::now();
$endDate = $currentDate->copy()->addDays(15);

      $data["next_15_days_invoice_due_dates"] = Invoice::where([
        "invoices.created_by" => $request->user()->id
     ])
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
->selectRaw('SUM(COALESCE(
    invoices.total_amount - (SELECT SUM(invoice_payments.amount) FROM invoice_payments WHERE invoice_payments.invoice_id = invoices.id),
    invoices.total_amount
)) AS total_due_sum')
->having('total_due_sum', '>', 0)
->get();
$data["next_15_days_invoice_due_amounts"] = $data["next_15_days_invoice_due_amounts"]->sum('total_due_sum');












             return response()->json($data, 200);
         }catch(Exception $e) {
       return $this->sendError($e, 500,$request);
         }

     }

}
