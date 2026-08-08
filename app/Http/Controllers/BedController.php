<?php

namespace App\Http\Controllers;

use App\Http\Requests\BedRequest;
use App\Models\Bed;
use App\Rules\ValidateBed;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BedController extends Controller
{
    /**
     * @OA\Get(
     *      path="/v1.0/beds",
     *      operationId="getAllBed",
     *      tags={"property_management.beds"},
     *      security={{"bearerAuth": {}}},
     *      summary="Get all beds",
     *      description="Returns list of beds",
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent())
     * )
     */
    // GET ALL BEDS
    public function getAllBed(Request $request)
    {
        $query = Bed::bedFilters($request->all());
        $beds = retrieve_data($query, 'sort_order', (new Bed)->getTable());

        return response()->json([
            'success' => true,
            'message' => 'Beds retrieved successfully',
            'meta' => $beds['meta'],
            'data' => $beds['data']
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1.0/beds/{id}",
     *      operationId="getBedById",
     *      tags={"property_management.beds"},
     *      security={{"bearerAuth": {}}},
     *      summary="Get bed by ID",
     *      description="Returns a single bed",
     *      @OA\Parameter(name="id", in="path", required=true, description="Bed ID", example="1"),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
     * )
     */
    // GET BED BY ID
    public function getBedById($id)
    {
        $bed = Bed::find($id);

        if (!$bed) {
            return response()->json([
                'success' => false,
                'message' => 'Bed not found',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bed retrieved successfully',
            'data' => $bed
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/v1.0/beds",
     *      operationId="createBed",
     *      tags={"property_management.beds"},
     *      security={{"bearerAuth": {}}},
     *      summary="Create new bed",
     *      description="Creates a new bed",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"title"},
     *              @OA\Property(property="title", type="string", example="King Size"),
     *              @OA\Property(property="description", type="string", example="Large bed")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Unprocessable Content", @OA\JsonContent())
     * )
     */
    // CREATE BED
    public function createBed(BedRequest $request)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();
        $bed = Bed::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bed created successfully',
            'data' => $bed
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *      path="/v1.0/beds",
     *      operationId="updateBed",
     *      tags={"property_management.beds"},
     *      security={{"bearerAuth": {}}},
     *      summary="Update existing bed",
     *      description="Updates an existing bed",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"id", "title"},
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="title", type="string", example="King Size Updated"),
     *              @OA\Property(property="description", type="string", example="Updated large bed")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Unprocessable Content", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
     * )
     */
    // UPDATE BED
    public function updateBed(BedRequest $request)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();
        $bed = Bed::find($validated['id']);

        if (!$bed) {
            return response()->json([
                'success' => false,
                'message' => 'Bed not found',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }

        $bed->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bed updated successfully',
            'data' => $bed
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/v1.0/beds/{ids}",
     *      operationId="deleteBed",
     *      tags={"property_management.beds"},
     *      security={{"bearerAuth": {}}},
     *      summary="Delete multiple beds",
     *      description="Deletes beds by comma-separated IDs",
     *      @OA\Parameter(name="ids", in="path", required=true, description="Comma-separated IDs", example="1,2,3"),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Unprocessable Content", @OA\JsonContent())
     * )
     */
    // DELETE BED
    public function deleteBed(Request $request, $ids)
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
        $existingIds = Bed::whereIn('id', $idsArray)
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
        Bed::destroy($existingIds);

        return response()->json([
            'success' => true,
            'message' => 'Beds deleted successfully',
            'data' => ['deleted_ids' => $existingIds]
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/v1.0/beds/{id}/toggle-active",
     *      operationId="toggleBedActive",
     *      tags={"property_management.beds"},
     *      security={{"bearerAuth": {}}},
     *      summary="Toggle bed active status",
     *      description="Toggles the active status of a bed",
     *      @OA\Parameter(name="id", in="path", required=true, description="Bed ID", example="1"),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
     * )
     */
    // TOGGLE BED ACTIVE STATUS
    public function toggleBedActive($id)
    {
        if (!request()->user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        $bed = Bed::find($id);

        if (!$bed) {
            return response()->json([
                'success' => false,
                'message' => 'Bed not found',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }

        $bed->is_active = !$bed->is_active;
        $bed->save();

        return response()->json([
            'success' => true,
            'message' => 'Bed status toggled successfully',
            'data' => $bed
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/v1.0/beds/sort-order",
     *      operationId="updateBedSortOrder",
     *      tags={"property_management.beds"},
     *      security={{"bearerAuth": {}}},
     *      summary="Update beds sort order",
     *      description="Updates the sort order for multiple beds",
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
    // UPDATE BED SORT ORDER
    public function updateBedSortOrder(Request $request)
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
            'ids.*' => ['integer', new ValidateBed()]
        ]);

        $ids = $validated['ids'];

        \Illuminate\Support\Facades\DB::transaction(function () use ($ids) {
            foreach ($ids as $index => $id) {
                Bed::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Beds sort order updated successfully',
            'data' => null
        ], Response::HTTP_OK);
    }
}
