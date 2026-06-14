<?php





namespace App\Http\Controllers;

use App\Http\Requests\RentCreateRequest;
use App\Http\Requests\RentUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Rent;
use App\Models\TenancyAgreement;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentController extends Controller
{

    use ErrorUtil, UserActivityUtil, BasicUtil;


    /**
     *
     * @OA\Post(
     * path="/v1.0/rents",
     * operationId="createRent",
     * tags={"rents"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to store rents",
     * description="This method is to store rents",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="tenancy_agreement_id", type="string", format="string", example="tenancy_agreement_id"),
     * @OA\Property(property="payment_date", type="string", format="string", example="payment_date"),
     *      *      *  * @OA\Property(property="rent_taken_by", type="string", format="string", example="rent_taken_by"),
     *    *  * @OA\Property(property="remarks", type="string", format="string", example="remarks"),
     * @OA\Property(property="payment_status", type="string", format="string", example="payment_status"),
     * @OA\Property(property="rent_amount", type="string", format="string", example="rent_amount"),
     *    *      * @OA\Property(property="rent_reference", type="string", format="string", example="rent_reference"),
     *      * @OA\Property(property="payment_method", type="string", format="string", example="payment_method"),
     * @OA\Property(property="paid_amount", type="string", format="string", example="paid_amount"),
     * @OA\Property(property="month", type="string", format="string", example="month"),
     * @OA\Property(property="year", type="string", format="string", example="year"),
     *
     *
     *
     * ),
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function createRent(RentCreateRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $request_data = $request->validated();




            $request_data["rent_reference"] = $this->rentReference();

            $request_data["created_by"] = auth()->user()->id;


            $rent_exists = Rent::where([
                "tenancy_agreement_id" => $request_data["tenancy_agreement_id"],
                'month' => $request_data["month"],
                'year' => $request_data["year"],
            ])
                ->exists();

            if ($rent_exists && $request_data["month"] != now()->month && $request_data["year"] != now()->year) {
                return response()->json([
                    "message" => "A rent record exists, but not for the current month and year."
                ], 409);
            }


            $request_data["payment_status"] = "pending";
            $request_data["arrear"] = 0;
            // Create the rent record
            $rent = Rent::create($request_data);


            $agreement = TenancyAgreement::where([
                "id" => $request_data["tenancy_agreement_id"]
            ])

                ->first();
            if (empty($agreement)) {
                throw new Exception("something went wrong", 500);
            }

            $all_rents = Rent::where([
                "tenancy_agreement_id" => $agreement->id
            ])
                ->orderBy('year')
                ->orderBy('month')
                ->orderBy('id')
                ->get();

            $this->processArrears($agreement, $all_rents, true);



            DB::commit();
            return response($rent, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Put(
     * path="/v1.0/rents",
     * operationId="updateRent",
     * tags={"rents"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to update rents ",
     * description="This method is to update rents ",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="tenancy_agreement_id", type="string", format="string", example="tenancy_agreement_id"),
     * @OA\Property(property="payment_date", type="string", format="string", example="payment_date"),
     *  * @OA\Property(property="remarks", type="string", format="string", example="remarks"),
     *      *  * @OA\Property(property="rent_taken_by", type="string", format="string", example="rent_taken_by"),
     * @OA\Property(property="payment_status", type="string", format="string", example="payment_status"),
     * @OA\Property(property="rent_amount", type="string", format="string", example="rent_amount"),
     *
     *      * @OA\Property(property="rent_reference", type="string", format="string", example="rent_reference"),
     *      * @OA\Property(property="payment_method", type="string", format="string", example="payment_method"),

     * @OA\Property(property="paid_amount", type="string", format="string", example="paid_amount"),

     * @OA\Property(property="month", type="string", format="string", example="month"),
     * @OA\Property(property="year", type="string", format="string", example="year"),
     *
     * ),
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function updateRent(RentUpdateRequest $request)
    {
        DB::beginTransaction();
        try {

            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $request_data = $request->validated();

            // $reference_no_exists =  Rent::where(
            //     [
            //         'rent_reference' => $request_data['rent_reference'],
            //         "created_by" => $request->user()->id
            //     ]
            // )
            //     ->whereNotIn('id', [$request_data["id"]])->exists();
            // if ($reference_no_exists) {
            //     $error =  [
            //         "message" => "The given data was invalid.",
            //         "errors" => ["rent_reference" => ["The rent reference has already been taken."]]
            //     ];
            //     throw new Exception(json_encode($error), 422);
            // }



            $rent = Rent::where(
                [
                    "id" => $request_data["id"],
                ]
            )->first();

            if (empty($rent)) {
                return response()->json([
                    "message" => "something went wrong."
                ], 500);
            }

            if ($rent) {
                $request_data["payment_status"] = "pending";
                $request_data["arrear"] = 0;

                $rent->fill($request_data);
                $rent->save();
            }




            $agreement = TenancyAgreement::where([
                "id" => $request_data["tenancy_agreement_id"]
            ])

                ->first();

            if (empty($agreement)) {
                throw new Exception("something went wrong", 500);
            }


            $all_rents = Rent::where([
                "tenancy_agreement_id" => $agreement->id
            ])
                ->orderBy('year')
                ->orderBy('month')
                ->orderBy('id')
                ->get();

            $this->processArrears($agreement, $all_rents, true);


            DB::commit();
            return response($rent, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     * path="/v1.0/rents",
     * operationId="getRents",
     * tags={"rents"},
     * security={
     * {"bearerAuth": {}}
     * },

     * @OA\Parameter(
     * name="start_payment_date",
     * in="query",
     * description="start_payment_date",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="end_payment_date",
     * in="query",
     * description="end_payment_date",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="payment_status",
     * in="query",
     * description="payment_status",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="per_page",
     * required=false,
     * example=""
     * ),

     * @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=false,
     * example="ASC"
     * ),
     * * @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=false,
     * example=""
     * ),
     *    * @OA\Parameter(
     * name="tenant_ids",
     * in="query",
     * description="id",
     * required=false,
     * example=""
     * ),
     * *    * @OA\Parameter(
     * name="property_ids",
     * in="query",
     * description="id",
     * required=false,
     * example=""
     * ),
     *  * *    * @OA\Parameter(
     * name="rent_reference",
     * in="query",
     * description="rent_reference",
     * required=false,
     * example=""
     * ),
     *
     *
     * summary="This method is to get rents ",
     * description="This method is to get rents ",
     *
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function getRents(Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $query = Rent::withCount([
                "landlord_payables"
            ])
            ->with("tenancy_agreement.property", "tenancy_agreement.tenants")
            ->filters();

            $rents = $this->retrieveData($query, "month", "rents");

            return response()->json($rents, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     * path="/v2.0/rents",
     * operationId="getRentsV2",
     * tags={"rents"},
     * security={
     * {"bearerAuth": {}}
     * },

     * @OA\Parameter(
     * name="start_payment_date",
     * in="query",
     * description="start_payment_date",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="end_payment_date",
     * in="query",
     * description="end_payment_date",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="payment_status",
     * in="query",
     * description="payment_status",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="per_page",
     * required=false,
     * example=""
     * ),

     * @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=false,
     * example="ASC"
     * ),
     * * @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=false,
     * example=""
     * ),
     *    * @OA\Parameter(
     * name="tenant_ids",
     * in="query",
     * description="id",
     * required=false,
     * example=""
     * ),
     * *    * @OA\Parameter(
     * name="property_ids",
     * in="query",
     * description="id",
     * required=false,
     * example=""
     * ),
     *
     * summary="This method is to get rents ",
     * description="This method is to get rents ",
     *
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function getRentsV2(Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $query = Rent::withCount("landlord_payables")->with(
                
              [ "tenancy_agreement.property", "tenancy_agreement.tenants", 
            "landlord_rent_payables" => function ($q) {
                $q->select(
                    "landlord_rent_payables.id",
                    "landlord_rent_payables.generated_id");
            }

            ]
            )
                ->filters();

            $rents = $this->retrieveData($query, "month", "rents");


          
        
            $highlights_query = Rent::where('rents.created_by', auth()->user()->id)
                ->when(request()->filled("tenant_ids"), function ($query) {
                    $tenant_ids = explode(',', request()->input("tenant_ids"));
                    return $query->whereHas("tenancy_agreement.tenants", function ($query) use ($tenant_ids) {
                        $query->whereIn("tenants.id", $tenant_ids);
                    });
                })
                ->when(request()->filled("landlord_id"), function ($query) {
                    $landlord_id = explode(',', request()->input("landlord_id"));
                    return $query->whereHas("tenancy_agreement.property.property_landlords", function ($query) use ($landlord_id) {
                        $query->whereIn("landlords.id", $landlord_id);
                    });
                })
                ->when(request()->filled("landlord_ids"), function ($query) {
                    $landlord_ids = explode(',', request()->input("landlord_ids"));
                    return $query->whereHas("tenancy_agreement.property.property_landlords", function ($query) use ($landlord_ids) {
                        $query->whereIn("landlords.id", $landlord_ids);
                    });
                })
                ->when(request()->filled("property_ids"), function ($query) {
                    $property_ids = explode(',', request()->input("property_ids"));
                    return $query->whereHas("tenancy_agreement", function ($query) use ($property_ids) {
                        $query->whereIn("tenancy_agreements.property_id", $property_ids);
                    });
                })
                ->when(request()->filled("rent_reference"), function ($query) {
                    return $query->where('rents.rent_reference', "like", "%" . request()->input("rent_reference") . "%");
                })
                ->when(request()->filled("start_payment_date"), function ($query) {
                    return $query->whereDate('rents.payment_date', ">=", request()->input("start_payment_date"));
                })
                ->when(request()->filled("end_payment_date"), function ($query) {
                    return $query->whereDate('rents.payment_date', "<=", request()->input("end_payment_date"));
                })
                ->when(request()->filled("start_date"), function ($query) {
                    return $query->whereDate('rents.created_at', ">=", request()->input("start_date"));
                })
                ->when(request()->filled("end_date"), function ($query) {
                    return $query->whereDate('rents.created_at', "<=", request()->input("end_date"));
                });

            $total_rent = (double) (clone $highlights_query)->sum('rent_amount');
            $total_paid = (double) (clone $highlights_query)->sum('paid_amount');
            $total_arrears = $total_rent - $total_paid;
            $highest_rent = (double) (clone $highlights_query)->max('rent_amount');

            $data_highlights = [
                "total_rent" => $total_rent,
                "highest_rent" => $highest_rent,
                "total_paid" => $total_paid,
                "total_arrears" => $total_arrears
            ];



            // Add data highlights to the response
            $response = [
                'data' => $rents,
                'data_highlights' => $data_highlights,
            ];

            return response()->json($response, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     * path="/v3.0/rents",
     * operationId="getRentsV3",
     * tags={"rents"},
     * security={
     * {"bearerAuth": {}}
     * },

     * @OA\Parameter(
     * name="start_payment_date",
     * in="query",
     * description="start_payment_date",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="end_payment_date",
     * in="query",
     * description="end_payment_date",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="payment_status",
     * in="query",
     * description="payment_status",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="per_page",
     * required=false,
     * example=""
     * ),

     * @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=false,
     * example="ASC"
     * ),
     * * @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=false,
     * example=""
     * ),
     *    * @OA\Parameter(
     * name="tenant_ids",
     * in="query",
     * description="id",
     * required=false,
     * example=""
     * ),
     * *    * @OA\Parameter(
     * name="property_ids",
     * in="query",
     * description="id",
     * required=false,
     * example=""
     * ),
     *  * *    * @OA\Parameter(
     * name="rent_reference",
     * in="query",
     * description="rent_reference",
     * required=false,
     * example=""
     * ),
     *
     *
     * summary="This method is to get rents ",
     * description="This method is to get rents ",
     *
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function getRentsV3(Request $request)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $query = Rent::with("tenancy_agreement.property", "tenancy_agreement.tenants")
                ->filters();

            $rents = $this->retrieveData($query, "month", "rents");

            $processed_rents = transform_mixed($rents, function ($rent) {
                $rent["landlords"] = $rent->landlords;
                return $rent;
            });

            return response()->json($processed_rents, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Delete(
     * path="/v1.0/rents/{ids}",
     * operationId="deleteRentsByIds",
     * tags={"rents"},
     * security={
     * {"bearerAuth": {}}
     * },
     * @OA\Parameter(
     * name="ids",
     * in="path",
     * description="ids",
     * required=true,
     * example="1,2,3"
     * ),
     * summary="This method is to delete rent by id",
     * description="This method is to delete rent by id",
     *

     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function deleteRentsByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");




            $rent =  Rent::where([
                "id" => $ids
            ])
                ->first();

            if (!$rent) {
                return response()->json(["message" => "Rent not found"], 404);
            }

            $tenancy_agreement_id = $rent->tenancy_agreement_id;

            
            $this->adjust_rent_and_expense_on_rent_delete($rent->id);


            $rent->delete();

            $agreement = TenancyAgreement::where([
                "id" => $tenancy_agreement_id
            ])

                ->first();

            if (empty($agreement)) {
                throw new Exception("something went wrong", 500);
            }


            $all_rents = Rent::where([
                "tenancy_agreement_id" => $agreement->id
            ])
                ->orderBy('year')
                ->orderBy('month')
                ->orderBy('id')
                ->get();

            $this->processArrears($agreement, $all_rents, true);


            return response()->json(["message" => "data deleted sussfully"], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    public function rentReference()
    {
        $current_number = 1; // Start from 0001

        do {
            $rent_reference = str_pad($current_number, 4, '0', STR_PAD_LEFT);
            $current_number++; // Increment the current number for the next iteration
        } while (
            Rent::where([
                'rent_reference' => $rent_reference,
                'created_by' => auth()->user()->id
            ])->exists()
        );

        return $rent_reference;
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/rents/generate/rent-reference",
     *      operationId="generateRentReference",
     *      tags={"rents"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to generate rent reference",
     *      description="This method is to generate rent reference",
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
    public function generateRentReference(Request $request)
    {
        try {
            $this->storeActivity($request, "");


            $rent_reference = $this->rentReference();


            return response()->json(["rent_reference" => $rent_reference], 200);
        } catch (Exception $e) {
 
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/rents/validate/rent-reference/{rent_reference}",
     *      operationId="validateRentReference",
     *      tags={"rents"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *              @OA\Parameter(
     *         name="rent_reference",
     *         in="path",
     *         description="rent_reference",
     *         required=true,
     *  example="1"
     *      ),

     *      summary="This method is to validate rent number",
     *      description="This method is to validate rent number",
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
    public function validateRentReference($rent_reference, Request $request)
    {
        try {
            $this->storeActivity($request, "");

            $rent_reference_exists =  Rent::where(
                [
                    'rent_reference' => $rent_reference,
                    "created_by" => $request->user()->id
                ]
            )->exists();


            return response()->json(["rent_reference_exists" => $rent_reference_exists], 200);
        } catch (Exception $e) {
      
            return $this->sendError($e, 500, $request);
        }
    }
}
