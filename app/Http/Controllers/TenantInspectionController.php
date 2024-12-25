<?php

namespace App\Http\Controllers;


use App\Http\Requests\TenantInspectionCreateRequest;
use App\Http\Requests\TenantInspectionUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;

use App\Models\TenantInspection;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantInspectionController extends Controller
{
    use ErrorUtil, UserActivityUtil;


     /**
     * @OA\Post(
     *      path="/v1.0/tenant-inspections",
     *      operationId="createTenantInspection",
     *      tags={"property_management.tenant_inspections"},
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
*     @OA\Property(property="tenant_id", type="number", example="1"),
 *     @OA\Property(property="address_line_1", type="string", example="Dhaka"),
 *     @OA\Property(property="inspected_by", type="string", example="John Doe"),
 *     @OA\Property(property="phone", type="string", example="+8801234567890"),
 *     @OA\Property(property="date", type="string", format="date", example="2024-12-25"),
 *     @OA\Property(property="garden", type="string", example="Yes"),
 *     @OA\Property(property="entrance", type="string", example="Front door"),
 *     @OA\Property(property="exterior_paintwork", type="string", example="Good"),
 *     @OA\Property(property="windows_outside", type="string", example="Clean"),
 *     @OA\Property(property="kitchen_floor", type="string", example="Tiled"),
 *     @OA\Property(property="oven", type="string", example="Functional"),
 *     @OA\Property(property="sink", type="string", example="Clean"),
 *     @OA\Property(property="lounge", type="string", example="Spacious"),
 *     @OA\Property(property="downstairs_carpet", type="string", example="Clean"),
 *     @OA\Property(property="upstairs_carpet", type="string", example="Worn"),
 *     @OA\Property(property="window_1", type="string", example="Double glazed"),
 *     @OA\Property(property="window_2", type="string", example="Single glazed"),
 *     @OA\Property(property="window_3", type="string", example="Cracked"),
 *     @OA\Property(property="window_4", type="string", example="Clean"),
 *     @OA\Property(property="windows_inside", type="string", example="Clean"),
 *     @OA\Property(property="wc", type="string", example="Functional"),
 *     @OA\Property(property="shower", type="string", example="Working"),
 *     @OA\Property(property="bath", type="string", example="Clean"),
 *     @OA\Property(property="hand_basin", type="string", example="Clean"),
 *     @OA\Property(property="smoke_detective", type="string", example="Installed"),
 *     @OA\Property(property="general_paintwork", type="string", example="Good"),
 *     @OA\Property(property="carbon_monoxide", type="string", example="Detector Installed"),
 *     @OA\Property(property="overall_cleanliness", type="string", example="Very Clean"),
 *     @OA\Property(property="comments", type="string", example="All items in good condition.")
 *
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


    public function createTenantInspection(TenantInspectionCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "");
            return DB::transaction(function () use ($request) {

                $request_data = $request->validated();

               $request_data["created_by"] = auth()->user()->id;
               $inspection = TenantInspection::create($request_data);

                return response($inspection, 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     * @OA\Put(
     *      path="/v1.0/tenant-inspections",
     *      operationId="updateTenantInspection",
     *      tags={"property_management.tenant_inspections"},
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
 *     @OA\Property(property="tenant_id", type="number", example="1"),
 *     @OA\Property(property="address_line_1", type="string", example="Dhaka"),
 *     @OA\Property(property="inspected_by", type="string", example="John Doe"),
 *     @OA\Property(property="phone", type="string", example="+8801234567890"),
 *     @OA\Property(property="date", type="string", format="date", example="2024-12-25"),
 *     @OA\Property(property="garden", type="string", example="Yes"),
 *     @OA\Property(property="entrance", type="string", example="Front door"),
 *     @OA\Property(property="exterior_paintwork", type="string", example="Good"),
 *     @OA\Property(property="windows_outside", type="string", example="Clean"),
 *     @OA\Property(property="kitchen_floor", type="string", example="Tiled"),
 *     @OA\Property(property="oven", type="string", example="Functional"),
 *     @OA\Property(property="sink", type="string", example="Clean"),
 *     @OA\Property(property="lounge", type="string", example="Spacious"),
 *     @OA\Property(property="downstairs_carpet", type="string", example="Clean"),
 *     @OA\Property(property="upstairs_carpet", type="string", example="Worn"),
 *     @OA\Property(property="window_1", type="string", example="Double glazed"),
 *     @OA\Property(property="window_2", type="string", example="Single glazed"),
 *     @OA\Property(property="window_3", type="string", example="Cracked"),
 *     @OA\Property(property="window_4", type="string", example="Clean"),
 *     @OA\Property(property="windows_inside", type="string", example="Clean"),
 *     @OA\Property(property="wc", type="string", example="Functional"),
 *     @OA\Property(property="shower", type="string", example="Working"),
 *     @OA\Property(property="bath", type="string", example="Clean"),
 *     @OA\Property(property="hand_basin", type="string", example="Clean"),
 *     @OA\Property(property="smoke_detective", type="string", example="Installed"),
 *     @OA\Property(property="general_paintwork", type="string", example="Good"),
 *     @OA\Property(property="carbon_monoxide", type="string", example="Detector Installed"),
 *     @OA\Property(property="overall_cleanliness", type="string", example="Very Clean"),
 *     @OA\Property(property="comments", type="string", example="All items in good condition.")
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

    public function updateTenantInspection(TenantInspectionUpdateRequest $request)
    {
        try {

            $this->storeActivity($request, "");
            return DB::transaction(function () use ($request) {
                $request_data = $request->validated();

                // Find the agreement, including soft-deleted records
                $inspection = TenantInspection::
                  where("created_by",auth()->user()->id)
                  ->where('id', $request_data['id'])
                  ->first();  // Returns null if not found

                // Check if agreement exists
                if (!$inspection) {
                    return response()->json(['message' => 'Inspection not found'], 404);
                }



                // Fill the model with the mass-assignable attributes
                $inspection->fill(
                    collect($request_data)->only([
                    'address_line_1',
                    'inspected_by',
                    'phone',
                    'date',
                    'garden',
                    'entrance',
                    'exterior_paintwork',
                    'windows_outside',
                    'kitchen_floor',
                    'oven',
                    'sink',
                    'lounge',
                    'downstairs_carpet',
                    'upstairs_carpet',
                    'window_1',
                    'window_2',
                    'window_3',
                    'window_4',
                    'windows_inside',
                    'wc',
                    'shower',
                    'bath',
                    'hand_basin',
                    'smoke_detective',
                    'general_paintwork',
                    'carbon_monoxide',
                    'overall_cleanliness',
                    'comments'
                ])
                ->toArray());
                $inspection->save(); // Save the agreement


                return response($inspection, 200);
            });
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }





    /**
     * @OA\Get(
     *      path="/v1.0/tenant-inspections",
     *      operationId="getTenantInspections",
     *      tags={"property_management.tenant_inspections"},
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      summary="Get property agreements",
     *      description="This method retrieves the history of property agreements for a given property and landlord, including soft-deleted agreements.",
     *      @OA\Parameter(
     *          name="tenant_ids",
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

    public function getTenantInspections(Request $request)
    {
        try {
            // Start building the query for history
            $query = TenantInspection::where('tenant_inspections.created_by', auth()->user()->id)
            ->when(request()->filled('tenant_ids'), function ($q)  {
                $tenant_ids = explode(',', request()->input('tenant_ids'));
                $q->whereIn('tenant_inspections.tenant_id', $tenant_ids);
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
     *      path="/v1.0/tenant-inspections/{id}",
     *      operationId="deleteTenantInspection",
     *      tags={"property_management.tenant_inspections"},
     *       security={
     *           {"bearerAuth": {}},
     *           {"pin": {}}
     *       },
     *      summary="Delete a document from a property",
     *      description="This method deletes a document associated with a specific property",
      *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          required=true,
     *          description="ID",
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

    public function deleteTenantInspection($id)
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
            $inspection = TenantInspection::
              where('created_by', auth()->user()->id)
            ->where([
                "id" => $id
            ])
            ->first();

            if (!$inspection) {
                return response()->json([
                    "message" => "no inspection found"
                ], 404);
            }

            // Delete the document
            $inspection->delete();

            return response()->json(['message' => 'inspection deleted successfully.'], 200);

        } catch (Exception $e) {
            return response()->json(['message' => 'An error occurred.'], 500);
        }
    }
}
