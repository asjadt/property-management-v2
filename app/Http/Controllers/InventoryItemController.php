<?php

namespace App\Http\Controllers;

use App\Http\Requests\InventoryItemRequest;
use App\Models\InventoryItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    /**
     * @OA\Post(
     *     path="/v1.0/inventory-items",
     *     operationId="createInventoryItem",
     *     tags={"property_inventories.Inventory_Items"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *     summary="Create a new inventory item",
     *     description="Add a new inventory item to the system.",
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
     *         description="Inventory item created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inventory item created successfully."),
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
    public function createInventoryItem(InventoryItemRequest $request)
    {
        $request_payload = $request->validated();

        $created_item = InventoryItem::create($request_payload);

        return response()->json([
            'success' => true,
            'message' => 'Inventory item created successfully.',
            'data' => $created_item,
        ], 201);
    }


    /**
     * @OA\Put(
     *     path="/v1.0/inventory-items",
     *     operationId="updateInventoryItem",
     *     tags={"property_inventories.Inventory_Items"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *     summary="Update an existing inventory item",
     *     description="Update the details of a specific inventory item by its ID.",
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
     *         description="Inventory item updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inventory item updated successfully."),
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
     *         description="Inventory item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Inventory item not found.")
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
    public function updateInventoryItem(InventoryItemRequest $request)
    {
        $request_payload = $request->validated();

        $inventory_item = InventoryItem::find($request_payload['id']);

        if (!$inventory_item) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory item not found.',
            ], 404);
        }

        $inventory_item->update($request_payload);

        return response()->json([
            'success' => true,
            'message' => 'Inventory item updated successfully.',
            'data' => $inventory_item,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/v1.0/inventory-items",
     *     operationId="getAllInventoryItems",
     *     tags={"property_inventories.Inventory_Items"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *     summary="Get all inventory items",
     *     description="Retrieve a list of all inventory items, with optional filtering by active status.",
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
     *         description="Number of items per page for pagination",
     *         @OA\Schema(type="integer", example="")
     *     ),
     *     @OA\Parameter(
     *         name="is_active",
     *         in="query",
     *         required=false,
     *         description="Filter items by active status (true/false)",
     *         @OA\Schema(type="boolean", example="")
     *     ),
     *     @OA\Parameter(
     *         name="search_key",
     *         in="query",
     *         required=false,
     *         description="Search term to filter items by name",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of inventory items retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inventory items retrieved successfully."),
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
    public function getAllInventoryItems(Request $request)
    {
        $query = InventoryItem::filters();


        $inventory_items = retrieve_data($query, 'created_at', 'inventory_items');

        return response()->json([
            'success' => true,
            'message' => 'Inventory items retrieved successfully.',
            'meta' => $inventory_items['meta'],
            'data' => $inventory_items['data'],
        ]);
    }

    /**
     * @OA\Get(
     *     path="/v1.0/inventory-items/{id}",
     *     operationId="getInventoryItemById",
     *     tags={"property_inventories.Inventory_Items"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *     summary="Get a single inventory item",
     *     description="Retrieve a specific inventory item by its ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the inventory item to retrieve",
     *         @OA\Schema(type="integer", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inventory item retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Inventory item retrieved successfully."),
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
     *         description="Inventory item not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Inventory item not found.")
     *         )
     *     )
     * )
     */
    public function getInventoryItemById($id)
    {
        $item = InventoryItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Inventory item not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Inventory item retrieved successfully.',
            'data' => $item,
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/v1.0/inventory-items",
     *     operationId="deleteInventoryItemByIds",
     *     tags={"property_inventories.Inventory_Items"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *     summary="Delete multiple inventory items by IDs",
     *     description="Delete inventory items by passing comma-separated IDs. Throws error if any ID does not exist.",
     *     @OA\Parameter(
     *         name="ids",
     *         in="query",
     *         required=true,
     *         description="Comma-separated inventory item IDs to delete, e.g., 1,2,3",
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Inventory items deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Selected inventory items deleted successfully."),
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
    public function deleteInventoryItemByIds(string $ids)
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
        $existingIds = InventoryItem::whereIn('id', $ids)->pluck('id')->toArray();

        // Check if all IDs exist
        $missingIds = array_diff($ids, $existingIds);
        if (!empty($missingIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Some IDs do not exist: ' . implode(',', $missingIds),
            ], 422);
        }

        // Delete all existing IDs
        InventoryItem::whereIn('id', $existingIds)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Selected inventory items deleted successfully.',
            'data' => [
                'deleted_ids' => $existingIds
            ],
        ]);
    }
}
