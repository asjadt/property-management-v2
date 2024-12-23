<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyAgreementCreateRequest;
use App\Http\Requests\PropertyAgreementUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\PropertyAgreement;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyAgreementController extends Controller
{
    use ErrorUtil, UserActivityUtil;


    /**
     * @OA\Post(
     *      path="/v1.0/property-agreement",
     *      operationId="createPropertyAgreement",
     *      tags={"property_management.property_agreement"},
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      summary="Store property agreement",
     *      description="This method is to store a new property agreement, replacing any existing agreement for the same property and landlord.",
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={},
     *            @OA\Property(property="landlord_id", type="integer", example=1),
     *            @OA\Property(property="property_id", type="integer", example=1),
     *            @OA\Property(property="start_date", type="string", format="date", example="2024-11-01"),
     *            @OA\Property(property="end_date", type="string", format="date", example="2025-11-01"),
     *            @OA\Property(property="payment_arrangement", type="string", enum={"By_Cash", "By_Cheque", "Bank_Transfer"}, example="By_Cash"),
     *            @OA\Property(property="cheque_payable_to", type="string", example="John Doe"),
     *            @OA\Property(property="agent_commission", type="number", format="float", example=200.00),
     *            @OA\Property(property="management_fee", type="number", format="float", example=50.00, nullable=true),
     *            @OA\Property(property="inventory_charges", type="number", format="float", example=100.00, nullable=true),
     *            @OA\Property(property="terms_conditions", type="string", example="The terms and conditions of the agreement are as follows..."),
     *            @OA\Property(property="legal_representative", type="string", example="legal_representative"),
     *            @OA\Property(property="min_price", type="string", example="10.5"),
     *            @OA\Property(property="max_price", type="string", example="11.5"),
     *            @OA\Property(property="agency_type", type="string", example="agency_type"),
     * *            @OA\Property(property="type", type="string", example="let_property")
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="landlord_id", type="integer", example=1),
     *              @OA\Property(property="property_id", type="integer", example=1),
     *              @OA\Property(property="start_date", type="string", format="date", example="2024-11-01"),
     *              @OA\Property(property="end_date", type="string", format="date", example="2025-11-01"),
     *              @OA\Property(property="payment_arrangement", type="string", enum={"By_Cash", "By_Cheque", "Bank_Transfer"}, example="By_Cash"),
     *              @OA\Property(property="cheque_payable_to", type="string", example="John Doe"),
     *              @OA\Property(property="agent_commission", type="number", format="float", example=200.00),
     *              @OA\Property(property="management_fee", type="number", format="float", example=50.00, nullable=true),
     *              @OA\Property(property="inventory_charges", type="number", format="float", example=100.00, nullable=true),
     *              @OA\Property(property="terms_conditions", type="string", example="The terms and conditions of the agreement are as follows..."),
     *      *            @OA\Property(property="legal_representative", type="string", example="legal_representative"),
     *            @OA\Property(property="min_price", type="string", example="10.5"),
     *            @OA\Property(property="max_price", type="string", example="11.5"),
     *            @OA\Property(property="agency_type", type="string", example="agency_type"),
     * *            @OA\Property(property="type", type="string", example="let_property"),
     *              @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-01T12:00:00Z"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-01T12:00:00Z"),
     *          ),
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

    public function createPropertyAgreement(PropertyAgreementCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "");
            return DB::transaction(function () use ($request) {
                $request_data = $request->validated();
                $agreement = PropertyAgreement::create($request_data);
                return response($agreement, 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     * @OA\Put(
     *      path="/v1.0/property-agreement",
     *      operationId="updatePropertyAgreement",
     *      tags={"property_management.property_agreement"},
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      summary="Update property agreement",
     *      description="This method updates an existing property agreement based on the provided data.",
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={},
     *            @OA\Property(property="id", type="integer", example=1),
     *            @OA\Property(property="landlord_id", type="integer", example=1),
     *            @OA\Property(property="property_id", type="integer", example=1),
     *            @OA\Property(property="start_date", type="string", format="date", example="2024-11-01"),
     *            @OA\Property(property="end_date", type="string", format="date", example="2025-11-01"),
     *            @OA\Property(property="payment_arrangement", type="string", enum={"By_Cash", "By_Cheque", "Bank_Transfer"}, example="By_Cash"),
     *            @OA\Property(property="cheque_payable_to", type="string", example="John Doe"),
     *            @OA\Property(property="agent_commission", type="number", format="float", example=200.00),
     *            @OA\Property(property="management_fee", type="number", format="float", example=50.00, nullable=true),
     *            @OA\Property(property="inventory_charges", type="number", format="float", example=100.00, nullable=true),
     *            @OA\Property(property="terms_conditions", type="string", example="Updated terms and conditions..."),
     *      *            @OA\Property(property="legal_representative", type="string", example="legal_representative"),
     *            @OA\Property(property="min_price", type="string", example="10.5"),
     *            @OA\Property(property="max_price", type="string", example="11.5"),
     *            @OA\Property(property="agency_type", type="string", example="agency_type"),
     * *            @OA\Property(property="type", type="string", example="let_property")
     *         ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="landlord_id", type="integer", example=1),
     *              @OA\Property(property="property_id", type="integer", example=1),
     *              @OA\Property(property="start_date", type="string", format="date", example="2024-11-01"),
     *              @OA\Property(property="end_date", type="string", format="date", example="2025-11-01"),
     *              @OA\Property(property="payment_arrangement", type="string", example="By_Cash"),
     *              @OA\Property(property="cheque_payable_to", type="string", example="John Doe"),
     *              @OA\Property(property="agent_commission", type="number", format="float", example=200.00),
     *              @OA\Property(property="management_fee", type="number", format="float", example=50.00),
     *              @OA\Property(property="inventory_charges", type="number", format="float", example=100.00),
     *              @OA\Property(property="terms_conditions", type="string", example="Updated terms and conditions..."),
     *      *            @OA\Property(property="legal_representative", type="string", example="legal_representative"),
     *            @OA\Property(property="min_price", type="string", example="10.5"),
     *            @OA\Property(property="max_price", type="string", example="11.5"),
     *            @OA\Property(property="agency_type", type="string", example="agency_type"),
     * *            @OA\Property(property="type", type="string", example="let_property"),
     *              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-01T12:00:00Z"),
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

    public function updatePropertyAgreement(PropertyAgreementUpdateRequest $request)
    {
        try {
            $this->storeActivity($request, "");

            return DB::transaction(function () use ($request) {
                $request_data = $request->validated();

                // Find the agreement, including soft-deleted records
                $agreement = PropertyAgreement::
                whereHas('property', function ($q) {
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

                $agreement->fill($request_data); // Use fill() to update attributes
                $agreement->save();  // Save changes to the database

                return response($agreement, 200);
            });
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }





    /**
     * @OA\Get(
     *      path="/v1.0/property-agreements",
     *      operationId="getPropertyAgreements",
     *      tags={"property_management.property_agreement"},
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      summary="Get property agreements",
     *      description="This method retrieves the history of property agreements for a given property and landlord, including soft-deleted agreements.",
     *      @OA\Parameter(
     *          name="landlord_id",
     *          in="query",
     *          required=false,
     *          @OA\Schema(type="integer", example=1)
     *      ),
     *      @OA\Parameter(
     *          name="property_id",
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

    public function getPropertyAgreements(Request $request)
    {
        try {
            // Start building the query for history
            $query = PropertyAgreement::whereHas('property', function ($q) {
                // Ensure the property is created by the authenticated user
                $q->where('properties.created_by', auth()->user()->id);
            })
                ->when($request->filled('landlord_id'), function ($q) use ($request) {
                    // Filter by landlord_id if provided
                    $q->where('landlord_id', $request->landlord_id);
                })
                ->when($request->filled('property_id'), function ($q) use ($request) {
                    // Filter by property_id if provided
                    $q->where('property_id', $request->property_id);
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
     * @OA\Delete(
     *      path="/v1.0/property-agreements/{agreement_id}",
     *      operationId="deletePropertyAgreement",
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
     *          description="Property ID",
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

    public function deletePropertyAgreement($agreement_id)
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
            $property_agreement = PropertyAgreement::
            whereHas('property', function ($q) {
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
