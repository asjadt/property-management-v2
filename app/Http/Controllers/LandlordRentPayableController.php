<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\MultipleFileUploadRequest;
use App\Http\Requests\ExpenseCreateRequest;
use App\Http\Requests\ExpenseUpdateRequest;
use App\Http\Requests\LandlordRentPayableCreateRequest;
use App\Http\Requests\LandlordRentPayableUpdateRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\LandlordPayableRent;
use App\Models\LandlordRentPayable;
use App\Models\Rent;
use App\Models\RentAdjustment;
use App\Models\Repair;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LandlordRentPayableController extends Controller
{
    use ErrorUtil, UserActivityUtil, BasicUtil;

    /**
     *
     * @OA\Post(
     *      path="/v1.0/landlord-rent-payables-file",
     *      operationId="createLandlordRentPayableFile",
     *      tags={"property_management.expense_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store reciept file",
     *      description="This method is to store reciept file",
     *
     *  @OA\RequestBody(
     *   * @OA\MediaType(
     *     mediaType="multipart/form-data",
     *     @OA\Schema(
     *         required={"file"},
     *         @OA\Property(
     *             description="file to upload",
     *             property="file",
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

    public function createLandlordRentPayableFile(FileUploadRequest $request)
    {
        try {
            $this->storeActivity($request, "");

            $request_data = $request->validated();

            $location =  config("setup-config.rent_payable_file");

            $new_file_name = time() . '_' . str_replace(' ', '_', $request_data["file"]->getClientOriginalName());


            $request_data["file"]->move(public_path($location), $new_file_name);


            return response()->json(["file" => $new_file_name, "location" => $location, "full_location" => ("/" . $location . "/" . $new_file_name)], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Post(
     *      path="/v1.0/landlord-rent-payables-file/multiple",
     *      operationId="createLandlordRentPayableFileMultiple",
     *      tags={"property_management.expense_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *      summary="This method is to store multiple expense file",
     *      description="This method is to store multiple expense file",
     *
     *  @OA\RequestBody(
     *   * @OA\MediaType(
     *     mediaType="multipart/form-data",
     *     @OA\Schema(
     *         required={"files[]"},
     *         @OA\Property(
     *             description="array of files to upload",
     *             property="files[]",
     *             type="array",
     *             @OA\Items(
     *                 type="file"
     *             ),
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

    public function createLandlordRentPayableFileMultiple(MultipleFileUploadRequest $request)
    {
        try {
            $this->storeActivity($request, "");

            $request_data = $request->validated();

            $location =  config("setup-config.rent_payable_file");

            $files = [];
            if (!empty($request_data["files"])) {
                foreach ($request_data["files"] as $file) {
                    $new_file_name = time() . '_' . $file->getClientOriginalName();
                    $new_file_name = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                    $file->move(public_path($location), $new_file_name);

                    array_push($files, ("/" . $location . "/" . $new_file_name));
                }
            }

            return response()->json(["files" => $files], 201);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Post(
     *      path="/v1.0/landlord-rent-payables",
     *      operationId="createLandlordRentPayable",
     *      tags={"property_management.landlord_rent_payable"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store Landlord Rent Payable",
     *      description="This method is to store Landlord Rent Payable",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id","payment_method","status","total_amount","create_date"},

     *             @OA\Property(property="payment_method", type="string", example="Bank Transfer"),
     *             @OA\Property(property="item_description", type="string", example="Monthly rent for August"),
     *             @OA\Property(property="status", type="string", example="pending"),
     *             @OA\Property(property="total_amount", type="number", format="float", example=5000.00),
     *             @OA\Property(property="create_date", type="string", format="date", example="2025-08-16"),
     *             @OA\Property(property="landlord_id", type="string", format="date", example="1"),
     *             @OA\Property(property="is_active", type="boolean", example=true),

     *             @OA\Property(
     *                 property="payable_rents",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="number", format="int64", example=1),
     *                     @OA\Property(property="rent_id", type="number", format="int64", example=101)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="rent_adjustments",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="number", format="int64", example=1),
     *                     @OA\Property(property="amount", type="number", format="float", example=100.00),
     *                     @OA\Property(property="description", type="string", example="Late payment adjustment"),
     *
     *                    @OA\Property(property="repair_id", type="number", format="int64", example=1),
     *                    @OA\Property(property="expense_id", type="number", format="int64", example=1),
     *  *                    @OA\Property(property="files", type="string", format="string", example=1),
     *
     *
     *                 )
     *             )
     *         )
     *     ),
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

//     public function createLandlordRentPayable(LandlordRentPayableCreateRequest $request)
//     {
//         try {
//             $this->storeActivity($request, "");

//             return DB::transaction(function () use ($request) {

//                 $request_data = $request->validated();


//                 $request_data['created_by'] = $request->user()->id;

//                 // Create main record
//                 $landlord_rent_payable = LandlordRentPayable::create($request_data);

//                 // Generate random ID
//                 $landlord_rent_payable->generated_id = Str::random(4) . $landlord_rent_payable->id . Str::random(4);
//                 $landlord_rent_payable->save();

//                 $payableRentIds = [];

//                 // Create related rents
//                 if (!empty($request_data['payable_rents'])) {
//                     foreach ($request_data['payable_rents'] as $rentData) {
//                         $rent = LandlordPayableRent::create([
//                             'landlord_rent_payable_id' => $landlord_rent_payable->id,
//                             'rent_id' => $rentData['rent_id']
//                         ]);
//                         $payableRentIds[] = $rent->rent_id;
//                     }
//                 }


//                 // Create related rent adjustments
//                 if (!empty($request_data['rent_adjustments'])) {
//                     foreach ($request_data['rent_adjustments'] as $adjData) {

//                         if (!empty($adjData["repair_id"])) {
//                             $valid_repair =  Repair::where([
//                                 "id" => $adjData["repair_id"]
//                             ])
//                                  ->whereDoesntHave('invoice_items',function ($query) use ($landlord_rent_payable) {
//                                     $query->whereHas("invoice", function ($query) use ($landlord_rent_payable) {

//                                         $query->whereNotIn("invoices.landlord_rent_payable_id", [$landlord_rent_payable->id]);
//                                     });

//                                 })
//                                 // ->where([
//                                 //     "paid_by" => "agent"
//                                 // ])
//                                 ->first();
//                             if (!$valid_repair) {
//                                 $error =  [
//                                     "message" => "The given data was invalid.",
//                                     "errors" => ["invoice_items" => ["invalid repair item"]]
//                                 ];
//                                 throw new Exception(json_encode($error), 422);
//                             }

//                             $valid_repair->paid_by = "landlord";
//                             $valid_repair->save();
//                         }
//                         if (!empty($adjData["expense_id"])) {
//                             $valid_expense =  Expense::where([
//                                 "id" => $adjData["expense_id"]
//                             ])
//                                 ->whereDoesntHave('invoice_items',function ($query) use ($landlord_rent_payable) {
//                                     $query->whereHas("invoice", function ($query) use ($landlord_rent_payable) {

//                                         $query->whereNotIn("invoices.landlord_rent_payable_id", [$landlord_rent_payable->id]);
//                                     });

//                                 })
//                                 ->whereDoesntHave('rent_adjustments', function ($query) use ($landlord_rent_payable) {
//                                     $query->whereNotIn("landlord_rent_payable_id", [$landlord_rent_payable->id]);
//                                 })
//                                 // ->where([
//                                 //     "paid_by" => "agent"
//                                 // ])
//                                 ->first();
//                             if (!$valid_expense) {
//                                 $error =  [
//                                     "message" => "The given data was invalid.",
//                                     "errors" => ["invoice_items" => ["invalid expense item"]]
//                                 ];
//                                 throw new Exception(json_encode($error), 422);
//                             }
//                             $valid_expense->paid_by = "landlord";
//                             $valid_expense->save();
//                         }




//                         RentAdjustment::create([
//                             'landlord_rent_payable_id' => $landlord_rent_payable->id,
//                             'amount' => $adjData['amount'],
//                             'description' => $adjData['description'] ?? null,
//                             "files" => $adjData["files"] ?? [],
//                             'expense_id' => $adjData['expense_id'] ?? null,
//                             'repair_id' => $adjData['repair_id'] ?? null,
//                         ]);
//                     }
//                 }

//                 // Calculate total_amount
//                 $total_rent_amount = !empty($payableRentIds) ? Rent::whereIn('id', $payableRentIds)->sum('paid_amount') : 0;

//                 $adjustment_amount = $landlord_rent_payable->rent_adjustments()->sum('amount');

//                 $landlord_rent_payable->total_amount = $total_rent_amount + $adjustment_amount;
//                 $landlord_rent_payable->save();

//                 // Load relations
//                 $landlord_rent_payable->load(['payable_rents.rent', 'rent_adjustments', 'creator']);


// $business = Business::where("owner_id", $request->user()->id)->first();
// $this->handleLandlordInvoice($landlord_rent_payable, $request_data, $business, auth()->user(),$total_rent_amount);




//                 return response()->json($landlord_rent_payable, 201);
//             });
//         } catch (Exception $e) {
//             return $this->sendError($e, 500, $request);
//         }
//     }




   public function createLandlordRentPayable(LandlordRentPayableCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "");

            return DB::transaction(function () use ($request) {

                $request_data = $request->validated();


                $request_data['created_by'] = $request->user()->id;

                // Create main record
                $landlord_rent_payable = LandlordRentPayable::create($request_data);

                // Generate random ID
                $landlord_rent_payable->generated_id = Str::random(4) . $landlord_rent_payable->id . Str::random(4);
                $landlord_rent_payable->save();

                $payableRentIds = [];


                if($request_data["invoice_id"]) {

                     Invoice::where([
                        "id" => $request_data["invoice_id"]
                     ])
                     ->delete();
                }



                // Create related rents
                if (!empty($request_data['payable_rents'])) {
                    foreach ($request_data['payable_rents'] as $rentData) {
                        $rent = LandlordPayableRent::create([
                            'landlord_rent_payable_id' => $landlord_rent_payable->id,
                            'rent_id' => $rentData['rent_id']
                        ]);
                        $payableRentIds[] = $rent->rent_id;
                    }
                }


                // Create related rent adjustments
                if (!empty($request_data['rent_adjustments'])) {
                    foreach ($request_data['rent_adjustments'] as $adjData) {

                        if (!empty($adjData["repair_id"])) {
                            $valid_repair =  Repair::where([
                                "id" => $adjData["repair_id"]
                            ])
                                 ->whereDoesntHave('invoice_items',function ($query) use ($landlord_rent_payable) {
                                    $query->whereHas("invoice", function ($query) use ($landlord_rent_payable) {

                                        $query->whereNotIn("invoices.landlord_rent_payable_id", [$landlord_rent_payable->id]);
                                    });

                                })
                                // ->where([
                                //     "paid_by" => "agent"
                                // ])
                                ->first();
                            if (!$valid_repair) {
                                $error =  [
                                    "message" => "The given data was invalid.",
                                    "errors" => ["invoice_items" => ["invalid repair item"]]
                                ];
                                throw new Exception(json_encode($error), 422);
                            }

                            $valid_repair->paid_by = "landlord";
                            $valid_repair->save();
                        }
                        if (!empty($adjData["expense_id"])) {
                            $valid_expense =  Expense::where([
                                "id" => $adjData["expense_id"]
                            ])
                                ->whereDoesntHave('invoice_items',function ($query) use ($landlord_rent_payable) {
                                    $query->whereHas("invoice", function ($query) use ($landlord_rent_payable) {

                                        $query->whereNotIn("invoices.landlord_rent_payable_id", [$landlord_rent_payable->id]);
                                    });

                                })
                                ->whereDoesntHave('rent_adjustments', function ($query) use ($landlord_rent_payable) {
                                    $query->whereNotIn("landlord_rent_payable_id", [$landlord_rent_payable->id]);
                                })
                                // ->where([
                                //     "paid_by" => "agent"
                                // ])
                                ->first();
                            if (!$valid_expense) {
                                $error =  [
                                    "message" => "The given data was invalid.",
                                    "errors" => ["invoice_items" => ["invalid expense item"]]
                                ];
                                throw new Exception(json_encode($error), 422);
                            }
                            $valid_expense->paid_by = "landlord";
                            $valid_expense->save();
                        }




                        RentAdjustment::create([
                            'landlord_rent_payable_id' => $landlord_rent_payable->id,
                            'amount' => $adjData['amount'],
                            'description' => $adjData['description'] ?? null,
                            "files" => $adjData["files"] ?? [],
                            'expense_id' => $adjData['expense_id'] ?? null,
                            'repair_id' => $adjData['repair_id'] ?? null,
                        ]);
                    }
                }

                // Calculate total_amount
                $total_rent_amount = !empty($payableRentIds) ? Rent::whereIn('id', $payableRentIds)->sum('paid_amount') : 0;

                $adjustment_amount = $landlord_rent_payable->rent_adjustments()->sum('amount');

                $landlord_rent_payable->total_amount = $total_rent_amount + $adjustment_amount;
                $landlord_rent_payable->save();

                // Load relations
                $landlord_rent_payable->load(['payable_rents.rent', 'rent_adjustments', 'creator']);


$business = Business::where("owner_id", $request->user()->id)->first();
$this->handleLandlordInvoice($landlord_rent_payable, $request_data, $business, auth()->user(),$total_rent_amount);




                return response()->json($landlord_rent_payable, 201);
            });
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }






    /**
     *
     * @OA\Put(
     *      path="/v1.0/landlord-rent-payables",
     *      operationId="updateLandlordRentPayable",
     *      tags={"property_management.landlord_rent_payable"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update expense",
     *      description="This method is to update expense",
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id","payment_method","status","total_amount","create_date"},
     *             @OA\Property(property="id", type="number", format="int64", example=1),
     *             @OA\Property(property="payment_method", type="string", example="Bank Transfer"),
     *             @OA\Property(property="item_description", type="string", example="Monthly rent for August"),
     *             @OA\Property(property="status", type="string", example="pending"),
     *             @OA\Property(property="total_amount", type="number", format="float", example=5000.00),
     *             @OA\Property(property="create_date", type="string", format="date", example="2025-08-16"),
     *             @OA\Property(property="landlord_id", type="string", format="date", example="1"),
     *
     *             @OA\Property(property="is_active", type="boolean", example=true),
     *

     *             @OA\Property(
     *                 property="payable_rents",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="number", format="int64", example=1),
     *                     @OA\Property(property="rent_id", type="number", format="int64", example=101)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="rent_adjustments",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="number", format="int64", example=1),
     *                     @OA\Property(property="amount", type="number", format="float", example=100.00),
     *                     @OA\Property(property="description", type="string", example="Late payment adjustment"),
     *  *                    @OA\Property(property="repair_id", type="number", format="int64", example=1),
     *                    @OA\Property(property="expense_id", type="number", format="int64", example=1),
     *  *                    @OA\Property(property="files", type="string", format="string", example=1),
     *                 )
     *             )
     *         )
     *     ),
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

    public function updateLandlordRentPayable(LandlordRentPayableUpdateRequest $request)
    {
        try {
            $this->storeActivity($request, "");

            return DB::transaction(function () use ($request) {

                $validated = $request->validated();

                // Encode receipt if present
                if (!empty($validated['receipt'])) {
                    $validated['receipt'] = json_encode($validated['receipt']);
                }

                // Fetch the main model
                $landlord_rent_payable = LandlordRentPayable::where([
                    'id' => $validated['id'],
                    'created_by' => $request->user()->id
                ])->firstOrFail();

             $this->adjust_rent_and_expense_on_rent_payable_delete($landlord_rent_payable->id);


                // Update main fields (excluding total_amount)
                $landlord_rent_payable->fill(array_diff_key($validated, ['total_amount' => '']));
                $landlord_rent_payable->save();

                // Create related rents
                LandlordPayableRent::where('landlord_rent_payable_id', $landlord_rent_payable->id)->delete();
                $payableRentIds = [];
                if (!empty($validated['payable_rents'])) {
                    foreach ($validated['payable_rents'] as $rentData) {
                        $rent = LandlordPayableRent::create([
                            'landlord_rent_payable_id' => $landlord_rent_payable->id,
                            'rent_id' => $rentData['rent_id']
                        ]);
                        $payableRentIds[] = $rent->rent_id;
                    }
                }

                RentAdjustment::where('landlord_rent_payable_id', $landlord_rent_payable->id)->delete();
                // Create related rent adjustments
                if (!empty($validated['rent_adjustments'])) {
                    foreach ($validated['rent_adjustments'] as $adjData) {

                        if (!empty($adjData["repair_id"])) {
                            $valid_repair =  Repair::where([
                                "id" => $adjData["repair_id"]
                            ])
                                ->whereDoesntHave('invoice_items',function ($query) use ($landlord_rent_payable) {
                                    $query->whereHas("invoice", function ($query) use ($landlord_rent_payable) {

                                        $query->whereNotIn("invoices.landlord_rent_payable_id", [$landlord_rent_payable->id]);
                                    });

                                })
                                ->whereDoesntHave('rent_adjustments', function ($query) use ($landlord_rent_payable) {
                                    $query->whereNotIn("landlord_rent_payable_id", [$landlord_rent_payable->id]);
                                })
                                // ->where([
                                //     "paid_by" => "agent"
                                // ])
                                ->first();
                            if (!$valid_repair) {
                                $error =  [
                                    "message" => "The given data was invalid.",
                                    "errors" => ["invoice_items" => ["invalid repair item"]]
                                ];
                                throw new Exception(json_encode($error), 422);
                            }
                            $valid_repair->paid_by = "landlord";
                            $valid_repair->save();
                        }
                        if (!empty($adjData["expense_id"])) {
                            $valid_expense =  Expense::where([
                                "id" => $adjData["expense_id"]
                            ])
                              ->whereDoesntHave('invoice_items',function ($query) use ($landlord_rent_payable) {
                                    $query->whereHas("invoice", function ($query) use ($landlord_rent_payable) {
                                        $query->whereNotIn("invoices.landlord_rent_payable_id", [$landlord_rent_payable->id]);
                                    });

                                })
                                ->whereDoesntHave('rent_adjustments', function ($query) use ($landlord_rent_payable) {
                                    $query->whereNotIn("landlord_rent_payable_id", [$landlord_rent_payable->id]);
                                })
                                // ->where([
                                //     "paid_by" => "agent"
                                // ])
                                ->first();
                            if (!$valid_expense) {
                                $error =  [
                                    "message" => "The given data was invalid.",
                                    "errors" => ["invoice_items" => ["invalid expense item"]]
                                ];
                                throw new Exception(json_encode($error), 422);
                            }
                            $valid_expense->paid_by = "landlord";
                            $valid_expense->save();
                        }


                        RentAdjustment::create([
                            'landlord_rent_payable_id' => $landlord_rent_payable->id,
                            'amount' => $adjData['amount'],
                            'description' => $adjData['description'] ?? null,
                            "files" => $adjData["files"] ?? [],
                            'expense_id' => $adjData['expense_id'] ?? null,
                            'repair_id' => $adjData['repair_id'] ?? null,
                        ]);
                    }
                }

                // Recalculate total_amount
                $total_rent_amount = 0;
                if (!empty($payableRentIds)) {
                    $total_rent_amount = Rent::whereIn('id', $payableRentIds)->sum('paid_amount');
                }

                $adjustment_amount = $landlord_rent_payable->rent_adjustments()->sum('amount');

                $landlord_rent_payable->total_amount = $total_rent_amount + $adjustment_amount;
                $landlord_rent_payable->save();

                $landlord_rent_payable->load(['payable_rents.rent', 'rent_adjustments', 'creator']);

                $business = Business::where("owner_id", $request->user()->id)->first();
$this->handleLandlordInvoice($landlord_rent_payable, $validated, $business, $request->user(),$total_rent_amount);



                return response()->json($landlord_rent_payable, 200);
            });
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    protected function handleLandlordInvoice(LandlordRentPayable $landlord_rent_payable, array $request_data, Business $business, $user,$total_rent_amount)
{
    // Delete previous invoices related to this rent payable
    $existingInvoices = Invoice::where('landlord_rent_payable_id', $landlord_rent_payable->id)->get();
    foreach ($existingInvoices as $invoice) {
        // Delete invoice items
        InvoiceItem::where('invoice_id', $invoice->id)->delete();
        // Delete invoice payments
        InvoicePayment::where('invoice_id', $invoice->id)->delete();
        // Delete invoice itself
        $invoice->delete();
    }

    $adjustment_amount = $landlord_rent_payable->rent_adjustments()->sum('amount');

    if (!empty($request_data['rent_adjustments']) && $adjustment_amount < 0) {

        $current_number = 1;
        do {
            $invoice_reference = str_pad($current_number, 4, '0', STR_PAD_LEFT);
            $current_number++;
        } while (
            Invoice::where([
                'invoice_reference' => $invoice_reference,
                'created_by' => $user->id
            ])->exists()
        );

        $invoice_data = [
            "logo" => $business->logo,
            "invoice_title" => $business->invoice_title ?? "Invoice",
            "invoice_reference" => $invoice_reference,
            "business_name" => $business->name,
            "business_address" => $business->address_line_1,
            "invoice_date" => $landlord_rent_payable->create_date,
            "due_date" => $landlord_rent_payable->create_date,
            "footer_text" => $business->footer_text ?? "Thanks for business with us",
            "property_id" => $landlord_rent_payable->payable_rents()->first()?->rent?->tenancy_agreement?->property_id ?? null,
            "status" => ($total_rent_amount + $adjustment_amount < 0) ? "partial" : "paid",
            "sub_total" => -$adjustment_amount,
            "total_amount" => -$adjustment_amount,
            "landlord_rent_payable_id" => $landlord_rent_payable->id,
            "created_by" => $user->id,
        ];

        $invoice = Invoice::create($invoice_data);

        $invoice->generated_id = Str::random(4) . $invoice->id . Str::random(4);
        $invoice->shareable_link = env("FRONT_END_URL_DASHBOARD") . "/share/invoice/" . Str::random(4) . "-" . $invoice->generated_id . "-" . Str::random(4);
        $invoice->save();

        $invoice->landlords()->sync([$request_data['landlord_id']]);

        foreach ($request_data['rent_adjustments'] as $adjData) {
            InvoiceItem::create([
                "name" => $adjData["description"] ?? "",
                "description" => $adjData["description"] ?? "",
                "quantity" => 1,
                "price" => -$adjData["amount"],
                "tax" => 0,
                "amount" => -$adjData["amount"],
                "repair_id" => $adjData["repair_id"] ?? null,
                "expense_id" => $adjData["expense_id"] ?? null,
                "invoice_id" => $invoice->id
            ]);
        }


        $paid_amount = ($total_rent_amount + $adjustment_amount < 0) ? $total_rent_amount : -$adjustment_amount;

        $invoice_payment = InvoicePayment::create([
            "amount" => $paid_amount,
            "payment_method" => "Landlord Payable Adjustment",
            "payment_date" => $landlord_rent_payable->create_date,
            "note" => "Invoice cleared against BiLL ID " . $landlord_rent_payable->id,
            "invoice_id" => $invoice->id,
            "receipt_by" => $user->id
        ]);

        $invoice_payment->generated_id = Str::random(4) . $invoice_payment->id . Str::random(4);
        $invoice_payment->shareable_link = env("FRONT_END_URL_DASHBOARD") . "/share/receipt/" . Str::random(4) . "-" . $invoice_payment->generated_id . "-" . Str::random(4);
        $invoice_payment->save();
    }
}

    /**
     *
     * @OA\Get(
     *      path="/v1.0/landlord-rent-payables/{perPage}",
     *      operationId="getLandlordRentPayables",
     *      tags={"property_management.landlord_rent_payable"},
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
     *
     *  * *      * *  @OA\Parameter(
     * name="rent_id",
     * in="query",
     * description="rent_id",
     * required=true,
     * example="1"
     * ),
     *
     * *      * *  @OA\Parameter(
     * name="property_id",
     * in="query",
     * description="property_id",
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
     * name="expense_category",
     * in="query",
     * description="expense_category",
     * required=true,
     * example="expense_category"
     * ),

     *      summary="This method is to get ",
     *      description="This method is to get",
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

    public function getLandlordRentPayables($perPage, Request $request)
    {
        try {
            $this->storeActivity($request, "");

            $query = LandlordRentPayable::with(['payable_rents.rent', 'rent_adjustments'])
                ->where('created_by', $request->user()->id);

            // Search by description or generated_id
            if (!empty($request->search_key)) {
                $term = $request->search_key;
                $query->where(function ($q) use ($term) {
                    $q->orWhere('item_description', 'like', "%$term%")
                        ->orWhere('generated_id', 'like', "%$term%");
                });
            }

            // Filter by payment method
            if (!empty($request->payment_method)) {
                $query->where('payment_method', $request->payment_method);
            }

            // Filter by status
            if (!empty($request->status)) {
                $query->where('status', $request->status);
            }
            if (!empty($request->landlord_id)) {
                $query->where('landlord_id', $request->landlord_id);
            }


            // Filter by date range
            if (!empty($request->start_date)) {
                $query->where('create_date', '>=', $request->start_date);
            }
            if (!empty($request->end_date)) {
                $query->where('create_date', '<=', $request->end_date);
            }

            if (!empty($request->id)) {
                $query->where('generated_id', $request->id);
            }

            // Filter by is_active
            if (!is_null($request->is_active)) {
                $query->where('is_active', $request->is_active);
            }

            // Filter by related rent_id
            if (!empty($request->rent_id)) {
                $query->whereHas('payable_rents', function ($q) use ($request) {
                    $q->where('rent_id', $request->rent_id);
                });
            }



            // Pagination and ordering
            $orderBy = $request->order_by ?? 'desc';
            $landlord_rent_payables = $query->orderBy('id', $orderBy)
                ->paginate($perPage);

            return response()->json($landlord_rent_payables, 200);
        } catch (\Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    /**
 *
 * @OA\Get(
 *      path="/v2.0/landlord-rent-payables/{perPage}",
 *      operationId="getLandlordRentPayablesV2",
 *      tags={"property_management.landlord_rent_payable"},
 *      security={{"bearerAuth": {}}},
 *
 *      @OA\Parameter(
 *         name="perPage",
 *         in="path",
 *         description="Number of records per page",
 *         required=true,
 *         example=10
 *      ),
 *      @OA\Parameter(
 *         name="search_key",
 *         in="query",
 *         description="Search by item description or generated ID",
 *         required=false,
 *         example="rent"
 *      ),
 *      @OA\Parameter(
 *         name="landlord_id",
 *         in="query",
 *         description="Filter by landlord ID",
 *         required=false,
 *         example=1
 *      ),
 *      @OA\Parameter(
 *         name="rent_id",
 *         in="query",
 *         description="Filter by related rent ID",
 *         required=false,
 *         example=1
 *      ),
 *      @OA\Parameter(
 *         name="start_date",
 *         in="query",
 *         description="Filter from create_date",
 *         required=false,
 *         example="2025-01-01"
 *      ),
 *      @OA\Parameter(
 *         name="end_date",
 *         in="query",
 *         description="Filter till create_date",
 *         required=false,
 *         example="2025-09-26"
 *      ),
 *      @OA\Parameter(
 *         name="status",
 *         in="query",
 *         description="Filter by status",
 *         required=false,
 *         example="pending"
 *      ),
 *      @OA\Parameter(
 *         name="payment_method",
 *         in="query",
 *         description="Filter by payment method",
 *         required=false,
 *         example="bank_transfer"
 *      ),
 *      @OA\Parameter(
 *         name="is_active",
 *         in="query",
 *         description="Filter by active status (1/0)",
 *         required=false,
 *         example=1
 *      ),
 *      @OA\Parameter(
 *         name="order_by",
 *         in="query",
 *         description="Order by ID ASC/DESC",
 *         required=false,
 *         example="DESC"
 *      ),
 *
 *      summary="Get landlord rent payables with detailed data highlights",
 *      description="Returns landlord rent payables with Total Rent, Total Paid via Payable, Total Deducted, and Total Unassigned Rent",
 *
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *          @OA\JsonContent()
 *      ),
 *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
 *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
 *      @OA\Response(response=422, description="Unprocessable Content", @OA\JsonContent()),
 *      @OA\Response(response=400, description="Bad Request", @OA\JsonContent()),
 *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
 * )
 */
public function getLandlordRentPayablesV2($perPage, Request $request)
{
    try {
        $this->storeActivity($request, "Fetch landlord rent payables summary");

        // MAIN QUERY
        $query = LandlordRentPayable::with(['payable_rents.rent', 'rent_adjustments'])
            ->where('created_by', $request->user()->id);

        // Filters
        if (!empty($request->search_key)) {
            $term = $request->search_key;
            $query->where(function ($q) use ($term) {
                $q->orWhere('item_description', 'like', "%$term%")
                  ->orWhere('generated_id', 'like', "%$term%");
            });
        }

        if (!empty($request->payment_method)) $query->where('payment_method', $request->payment_method);
        if (!empty($request->status)) $query->where('status', $request->status);
        if (!empty($request->landlord_id)) $query->where('landlord_id', $request->landlord_id);
        if (!empty($request->start_date)) $query->where('create_date', '>=', $request->start_date);
        if (!empty($request->end_date)) $query->where('create_date', '<=', $request->end_date);
        if (!empty($request->rent_id)) {
            $query->whereHas('payable_rents', fn($q) => $q->where('rent_id', $request->rent_id));
        }
        if (!is_null($request->is_active)) $query->where('is_active', $request->is_active);

        $orderBy = $request->order_by ?? 'desc';
        $landlord_rent_payables = $query->orderBy('id', $orderBy)
            ->paginate($perPage);

        // =========================
        // DATA HIGHLIGHTS
        // =========================

        // 1. Total Rent Amount (all collected rents)
        $total_rent_amount = Rent::where('created_by', $request->user()->id)
        ->whereHas("tenancy_agreement", function($q) use ($request) {
            $q->whereHas('property', function($q) use ($request) {
                $q->whereHas("property_landlords", function($q) use ($request) {
                    $q->where('landlords.id', $request->landlord_id);
                });
            });
        })

        ->sum('paid_amount');

        // 2. Total Paid via Payable (rents already linked to payables)
        $total_paid = Rent::whereHas('landlord_rent_payables')
        ->where('created_by', $request->user()->id)
        ->whereHas("tenancy_agreement", function($q) use ($request) {
            $q->whereHas('property', function($q) use ($request) {
                $q->whereHas("property_landlords", function($q) use ($request) {
                    $q->where('landlords.id', $request->landlord_id);
                });
            });
        })

        ->sum('paid_amount');

        // 3. Total Deducted (all adjustments)
        $total_deducted = RentAdjustment::whereHas('landlord_rent_payable', function($q) use ($request) {
            $q->where('landlord_rent_payables.created_by', $request->user()->id)
           ->where('landlord_rent_payables.landlord_id', request()->input("landlord_id"));

        })->sum('amount');

        // 4. Total Unassigned Rent (rents collected but not linked to any payable)
        $total_unassigned_rent = Rent::where('paid_amount', '>', 0)
            ->whereDoesntHave('landlord_rent_payables')
        ->where('created_by', $request->user()->id)
        ->whereHas("tenancy_agreement", function($q) use ($request) {
            $q->whereHas('property', function($q) use ($request) {
                $q->whereHas("property_landlords", function($q) use ($request) {
                    $q->where('landlords.id', $request->landlord_id);
                });
            });
        })->sum('paid_amount');

        $data_highlights = [
            'total_rent_amount' => $total_rent_amount,
            'total_paid_via_payable' => $total_paid,
            'total_deducted' => $total_deducted,
            'total_due_rents' => $total_unassigned_rent,
        ];

        $response = [
            'data' => $landlord_rent_payables,
            'data_highlights' => $data_highlights,
        ];

        return response()->json($response, 200);

    } catch (\Exception $e) {
        return $this->sendError($e, 500, $request);
    }
}


    /**
     *
     * @OA\Get(
     *      path="/v1.0/landlord-rent-payables/get/single/{id}",
     *      operationId="getLandlordRentPayableById",
     *      tags={"property_management.landlord_rent_payable"},
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

     *      summary="This method is to get by id",
     *      description="This method is to get by id",
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

    public function getLandlordRentPayableById($id, Request $request)
    {
        try {
            $this->storeActivity($request, "");


            $landlord_rent_payable = LandlordRentPayable::with("payable_rents", "rent_adjustments")
                ->where([
                    "generated_id" => $id,
                    "created_by" => $request->user()->id
                ])
                ->first();

            if (!$landlord_rent_payable) {
                return response()->json([
                    "message" => "no landlord rent payable found"
                ], 404);
            }

            return response()->json($landlord_rent_payable, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/landlord-rent-payables/{id}",
     *      operationId="deleteLandlordRentPayableById",
     *      tags={"property_management.landlord_rent_payable"},
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
     *      summary="This method is to delete expense by id",
     *      description="This method is to delete expense by id",
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

    public function deleteLandlordRentPayableById($id, Request $request)
    {


        try {
            $this->storeActivity($request, "");

            $business = Business::where([
                "owner_id" => $request->user()->id
            ])->first();

            if (!$business) {
                return response()->json([
                    "message" => "you don't have a valid business"
                ], 401);
            }
            if (!($business->pin == $request->header("pin"))) {
                return response()->json([
                    "message" => "invalid pin"
                ], 401);
            }

            $landlord_rent_payable = LandlordRentPayable::where([
                "id" => $id,
                "created_by" => $request->user()->id
            ])
                ->first();

            if (!$landlord_rent_payable) {
                return response()->json([
                    "message" => "no expense found"
                ], 404);
            }


            $this->adjust_rent_and_expense_on_rent_payable_delete($landlord_rent_payable->id);


            $landlord_rent_payable->delete();

            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
