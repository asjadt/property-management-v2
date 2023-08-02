<?php

namespace App\Http\Controllers;

use App\Http\Requests\InvoiceReminderCreateRequest;
use App\Http\Requests\InvoiceReminderNumberToDateCreateRequest;
use App\Http\Requests\InvoiceReminderUpdateForm;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\InvoiceReminder;
use DateTime;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceReminderController extends Controller
{
    use ErrorUtil, UserActivityUtil;

/**
 *
 * @OA\Post(
 *      path="/v1.0/invoice-reminders/number-todate-convert",
 *      operationId="createInvoiceReminderNumberDateConvert",
 *      tags={"property_management.invoice_reminder_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store invoice reminder number to date convert",
 *      description="This method is to store invoice reminder number to date convert",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"name","description","logo"},

  *             @OA\Property(property="send_reminder", type="string", format="string",example="1"),
 *            @OA\Property(property="reminder_date_amount", type="number", format="number",example="14"),
 *            @OA\Property(property="invoice_id", type="string", format="string",example="1"),
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

 public function createInvoiceReminderNumberDateConvert(InvoiceReminderNumberToDateCreateRequest $request)
 {
     try {
         $this->storeActivity($request,"");
         return DB::transaction(function () use ($request) {



             $insertableData = $request->validated();








             $invoice = Invoice::where([
                 "id" => $insertableData["invoice_id"],
                 "invoices.created_by" => $request->user()->id

             ])
             ->first();
             if(!$invoice) {
                return response()->json([
                    "message" => "no invoice found"
                ],404);
             }
             if(empty($invoice->due_date)) {
                return response()->json([
                    "message" => "invoice due not defined"
                ],404);
             }
             $due_date = DateTime::createFromFormat('Y-m-d', $invoice->due_date);
             if ($due_date !== false) {
                 $due_date->modify(($insertableData["reminder_date_amount"] . ' days'));
                 $reminder_date = $due_date->format('Y-m-d');
             } else {
                 $reminder_date = null;
             }

             $insertableData["reminder_status"] = "not_sent";
             $insertableData["reminder_date"] = $reminder_date;

$invoice_reminder =  InvoiceReminder::create($insertableData);




             return response($invoice_reminder, 201);





         });




     } catch (Exception $e) {

         return $this->sendError($e, 500,$request);
     }
 }
/**
 *
 * @OA\Post(
 *      path="/v1.0/invoice-reminders",
 *      operationId="createInvoiceReminder",
 *      tags={"property_management.invoice_reminder_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store invoice reminder",
 *      description="This method is to store invoice reminder",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"name","description","logo"},

  *             @OA\Property(property="send_reminder", type="string", format="string",example="1"),
 *            @OA\Property(property="reminder_date", type="string", format="string",example="2019-06-29"),
 *            @OA\Property(property="invoice_id", type="string", format="string",example="1"),
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

public function createInvoiceReminder(InvoiceReminderCreateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return DB::transaction(function () use ($request) {



            $insertableData = $request->validated();

            $insertableData["reminder_status"] = "not_sent";

            $invoice = Invoice::where([
                "id" => $insertableData["invoice_id"],
                "invoices.created_by" => $request->user()->id

            ])
            ->first();
            if(!$invoice) {
                throw new Exception("something went wrong");
            }

            $invoice_reminder =  InvoiceReminder::create($insertableData);




            return response($invoice_reminder, 201);





        });




    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 * @OA\Put(
 *      path="/v1.0/invoice-reminders",
 *      operationId="updateInvoiceReminder",
 *      tags={"property_management.invoice_reminder_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to update invoice reminder",
 *      description="This method is to update invoice reminder",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"id","name","description","logo"},
 *     *             @OA\Property(property="id", type="number", format="number",example="1"),

  *             @OA\Property(property="send_reminder", type="string", format="string",example="1"),
 *            @OA\Property(property="reminder_date", type="string", format="string",example="2019-06-29"),

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

public function updateInvoiceReminder(InvoiceReminderUpdateForm $request)
{
    try {
        $this->storeActivity($request,"");
        return  DB::transaction(function () use ($request) {

            $updatableData = $request->validated();
            $updatableData["reminder_status"] = "not_sent";
            // $affiliationPrev = InvoiceReminder::where([
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




            $invoice_reminder  = InvoiceReminder::leftJoin('invoices', 'invoice_reminders.invoice_id', '=', 'invoices.id')
            ->where([
                "invoice_reminders.id" => $updatableData["id"],
                "invoices.created_by" => $request->user()->id
            ])->first();



            $invoice_reminder_date = new DateTime($invoice_reminder->reminder_date);
            $updatableData_date = new DateTime($updatableData["reminder_date"]);

            // Extract day components from the dates.
            $invoice_reminder_day = (int)$invoice_reminder_date->format('d');
            $updatableData_day = (int)$updatableData_date->format('d');

            // Compare the day components.
            if ($invoice_reminder_day !== $updatableData_day) {

                $invoice_reminder->reminder_date_amount = NULL;
            }
            $invoice_reminder->send_reminder = $updatableData["send_reminder"];
            $invoice_reminder->reminder_date = $updatableData["reminder_date"];

            $invoice_reminder->save();


            return response($invoice_reminder, 200);
        });
    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v1.0/invoice-reminders/{perPage}",
 *      operationId="getInvoiceReminders",
 *      tags={"property_management.invoice_reminder_management"},
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
 *      summary="This method is to get invoice reminders ",
 *      description="This method is to get invoice reminders",
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

public function getInvoiceReminders($perPage, Request $request)
{
    try {
        $this->storeActivity($request,"");

        // $automobilesQuery = AutomobileMake::with("makes");

        $invoice_reminderQuery =  InvoiceReminder::leftJoin('invoices', 'invoice_reminders.invoice_id', '=', 'invoices.id')
        ->where([
            "invoices.created_by" => $request->user()->id
        ])
        ;

        if (!empty($request->search_key)) {
            $invoice_reminderQuery = $invoice_reminderQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                $query->where("invoices.business_name", "like", "%" . $term . "%");
            });
        }

        if (!empty($request->start_date)) {
            $invoice_reminderQuery = $invoice_reminderQuery->where('invoice_reminders.created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $invoice_reminderQuery = $invoice_reminderQuery->where('invoice_reminders.created_at', "<=", $request->end_date);
        }

        $invoice_reminders = $invoice_reminderQuery
        ->groupBy("invoice_reminders.id")
        ->select(
            "invoice_reminders.*",
            "invoices.invoice_reference",

            )->orderByDesc("invoice_reminders.id")->paginate($perPage);

        return response()->json($invoice_reminders, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}



/**
 *
 * @OA\Get(
 *      path="/v1.0/invoice-reminders/get/single/{id}",
 *      operationId="getInvoiceReminderById",
 *      tags={"property_management.invoice_reminder_management"},
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

 *      summary="This method is to get invoice reminder by id",
 *      description="This method is to get invoice reminder by id",
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

public function getInvoiceReminderById($id, Request $request)
{
    try {
        $this->storeActivity($request,"");


        $invoice_reminder = InvoiceReminder::
        leftJoin('invoices', 'invoice_reminders.invoice_id', '=', 'invoices.id')

        ->where([
            "invoices.created_by" => $request->user()->id,
            "invoice_reminders.id" => $id

        ])
        ->select(
            "invoice_reminders.*",
            "invoices.invoice_reference",

            )
        ->first();

        if(!$invoice_reminder) {
     return response()->json([
"message" => "no invoice reminder found"
],404);
        }


        return response()->json($invoice_reminder, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}










/**
 *
 *     @OA\Delete(
 *      path="/v1.0/invoice-reminders/{id}",
 *      operationId="deleteInvoiceReminderById",
 *      tags={"property_management.invoice_reminder_management"},
 *       security={
 *           {"bearerAuth": {}},
 *           {"pin": {}}
 *       },
 *              @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="id",
 *         required=true,
 *  example="1"
 *      ),
 *      summary="This method is to delete invoice reminder by id",
 *      description="This method is to delete invoice reminder by id",
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

public function deleteInvoiceReminderById($id, Request $request)
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

        $invoice_reminder = InvoiceReminder::leftJoin('invoices', 'invoice_reminders.invoice_id', '=', 'invoices.id')

        ->where([
            "invoices.created_by" => $request->user()->id,
            "invoice_reminders.id" => $id

        ])
        ->select("invoice_reminders.id")
        ->first();

        if(!$invoice_reminder) {
     return response()->json([
"message" => "no invoice reminder found"
],404);
        }
        $invoice_reminder->delete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 *     @OA\Delete(
 *      path="/v1.0/invoice-reminders/without-pin/{id}",
 *      operationId="deleteInvoiceReminderWithoutById",
 *      tags={"property_management.invoice_reminder_management"},
 *       security={
 *           {"bearerAuth": {}},
 *           {"pin": {}}
 *       },
 *              @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="id",
 *         required=true,
 *  example="1"
 *      ),
 *      summary="This method is to delete invoice reminder by id",
 *      description="This method is to delete invoice reminder by id",
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

 public function deleteInvoiceReminderWithoutById($id, Request $request)
 {

     try {
         $this->storeActivity($request,"");

         // $business = Business::where([
         //     "owner_id" => $request->user()->id
         //   ])->first();

         // if(!$business) {
         //     return response()->json([
         //      "message" => "you don't have a valid business"
         //     ],401);
         //  }
         //  if(!($business->pin == $request->header("pin"))) {
         //      return response()->json([
         //          "message" => "invalid pin"
         //         ],401);
         //  }

         $invoice_reminder = InvoiceReminder::leftJoin('invoices', 'invoice_reminders.invoice_id', '=', 'invoices.id')

         ->where([
             "invoices.created_by" => $request->user()->id,
             "invoice_reminders.id" => $id

         ])
         ->select("invoice_reminders.id")
         ->first();

         if(!$invoice_reminder) {
      return response()->json([
 "message" => "no invoice reminder found"
 ],404);
         }
         $invoice_reminder->delete();

         return response()->json(["ok" => true], 200);
     } catch (Exception $e) {

         return $this->sendError($e, 500,$request);
     }
 }

}
