<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyTypeRequest;
use App\Http\Requests\SyncPropertyTypeRelationsRequest;
use App\Models\PropertyType;
use Illuminate\Support\Facades\DB;
use App\Rules\ValidatePropertyType;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PropertyTypeController extends Controller
{
    /**
     * @OA\Get(
     *      path="/v1.0/property-types",
     *      operationId="getAllPropertyType",
     *      tags={"property_management.property_types"},
     *      security={{"bearerAuth": {}}},
     *      summary="Get all property types",
     *      description="Returns list of property types",
     *      @OA\Response(
     *          response=200, 
     *          description="Successful operation", 
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Property types retrieved successfully"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="current_page", type="integer", example=1),
     *                  @OA\Property(property="data", type="array", @OA\Items(
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="title", type="string", example="Apartment"),
     *                      @OA\Property(property="description", type="string", example="An apartment building"),
     *                      @OA\Property(property="sort_order", type="integer", example=1),
     *                      @OA\Property(property="is_active", type="boolean", example=true),
     *                      @OA\Property(property="beds", type="array", @OA\Items(
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="title", type="string", example="One")
     *                      )),
     *                      @OA\Property(property="baths", type="array", @OA\Items(
     *                          @OA\Property(property="id", type="integer", example=2),
     *                          @OA\Property(property="title", type="string", example="Two")
     *                      ))
     *                  )),
     *                  @OA\Property(property="first_page_url", type="string"),
     *                  @OA\Property(property="from", type="integer", example=1),
     *                  @OA\Property(property="last_page", type="integer", example=1),
     *                  @OA\Property(property="last_page_url", type="string"),
     *                  @OA\Property(property="next_page_url", type="string", nullable=true),
     *                  @OA\Property(property="path", type="string"),
     *                  @OA\Property(property="per_page", type="integer", example=15),
     *                  @OA\Property(property="prev_page_url", type="string", nullable=true),
     *                  @OA\Property(property="to", type="integer", example=5),
     *                  @OA\Property(property="total", type="integer", example=5)
     *              )
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent())
     * )
     */

        // GET ALL PROPERTY TYPE
    public function getAllPropertyType(Request $request)
    {
        $query = PropertyType::propertyTypeFilters($request->all())->with(['beds', 'baths']);
        $propertyTypes = retrieve_data($query, 'sort_order', (new PropertyType)->getTable());

        return response()->json([
            'success' => true,
            'message' => 'Property types retrieved successfully',
            'data' => $propertyTypes
        ], Response::HTTP_OK);
    }


    /**
     * @OA\Put(
     *      path="/v1.0/property-types/{id}/sync-options",
     *      operationId="syncPropertyTypeRelations",
     *      tags={"property_management.property_types"},
     *      security={{"bearerAuth": {}}},
     *      summary="Sync beds and baths to property type",
     *      description="Syncs bed and bath relationships for a specific property type",
     *      @OA\Parameter(name="id", in="path", required=true, description="Property type ID", example="1"),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="bed_ids", type="array", @OA\Items(type="integer"), example={1, 2}),
     *              @OA\Property(property="bath_ids", type="array", @OA\Items(type="integer"), example={3, 4})
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Unprocessable Content", @OA\JsonContent())
     * )
     */
    // SYNC PROPERTY TYPE RELATIONS
    public function syncPropertyTypeRelations(SyncPropertyTypeRelationsRequest $request, $id)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        $propertyType = PropertyType::find($id);

        if (!$propertyType) {
            return response()->json([
                'success' => false,
                'message' => 'Property type not found',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validated();

        DB::transaction(function () use ($propertyType, $validated) {
            if (isset($validated['bed_ids'])) {
                $propertyType->beds()->sync($validated['bed_ids']);
            }
            if (isset($validated['bath_ids'])) {
                $propertyType->baths()->sync($validated['bath_ids']);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Property type relations synced successfully',
            'data' => $propertyType->load(['beds', 'baths'])
        ], Response::HTTP_OK);
    }


    /**
     * @OA\Get(
     *      path="/v1.0/property-types/{id}",
     *      operationId="getPropertyTypeById",
     *      tags={"property_management.property_types"},
     *      security={{"bearerAuth": {}}},
     *      summary="Get property type by ID",
     *      description="Returns a single property type",
     *      @OA\Parameter(name="id", in="path", required=true, description="Property type ID", example="1"),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Property type retrieved successfully"),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="id", type="integer", example=1),
     *                  @OA\Property(property="title", type="string", example="Apartment"),
     *                  @OA\Property(property="description", type="string", example="An apartment building"),
     *                  @OA\Property(property="sort_order", type="integer", example=1),
     *                  @OA\Property(property="is_active", type="boolean", example=true),
     *                  @OA\Property(property="beds", type="array", @OA\Items(
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="title", type="string", example="One")
     *                  )),
     *                  @OA\Property(property="baths", type="array", @OA\Items(
     *                      @OA\Property(property="id", type="integer", example=2),
     *                      @OA\Property(property="title", type="string", example="Two")
     *                  ))
     *              )
     *          )
     *      ),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
     * )
     */
    // GET PROPERTY TYPE BY ID
    public function getPropertyTypeById($id)
    {
        $propertyType = PropertyType::with(['beds', 'baths'])->find($id);

        if (!$propertyType) {
            return response()->json([
                'success' => false,
                'message' => 'Property type not found',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Property type retrieved successfully',
            'data' => $propertyType
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/v1.0/property-types",
     *      operationId="createPropertyType",
     *      tags={"property_management.property_types"},
     *      security={{"bearerAuth": {}}},
     *      summary="Create new property type",
     *      description="Creates a new property type",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"title"},
     *              @OA\Property(property="title", type="string", example="Apartment"),
     *              @OA\Property(property="description", type="string", example="An apartment building"),
     *              @OA\Property(property="bed_ids", type="array", @OA\Items(type="integer"), example={1, 2}),
     *              @OA\Property(property="bath_ids", type="array", @OA\Items(type="integer"), example={3, 4})
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Unprocessable Content", @OA\JsonContent())
     * )
     */
    // CREATE PROPERTY TYPE
    public function createPropertyType(PropertyTypeRequest $request)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();

        $propertyType = DB::transaction(function () use ($validated) {
            $pt = PropertyType::create($validated);

            if (isset($validated['bed_ids'])) {
                $pt->beds()->sync($validated['bed_ids']);
            }
            if (isset($validated['bath_ids'])) {
                $pt->baths()->sync($validated['bath_ids']);
            }

            return $pt->load(['beds', 'baths']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Property type created successfully',
            'data' => $propertyType
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *      path="/v1.0/property-types",
     *      operationId="updatePropertyType",
     *      tags={"property_management.property_types"},
     *      security={{"bearerAuth": {}}},
     *      summary="Update existing property type",
     *      description="Updates an existing property type",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"id", "title"},
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="title", type="string", example="Apartment Updated"),
     *              @OA\Property(property="description", type="string", example="An updated apartment building"),
     *              @OA\Property(property="bed_ids", type="array", @OA\Items(type="integer"), example={1, 2}),
     *              @OA\Property(property="bath_ids", type="array", @OA\Items(type="integer"), example={3, 4})
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Unprocessable Content", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
     * )
     */
    // UPDATE PROPERTY TYPE
    public function updatePropertyType(PropertyTypeRequest $request)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();
        $propertyType = PropertyType::find($validated['id']);

        if (!$propertyType) {
            return response()->json([
                'success' => false,
                'message' => 'Property type not found',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }

        $propertyType = DB::transaction(function () use ($propertyType, $validated) {
            $propertyType->update($validated);

            if (isset($validated['bed_ids'])) {
                $propertyType->beds()->sync($validated['bed_ids']);
            }
            if (isset($validated['bath_ids'])) {
                $propertyType->baths()->sync($validated['bath_ids']);
            }

            return $propertyType->load(['beds', 'baths']);
        });

        return response()->json([
            'success' => true,
            'message' => 'Property type updated successfully',
            'data' => $propertyType
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/v1.0/property-types/{ids}",
     *      operationId="deletePropertyType",
     *      tags={"property_management.property_types"},
     *      security={{"bearerAuth": {}}},
     *      summary="Delete multiple property types",
     *      description="Deletes property types by comma-separated IDs",
     *      @OA\Parameter(name="ids", in="path", required=true, description="Comma-separated IDs", example="1,2,3"),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Unprocessable Content", @OA\JsonContent())
     * )
     */
    // DELETE PROPERTY TYPE
    public function deletePropertyType(Request $request, $ids)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        // PARSE COMMA SEPARATED IDS
        $idsArray = explode(',', $ids);

        // FETCH EXISTING IDS FROM DB
        $existingIds = PropertyType::whereIn('id', $idsArray)
            ->pluck('id')
            ->toArray();

        // FIND INVALID IDS
        $nonExistingIds = array_diff($idsArray, $existingIds);

        // THROW ERROR IF ANY ID IS INVALID
        if (!empty($nonExistingIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some or all of the specified data do not exist. Invalid IDs: ' . implode(', ', $nonExistingIds),
                'data' => null
            ], Response::HTTP_BAD_REQUEST);
        }

        // DELETE VALID IDS
        PropertyType::destroy($existingIds);

        return response()->json([
            'success' => true,
            'message' => 'Property types deleted successfully',
            'data' => ['deleted_ids' => $existingIds]
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/v1.0/property-types/{id}/toggle-active",
     *      operationId="togglePropertyTypeActive",
     *      tags={"property_management.property_types"},
     *      security={{"bearerAuth": {}}},
     *      summary="Toggle property type active status",
     *      description="Toggles the active status of a property type",
     *      @OA\Parameter(name="id", in="path", required=true, description="Property type ID", example="1"),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
     * )
     */
    // TOGGLE PROPERTY TYPE ACTIVE STATUS
    public function togglePropertyTypeActive($id)
    {
        if (!request()->user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        $propertyType = PropertyType::find($id);

        if (!$propertyType) {
            return response()->json([
                'success' => false,
                'message' => 'Property type not found',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }

        $propertyType->is_active = !$propertyType->is_active;
        $propertyType->save();

        return response()->json([
            'success' => true,
            'message' => 'Property type status toggled successfully',
            'data' => $propertyType
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/v1.0/property-types/sort-order",
     *      operationId="updatePropertyTypeSortOrder",
     *      tags={"property_management.property_types"},
     *      security={{"bearerAuth": {}}},
     *      summary="Update property types sort order",
     *      description="Updates the sort order for multiple property types",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"ids"},
     *              @OA\Property(
     *                  property="ids",
     *                  type="array",
     *                  @OA\Items(type="integer"),
     *                  example={3, 1, 2}
     *              )
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Unprocessable Content", @OA\JsonContent())
     * )
     */
    // UPDATE PROPERTY TYPE SORT ORDER
    public function updatePropertyTypeSortOrder(Request $request)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => ['integer', new ValidatePropertyType()]
        ]);

        $ids = $validated['ids'];

        \Illuminate\Support\Facades\DB::transaction(function () use ($ids) {
            foreach ($ids as $index => $id) {
                PropertyType::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Property types sort order updated successfully',
            'data' => null
        ], Response::HTTP_OK);
    }
}
