<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyTypeRequest;
use App\Models\PropertyType;
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
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent())
     * )
     */
    // GET ALL PROPERTY TYPE
    public function getAllPropertyType(Request $request)
    {
        $query = PropertyType::propertyTypeFilters($request->all());
        $propertyTypes = retrieve_data($query, 'sort_order', (new PropertyType)->getTable());

        return response()->json([
            'success' => true,
            'message' => 'Property types retrieved successfully',
            'data' => $propertyTypes
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
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
     * )
     */
    // GET PROPERTY TYPE BY ID
    public function getPropertyTypeById($id)
    {
        $propertyType = PropertyType::find($id);

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
     *              @OA\Property(property="description", type="string", example="An apartment building")
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
        $validated = $request->validated();
        $propertyType = PropertyType::create($validated);

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
     *              @OA\Property(property="description", type="string", example="An updated apartment building")
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
        $validated = $request->validated();
        $propertyType = PropertyType::find($validated['id']);

        if (!$propertyType) {
            return response()->json([
                'success' => false,
                'message' => 'Property type not found',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }

        $propertyType->update($validated);

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
