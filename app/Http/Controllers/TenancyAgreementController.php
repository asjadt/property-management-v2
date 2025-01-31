<?php

namespace App\Http\Controllers;

use App\Http\Requests\TenancyAgreementCreateRequest;
use App\Http\Requests\TenancyAgreementUpdateRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\Rent;
use App\Models\TenancyAgreement;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenancyAgreementController extends Controller
{
    use ErrorUtil, UserActivityUtil, BasicUtil;


    /**
     * @OA\Post(
     *      path="/v1.0/tenancy-agreement",
     *      operationId="createTenancyAgreement",
     *      tags={"property_management.property_agreement"},
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      summary="Store property agreement",
     *      description="This method is to store a new property agreement, replacing any existing agreement for the same property and landlord.",
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *         required={},
     *
     *         @OA\Property(property="property_id", type="number", example="1"),
     *         @OA\Property(property="agreed_rent", type="string", example="1000.00"),
     *         @OA\Property(property="security_deposit_hold", type="string", example="2000.00"),
     *         @OA\Property(property="tenant_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3}),
     *         @OA\Property(property="rent_payment_option", type="string", enum={"By_Cash", "By_Cheque", "Bank_Transfer"}, example="By_Cash"),
     *         @OA\Property(property="tenant_contact_duration", type="string", example="12 months"),
     *         @OA\Property(property="date_of_moving", type="string", format="date", example="2024-11-01"),
     *      *         @OA\Property(property="holder_reference_number", type="string", format="string", example="holder_reference_number"),
     *         @OA\Property(property="holder_entity_id", type="number", format="number", example="1"),
     *         @OA\Property(property="let_only_agreement_expired_date", type="string", format="date", example="2025-11-01", nullable=true),
     *         @OA\Property(property="tenant_contact_expired_date", type="string", format="date", example="2025-11-01", nullable=true),
     *         @OA\Property(property="rent_due_day", type="string", format="date", example="2024-11-05"),
     *         @OA\Property(property="no_of_occupants", type="string", example="3"),
     *         @OA\Property(property="tenant_contact_year_duration", type="string", example="tenant_contact_year_duration"),
     *         @OA\Property(property="renewal_fee", type="string", example="50.00"),
     *         @OA\Property(property="housing_act", type="string", example="Housing Act 1988"),
     *         @OA\Property(property="let_type", type="string", example="Standard Let"),
     *         @OA\Property(property="terms_and_conditions", type="string", example="Updated terms and conditions..."),
     *         @OA\Property(property="agency_name", type="string", example="XYZ Realty"),
     *         @OA\Property(property="landlord_name", type="string", example="John Doe"),
     *         @OA\Property(property="agency_witness_name", type="string", example="Jane Smith"),
     *         @OA\Property(property="tenant_witness_name", type="string", example="Mark Johnson"),
     *         @OA\Property(property="agency_witness_address", type="string", example="123 Agency St, City, Country"),
     *         @OA\Property(property="tenant_witness_address", type="string", example="456 Tenant Rd, City, Country"),
     *         @OA\Property(property="guarantor_name", type="string", example="Sarah Lee", nullable=true),
     *         @OA\Property(property="guarantor_address", type="string", example="789 Guarantor Ave, City, Country", nullable=true),
     *
     *       @OA\Property(property="tenant_sign_date", type="string", format="date", example="2024-11-01"),
     *       @OA\Property(property="agency_sign_date", type="string", format="date", example="2025-11-01")
     *
     *     )
     * ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              example={"message": "Tenancy agreement created successfully."}
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(
     *              example={"message": "Unauthenticated."}
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent(
     *              example={"message": "Validation failed."}
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              example={"message": "Forbidden."}
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(
     *              example={"message": "Bad request."}
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(
     *              example={"message": "Resource not found."}
     *          )
     *      )
     * )
     */


    public function createTenancyAgreement(TenancyAgreementCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "");
            return DB::transaction(function () use ($request) {

                $request_data = $request->validated();


    $start_date = Carbon::parse($request_data["date_of_moving"]);
     $end_date = Carbon::parse($request_data["tenant_contact_expired_date"]);
     $months_difference = $start_date->diffInMonths($end_date);
     $request_data["total_agreed_rent"] = $request_data["agreed_rent"] * $months_difference;

                $agreement = TenancyAgreement::create($request_data);
                $agreement->tenants()->sync($request_data["tenant_ids"]);

                return response($agreement, 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     * @OA\Put(
     *      path="/v1.0/tenancy-agreement",
     *      operationId="updateTenancyAgreement",
     *      tags={"property_management.property_agreement"},
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      summary="Update property agreement",
     *      description="This method updates an existing property agreement based on the provided data.",
     * @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *         required={},
     *         @OA\Property(property="id", type="string", example="1"),
     *         @OA\Property(property="agreed_rent", type="string", example="1000.00"),
     *         @OA\Property(property="security_deposit_hold", type="string", example="2000.00"),
     *         @OA\Property(property="tenant_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3}),
     *         @OA\Property(property="rent_payment_option", type="string", enum={"By_Cash", "By_Cheque", "Bank_Transfer"}, example="By_Cash"),
     *         @OA\Property(property="tenant_contact_duration", type="string", example="12 months"),
     *         @OA\Property(property="date_of_moving", type="string", format="date", example="2024-11-01"),
     *         @OA\Property(property="holder_reference_number", type="string", format="string", example="holder_reference_number"),
    *         @OA\Property(property="holder_entity_id", type="number", format="number", example="1"),
     *
     *
     *         @OA\Property(property="let_only_agreement_expired_date", type="string", format="date", example="2025-11-01", nullable=true),
     *         @OA\Property(property="tenant_contact_expired_date", type="string", format="date", example="2025-11-01", nullable=true),
     *         @OA\Property(property="rent_due_day", type="string", format="date", example="2024-11-05"),
     *         @OA\Property(property="no_of_occupants", type="string", example="3"),
     *         @OA\Property(property="tenant_contact_year_duration", type="string", example="tenant_contact_year_duration"),
     *         @OA\Property(property="renewal_fee", type="string", example="50.00"),
     *         @OA\Property(property="housing_act", type="string", example="Housing Act 1988"),
     *         @OA\Property(property="let_type", type="string", example="Standard Let"),
     *         @OA\Property(property="terms_and_conditions", type="string", example="Updated terms and conditions..."),
     *         @OA\Property(property="agency_name", type="string", example="XYZ Realty"),
     *         @OA\Property(property="landlord_name", type="string", example="John Doe"),
     *         @OA\Property(property="agency_witness_name", type="string", example="Jane Smith"),
     *         @OA\Property(property="tenant_witness_name", type="string", example="Mark Johnson"),
     *         @OA\Property(property="agency_witness_address", type="string", example="123 Agency St, City, Country"),
     *         @OA\Property(property="tenant_witness_address", type="string", example="456 Tenant Rd, City, Country"),
     *         @OA\Property(property="guarantor_name", type="string", example="Sarah Lee", nullable=true),
     *         @OA\Property(property="guarantor_address", type="string", example="789 Guarantor Ave, City, Country", nullable=true),
     *    *       @OA\Property(property="tenant_sign_date", type="string", format="date", example="2024-11-01"),
     *       @OA\Property(property="agency_sign_date", type="string", format="date", example="2025-11-01")
     *     )
     * ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *          ),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent(),
     *      ),
     * )
     */

    public function updateTenancyAgreement(TenancyAgreementUpdateRequest $request)
    {
        try {

            $this->storeActivity($request, "");
            return DB::transaction(function () use ($request) {
                $request_data = $request->validated();

                // Find the agreement, including soft-deleted records
                $agreement = TenancyAgreement::whereHas('property', function ($q) {
                        // Ensure the property is created by the authenticated user
                        $q->where('properties.created_by', auth()->user()->id);
                    })
                    ->where('id', $request_data['id'])
                    ->first();  // Returns null if not found

                // Check if agreement exists
                if (!$agreement) {
                    return response()->json(['message' => 'Agreement not found'], 404);
                }
                // Ensure agreement exists

                $start_date = Carbon::parse($request_data["date_of_moving"]);
                $end_date = Carbon::parse($request_data["tenant_contact_expired_date"]);
                $months_difference = $start_date->diffInMonths($end_date);
                $request_data["total_agreed_rent"] = $request_data["agreed_rent"] * $months_difference;
                // Fill the model with the mass-assignable attributes
                $agreement->fill($request_data);
                $agreement->save(); // Save the agreement

                // Handle tenant_ids separately and sync them with the agreement
                $agreement->tenants()->sync($request_data["tenant_ids"]);

                return response($agreement, 200);
            });
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }



    /**
     * @OA\Get(
     *      path="/v1.0/tenancy-agreements",
     *      operationId="getTenancyAgreements",
     *      tags={"property_management.property_agreement"},
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      summary="Get property agreements",
     *      description="This method retrieves the history of property agreements for a given property and landlord, including soft-deleted agreements.",
     * *      @OA\Parameter(
     *          name="year",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=1)
     *      ),
     * *      @OA\Parameter(
     *          name="month",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Parameter(
     *          name="tenant_ids",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Parameter(
     *          name="property_ids",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(),
     *      )
     * )
     */

    public function getTenancyAgreements(Request $request)
    {
        try {
            // Start building the query for history
            $query = TenancyAgreement::with([
                "tenants" => function ($query) {
                    $query->select(
                        "tenants.id",
                        "tenants.first_Name",
                        "tenants.last_Name"
                    );
                }
            ])

                ->whereHas('property', function ($q) {
                    // Ensure the property is created by the authenticated user
                    $q->where('properties.created_by', auth()->user()->id);
                })
                ->when($request->filled('tenant_ids'), function ($q) use ($request) {
                    // Filter by property_id if provided
                    $q->whereHas('tenants', function ($q) {
                        $tenant_ids = explode(',', request()->input('tenant_ids'));
                        // Ensure the property is created by the authenticated user
                        $q->whereIn('tenants.id', $tenant_ids);
                    });
                })
                ->when($request->filled('property_id'), function ($q) use ($request) {
                    // Filter by property_id if provided
                    $q->where('property_id', $request->property_id);
                })
                ->when($request->filled('property_ids'), function ($q) use ($request) {
                    // Filter by property_id if provided
                    $property_ids = explode(',', request()->input("property_ids"));
                    $q->whereIn('property_id', $property_ids);
                })
                ->when($request->filled('id'), function ($q) use ($request) {
                    // If specific ID is provided, return that record only
                    return $q->where('id', $request->input('id'))->first();
                }, function ($q) {
                    // Otherwise, fetch history (including soft-deleted agreements)
                    return $q
                        // ->withTrashed() // Include soft-deleted records
                        ->when(!empty($request->per_page), function ($q) {
                            // Paginate if per_page is provided
                            return $q->paginate(request()->per_page);
                        }, function ($q) {
                            // Otherwise, return all agreements
                            return $q->get();
                        });
                });

            return response()->json($query, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     * @OA\Get(
     *      path="/v2.0/tenancy-agreements",
     *      operationId="getTenancyAgreementsV2",
     *      tags={"property_management.property_agreement"},
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      summary="Get property agreements",
     *      description="This method retrieves the history of property agreements for a given property and landlord, including soft-deleted agreements.",
     * *      @OA\Parameter(
     *          name="year",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=1)
     *      ),
     * *      @OA\Parameter(
     *          name="month",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Parameter(
     *          name="tenant_ids",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Parameter(
     *          name="property_ids",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(),
     *      )
     * )
     */

    public function getTenancyAgreementsV2(Request $request)
    {
        try {
            // Start building the query for tenancy agreements
            $agreements = TenancyAgreement::with([
                "tenants" => function ($query) {
                    $query->select(
                        "tenants.id",
                        "tenants.first_Name",
                        "tenants.last_Name"
                    );
                }
            ])

                ->whereHas('property', function ($q) {
                    // Ensure the property is created by the authenticated user
                    $q->where('properties.created_by', auth()->user()->id);
                })
                ->when($request->filled('tenant_ids'), function ($q) use ($request) {
                    // Filter by property_id if provided
                    $q->whereHas('tenants', function ($q) {
                        $tenant_ids = explode(',', request()->input('tenant_ids'));
                        // Ensure the property is created by the authenticated user
                        $q->whereIn('tenants.id', $tenant_ids);
                    });
                })
                ->when($request->filled('property_id'), function ($q) use ($request) {
                    // Filter by property_id if provided
                    $q->where('tenancy_agreements.property_id', $request->property_id);
                })
                ->when($request->filled('property_ids'), function ($q) use ($request) {
                    // Filter by property_id if provided
                    $property_ids = explode(',', request()->input("property_ids"));
                    $q->whereIn('tenancy_agreements.property_id', $property_ids);
                })
                ->when($request->filled('id'), function ($q) use ($request) {
                    // If specific ID is provided, return that record only
                    return $q->where('id', $request->input('id'))->first();
                }, function ($q) {
                    // Otherwise, fetch history (including soft-deleted agreements)
                    return $q
                        // ->withTrashed() // Include soft-deleted records
                        ->when(!empty($request->per_page), function ($q) {
                            // Paginate if per_page is provided
                            return $q->paginate(request()->per_page);
                        }, function ($q) {
                            // Otherwise, return all agreements
                            return $q->get();
                        });
                });

            if ($agreements instanceof \Illuminate\Pagination\LengthAwarePaginator || $agreements instanceof \Illuminate\Support\Collection) {
                $agreementIds = $agreements->pluck('id')->all();
            } elseif ($agreements instanceof \App\Models\TenancyAgreement) {
                $agreementIds = [$agreements->id];
            } else {
                $agreementIds = [];
            }

            // Calculate rent highlights (total rent, total paid, total arrears, highest rent)

            $rentHighlights = TenancyAgreement::whereIn('id', $agreementIds)
            ->withSum('rents', 'paid_amount') // Sum of all paid amounts in related rents

            ->selectRaw(
                'SUM(tenancy_agreements.total_agreed_rent) as total_rent,
                 SUM(COALESCE(rents.paid_amount, 0)) as total_paid,
                 SUM(tenancy_agreements.total_agreed_rent - COALESCE(rents.paid_amount, 0)) as total_arrears,
                 MAX(tenancy_agreements.total_agreed_rent) as highest_rent'
            )
            ->first();


            return response()->json([
                'data' => $agreements,
                'rent_highlights' => $rentHighlights,
            ], 200);


        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }




    /**
     * @OA\Get(
     *      path="/v1.0/tenancy-agreements-with-rent",
     *      operationId="getTenancyAgreementsWithRent",
     *      tags={"property_management.property_agreement"},
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      summary="Get property agreements",
     *      description="This method retrieves the history of property agreements for a given property and landlord, including soft-deleted agreements.",
     * *      @OA\Parameter(
     *          name="year",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=1)
     *      ),
     * *      @OA\Parameter(
     *          name="month",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Parameter(
     *          name="tenant_ids",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Parameter(
     *          name="property_ids",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found",
     *          @OA\JsonContent(),
     *      )
     * )
     */

    public function getTenancyAgreementsWithRent(Request $request)
    {
        try {

            $year = $request->year;
            $month = $request->month;

            if (!request()->filled('year') || !request()->filled('month')) {

                return response()->json(
                    [
                        "message" => "year and month are required"
                    ],
                    404
                );
            }


            $tenancy_agreements = TenancyAgreement::with([
                "tenants" => function ($query) {
                    $query->select(
                        "tenants.id",
                        "tenants.first_Name",
                        "tenants.last_Name"
                    );
                }
            ])
                ->where(function ($query) use ($year, $month) {


                    // Create the start and end dates for the given month
                    $startDate = Carbon::createFromDate($year, $month, 1)->startOfDay();
                    $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth()->endOfDay();

                    $query->where(function ($subQuery) use ($startDate, $endDate) {
                        $subQuery->where('tenancy_agreements.date_of_moving', '<=', $endDate)
                            ->where('tenancy_agreements.tenant_contact_expired_date', '>=', $startDate);
                    })
                        ->whereDoesntHave("rent", function ($subQuery) use ($year, $month) {
                            $subQuery->where('rents.year', $year)
                                ->where('rents.month', $month);
                        });
                })

                ->whereHas('property', function ($q) {
                    // Ensure the property is created by the authenticated user
                    $q->where('properties.created_by', auth()->user()->id);
                })
                ->when($request->filled('tenant_ids'), function ($q) use ($request) {
                    // Filter by property_id if provided
                    $q->whereHas('tenants', function ($q) {
                        $tenant_ids = explode(',', request()->input('tenant_ids'));
                        // Ensure the property is created by the authenticated user
                        $q->whereIn('tenants.id', $tenant_ids);
                    });
                })
                ->when($request->filled('property_id'), function ($q) use ($request) {
                    // Filter by property_id if provided
                    $q->where('property_id', $request->property_id);
                })
                ->when($request->filled('property_ids'), function ($q) use ($request) {
                    // Filter by property_id if provided
                    $property_ids = explode(',', request()->input("property_ids"));
                    $q->whereIn('property_id', $property_ids);
                })
                ->get();



                $all_rents = Rent::with("tenancy_agreement.property", "tenancy_agreement.tenants")
                ->where('rents.created_by', auth()->user()->id)
                ->where('rents.year', request()->input('year'))
                ->where('rents.month', request()->input('month'))
                ->when(request()->filled("tenant_ids"), function ($query) {
                    return $query->whereHas("tenancy_agreement.tenants", function ($query) {
                        $tenant_ids = explode(',', request()->input("tenant_ids"));
                        $query->whereIn("tenants.id", $tenant_ids);
                    });
                })
                ->when(request()->filled("property_ids"), function ($query) {
                    return $query->whereHas("tenancy_agreement", function ($query) {
                        $property_ids = explode(',', request()->input("property_ids"));
                        $query->whereIn("tenancy_agreements.property_id", $property_ids);
                    });
                })
                ->when(request()->filled("start_payment_date"), function ($query) {
                    return $query->whereDate(
                        'rents.payment_date',
                        ">=",
                        request()->input("start_payment_date")
                    );
                })

                ->when(request()->filled("end_payment_date"), function ($query) {
                    return $query->whereDate('rents.payment_date', "<=", request()->input("end_payment_date"));
                })
                ->when(request()->filled("payment_status"), function ($query) {
                    return $query->where(
                        'rents.payment_status',
                        request()->input("payment_status")
                    );
                })
                ->when(request()->filled("search_key"), function ($query) {
                    return $query->where(function ($query) {
                        $term = request()->input("search_key");
                        $query

                            ->orWhere("rents.payment_status", "like", "%" . $term . "%");
                    });
                })
                ->when(request()->filled("start_date"), function ($query) {
                    return $query->whereDate('rents.created_at', ">=", request()->input("start_date"));
                })
                ->when(request()->filled("end_date"), function ($query) {
                    return $query->whereDate('rents.created_at', "<=", request()->input("end_date"));
                })

                ->get();


            $paid_rents = $all_rents->filter(fn($rent) => $rent->payment_status === 'paid')->toArray();
            $arrears_rents = $all_rents->filter(fn($rent) => $rent->payment_status === 'arrears')->toArray();
            $overpaid_rents = $all_rents->filter(fn($rent) => $rent->payment_status === 'overpaid')->toArray();


            foreach ($tenancy_agreements as $tenancy_agreement) {
                // Calculate total arrears
                $agreement_rents = Rent::where([
                    "tenancy_agreement_id" => $tenancy_agreement->id
                ])
                    ->where(function ($query) use ($year, $month) {
                        $query->where('year', '<', ["year"])
                            ->orWhere(function ($query) use ($year, $month) {
                                $query->where('year', $year)
                                    ->where('month', '<=', $month);
                            });
                    })
                    ->orderBy('year')
                    ->orderBy('month')
                    ->get();

                $tenancy_agreement["arrear"] =   $this->processArrears($tenancy_agreement,$agreement_rents,false);
            }


            $responseData = [
                "selectable_tenancy_agreements" => $tenancy_agreements,
                "paid_rents" => $paid_rents,
                "arrears_rents" => $arrears_rents,
                "overpaid_rents" => $overpaid_rents
            ];

            return response()->json($responseData, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }






    /**
     * @OA\Delete(
     *      path="/v1.0/tenancy-agreements/{agreement_id}",
     *      operationId="deleteTenancyAgreement",
     *      tags={"property_management.property_agreement"},
     *       security={
     *           {"bearerAuth": {}},
     *           {"pin": {}}
     *       },
     *      summary="Delete a document from a property",
     *      description="This method deletes a document associated with a specific property",
     *      @OA\Parameter(
     *          name="agreement_id",
     *          in="path",
     *          required=true,
     *          description="agreement ID",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Document deleted successfully",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Property or Document Not Found",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @OA\JsonContent(),
     *      )
     * )
     */

    public function deleteTenancyAgreement($agreement_id)
    {
        try {

            $business = Business::where([
                "owner_id" => request()->user()->id
            ])->first();

            if (!$business) {
                return response()->json([
                    "message" => "you don't have a valid business"
                ], 401);
            }

            if (!($business->pin == request()->header("pin"))) {
                return response()->json([
                    "message" => "invalid pin"
                ], 401);
            }

            // Find the property
            $property_agreement = TenancyAgreement::whereHas('property', function ($q) {
                    // Ensure the property is created by the authenticated user
                    $q->where('properties.created_by', auth()->user()->id);
                })
                ->where([
                    "id" => $agreement_id
                ])
                ->first();
            if (!$property_agreement) {
                return response()->json([
                    "message" => "no agreement found"
                ], 404);
            }

            // Delete the document
            $property_agreement->delete();


            return response()->json(['message' => 'agreement deleted successfully.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred.'], 500);
        }
    }
}
