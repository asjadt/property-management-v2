<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryLocationRequest;
use App\Models\InventoryLocation;
use Illuminate\Http\Request;

class InventoryLocationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/v1.0/inventory-locations",
     *     operationId="createInventoryLocation",
     *     tags={"property_inventories.Inventory_Locations"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *     summary="Create a new inventory location",
     *     description="Add a new inventory location to the system.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name"},
     *             @OA\Property(property="name", type="string", example="Air Conditioner"),
     *             @OA\Property(property="description", type="string", example="1.5 Ton Split AC"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Inventory location created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inventory location created successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Air Conditioner"),
     *                 @OA\Property(property="description", type="string", example="1.5 Ton Split AC"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-24T12:00:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The name field is required.")
     *         )
     *     )
     * )
     */
    public function createInventoryLocation(InventoryLocationRequest $request)
    {
        $request_payload = $request->validated();

        $created_item = InventoryLocation::create($request_payload);

        return response()->json([
            'success' => true,
            'message' => 'Inventory location created successfully.',
            'data' => $created_item,
        ], 201);
    }


    /**
     * @OA\Put(
     *     path="/v1.0/inventory-locations",
     *     operationId="updateInventoryLocation",
     *     tags={"property_inventories.Inventory_Locations"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *     summary="Update an existing inventory location",
     *     description="Update the details of a specific inventory location by its ID.",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=""),
     *             @OA\Property(property="name", type="string", example="Updated Air Conditioner"),
     *             @OA\Property(property="description", type="string", example="1.5 Ton Split AC (Updated Model)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inventory location updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inventory location updated successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Updated Air Conditioner"),
     *                 @OA\Property(property="description", type="string", example="1.5 Ton Split AC (Updated Model)"),
     *                 @OA\Property(property="is_active", type="boolean", example=false),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-24T12:10:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inventory location not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Inventory location not found.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="The name field must be a string.")
     *         )
     *     )
     * )
     */
    public function updateInventoryLocation(InventoryLocationRequest $request)
    {
        $request_payload = $request->validated();

        $inventory_location = InventoryLocation::find($request_payload['id']);

        if (!$inventory_location) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory item not found.',
            ], 404);
        }

        $inventory_location->update($request_payload);

        return response()->json([
            'success' => true,
            'message' => 'Inventory location updated successfully.',
            'data' => $inventory_location,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/v1.0/inventory-locations",
     *     operationId="getAllInventoryLocations",
     *     tags={"property_inventories.Inventory_Locations"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *     summary="Get all inventory locations",
     *     description="Retrieve a list of all inventory locations, with optional filtering by active status.",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         description="Page number for pagination",
     *         @OA\Schema(type="integer", example="")
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         description="Number of locations per page for pagination",
     *         @OA\Schema(type="integer", example="")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         required=false,
     *         description="Filter locations by active status (true/false)",
     *         @OA\Schema(type="boolean", example="")
     *     ),
     *     @OA\Parameter(
     *         name="search_key",
     *         in="query",
     *         required=false,
     *         description="Search term to filter locations by name",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of inventory locations retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inventory locations retrieved successfully."),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Air Conditioner"),
     *                     @OA\Property(property="description", type="string", example="1.5 Ton Split AC"),
     *                     @OA\Property(property="is_active", type="boolean", example=true),
     *                     @OA\Property(property="created_at", type="string", example="2025-10-24T12:00:00.000000Z"),
     *                     @OA\Property(property="updated_at", type="string", example="2025-10-24T12:10:00.000000Z")
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function getAllInventoryLocations(Request $request)
    {
        $query = InventoryLocation::filters();


        $inventory_locations = retrieve_data($query, 'created_at', 'inventory_locations');

        return response()->json([
            'success' => true,
            'message' => 'Inventory locations retrieved successfully.',
            'meta' => $inventory_locations['meta'],
            'data' => $inventory_locations['data'],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/v1.0/inventory-locations/{id}",
     *     operationId="getInventoryLocationById",
     *     tags={"property_inventories.Inventory_Locations"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *     summary="Get a single inventory location",
     *     description="Retrieve a specific inventory location by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the inventory location to retrieve",
     *         @OA\Schema(type="integer", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inventory location retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inventory location retrieved successfully."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="Air Conditioner"),
     *                 @OA\Property(property="description", type="string", example="1.5 Ton Split AC"),
     *                 @OA\Property(property="is_active", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", example="2025-10-24T12:00:00.000000Z"),
     *                 @OA\Property(property="updated_at", type="string", example="2025-10-24T12:10:00.000000Z")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Inventory location not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Inventory location not found.")
     *         )
     *     )
     * )
     */
    public function getInventoryLocationById($id)
    {
        $inventory_location = InventoryLocation::find($id);

        if (!$inventory_location) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory location not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Inventory item retrieved successfully.',
            'data' => $inventory_location,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/v1.0/inventory-locations",
     *     operationId="deleteInventoryLocationByIds",
     *     tags={"property_inventories.Inventory_Locations"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *     summary="Delete multiple inventory locations by IDs",
     *     description="Delete inventory locations by passing comma-separated IDs. Throws error if any ID does not exist.",
     *     @OA\Parameter(
     *         name="ids",
     *         in="query",
     *         required=true,
     *         description="Comma-separated inventory item IDs to delete, e.g., 1,2,3",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inventory locations deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Selected inventory locations deleted successfully."),
     *             @OA\Property(property="deleted_count", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Some IDs do not exist.")
     *         )
     *     )
     * )
     */
    public function deleteInventoryLocationByIds(string $ids)
    {

        // Convert comma-separated string into array of integers
        $ids = collect(explode(',', $ids))
            ->map(fn($id) => (int) trim($id))
            ->filter(fn($id) => $id > 0)
            ->unique()
            ->toArray();

        if (empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid IDs provided.',
            ], 422);
        }

        // Fetch existing IDs from the database
        $existingIds = InventoryLocation::whereIn('id', $ids)->pluck('id')->toArray();

        // Check if all IDs exist
        $missingIds = array_diff($ids, $existingIds);
        if (!empty($missingIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some IDs do not exist: ' . implode(',', $missingIds),
            ], 422);
        }

        // Delete all existing IDs
        InventoryLocation::whereIn('id', $existingIds)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected inventory locations deleted successfully.',
            'data' => [
                'deleted_ids' => $existingIds
            ],
        ]);
    }
}
