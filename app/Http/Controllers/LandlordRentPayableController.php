<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\MultipleFileUploadRequest;
use App\Http\Requests\ExpenseCreateRequest;
use App\Http\Requests\ExpenseUpdateRequest;
use App\Http\Requests\LandlordRentPayableCreateRequest;
use App\Http\Requests\LandlordRentPayableUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\Expense;
use App\Models\LandlordPayableRent;
use App\Models\LandlordRentPayable;
use App\Models\Rent;
use App\Models\RentAdjustment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LandlordRentPayableController extends Controller
{
    use ErrorUtil, UserActivityUtil;

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

    public function createLandlordRentPayable(LandlordRentPayableCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "");

            return DB::transaction(function () use ($request) {

                $data = $request->validated();

                $data['created_by'] = $request->user()->id;

                // Create main record
                $rentPayable = LandlordRentPayable::create($data);

                // Generate random ID
                $rentPayable->generated_id = Str::random(4) . $rentPayable->id . Str::random(4);
                $rentPayable->save();

                $payableRentIds = [];

                // Create related rents
                if (!empty($data['payable_rents'])) {
                    foreach ($data['payable_rents'] as $rentData) {
                        $rent = LandlordPayableRent::create([
                            'landlord_rent_payable_id' => $rentPayable->id,
                            'rent_id' => $rentData['rent_id']
                        ]);
                        $payableRentIds[] = $rent->rent_id;
                    }
                }

                // Create related rent adjustments
                if (!empty($data['rent_adjustments'])) {
                    foreach ($data['rent_adjustments'] as $adjData) {
                        if (!empty($adjData["repair_id"])) {
                            $rent_adjustment_exists =    RentAdjustment::where([
                                "repair_id" => $adjData["repair_id"]
                            ])
                                ->whereNotIn("landlord_rent_payable_id", [$rentPayable->id])
                                ->first();
                            if (!$rent_adjustment_exists) {
                                $error =  [
                                    "message" => "The given data was invalid.",
                                    "errors" => ["rent_adjustments" => ["invalid repair item"]]
                                ];
                                throw new Exception(json_encode($error), 422);
                            }
                        }

                        if (!empty($adjData["expense_id"])) {
                            $rent_adjustment_exists =    RentAdjustment::where([
                                "expense_id" => $adjData["expense_id"]
                            ])
                                ->whereNotIn("landlord_rent_payable_id", [$rentPayable->id])
                                ->first();
                            if (!$rent_adjustment_exists) {
                                $error =  [
                                    "message" => "The given data was invalid.",
                                    "errors" => ["rent_adjustments" => ["invalid expense item"]]
                                ];
                                throw new Exception(json_encode($error), 422);
                            }
                        }

                        RentAdjustment::create([
                            'landlord_rent_payable_id' => $rentPayable->id,
                            'amount' => $adjData['amount'],
                            'description' => $adjData['description'] ?? null,
                            "files" => $adjData["files"] ?? [],
                            'expense_id' => $adjData['expense_id'] ?? null,
                            'repair_id' => $adjData['repair_id'] ?? null,
                        ]);
                    }
                }

                // Calculate total_amount
                $totalRentAmount = !empty($payableRentIds) ? Rent::whereIn('id', $payableRentIds)->sum('paid_amount') : 0;
                $totalAdjustment = $rentPayable->rent_adjustments()->sum('amount');

                $rentPayable->total_amount = $totalRentAmount + $totalAdjustment;
                $rentPayable->save();

                // Load relations
                $rentPayable->load(['payable_rents.rent', 'rent_adjustments', 'creator']);

                return response()->json($rentPayable, 201);
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
                $rentPayable = LandlordRentPayable::where([
                    'id' => $validated['id'],
                    'created_by' => $request->user()->id
                ])->firstOrFail();

                // Update main fields (excluding total_amount)
                $rentPayable->fill(array_diff_key($validated, ['total_amount' => '']));
                $rentPayable->save();

               // Create related rents
               LandlordPayableRent::where('landlord_rent_payable_id', $rentPayable->id)->delete();
                $payableRentIds = [];
                if (!empty($validated['payable_rents'])) {
                    foreach ($validated['payable_rents'] as $rentData) {
                        $rent = LandlordPayableRent::create([
                            'landlord_rent_payable_id' => $rentPayable->id,
                            'rent_id' => $rentData['rent_id']
                        ]);
                        $payableRentIds[] = $rent->rent_id;
                    }
                }

                RentAdjustment::where('landlord_rent_payable_id', $rentPayable->id)->delete();
                // Create related rent adjustments
                if (!empty($validated['rent_adjustments'])) {
                    foreach ($validated['rent_adjustments'] as $adjData) {
                        if (!empty($adjData["repair_id"])) {
                            $rent_adjustment_exists =    RentAdjustment::where([
                                "repair_id" => $adjData["repair_id"]
                            ])
                                ->whereNotIn("landlord_rent_payable_id", [$rentPayable->id])
                                ->first();
                            if (!$rent_adjustment_exists) {
                                $error =  [
                                    "message" => "The given data was invalid.",
                                    "errors" => ["rent_adjustments" => ["invalid repair item"]]
                                ];
                                throw new Exception(json_encode($error), 422);
                            }
                        }

                        if (!empty($adjData["expense_id"])) {
                            $rent_adjustment_exists =    RentAdjustment::where([
                                "expense_id" => $adjData["expense_id"]
                            ])
                                ->whereNotIn("landlord_rent_payable_id", [$rentPayable->id])
                                ->first();
                            if (!$rent_adjustment_exists) {
                                $error =  [
                                    "message" => "The given data was invalid.",
                                    "errors" => ["rent_adjustments" => ["invalid expense item"]]
                                ];
                                throw new Exception(json_encode($error), 422);
                            }
                        }

                        RentAdjustment::create([
                            'landlord_rent_payable_id' => $rentPayable->id,
                            'amount' => $adjData['amount'],
                            'description' => $adjData['description'] ?? null,
                            "files" => $adjData["files"] ?? [],
                            'expense_id' => $adjData['expense_id'] ?? null,
                            'repair_id' => $adjData['repair_id'] ?? null,
                        ]);
                    }
                }

                // Recalculate total_amount
                $totalRentAmount = 0;
                if (!empty($payableRentIds)) {
                    $totalRentAmount = Rent::whereIn('id', $payableRentIds)->sum('paid_amount');
                }

                $totalAdjustment = $rentPayable->rent_adjustments()->sum('amount');

                $rentPayable->total_amount = $totalRentAmount + $totalAdjustment;
                $rentPayable->save();

                $rentPayable->load(['payable_rents.rent', 'rent_adjustments', 'creator']);

                return response()->json($rentPayable, 200);
            });
        } catch (\Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
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
            $rentPayables = $query->orderBy('id', $orderBy)
                ->paginate($perPage);

            return response()->json($rentPayables, 200);
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
            $landlord_rent_payable->delete();

            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
}
