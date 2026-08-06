<?php

namespace App\Http\Controllers;

use App\Http\Requests\BathRequest;
use App\Models\Bath;
use App\Rules\ValidateBath;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BathController extends Controller
{
    /**
     * @OA\Get(
     *      path="/v1.0/baths",
     *      operationId="getAllBath",
     *      tags={"property_management.baths"},
     *      security={{"bearerAuth": {}}},
     *      summary="Get all baths",
     *      description="Returns list of baths",
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent())
     * )
     */
    // GET ALL BATHS
    public function getAllBath(Request $request)
    {
        $query = Bath::bathFilters($request->all());
        $baths = retrieve_data($query, 'sort_order', (new Bath)->getTable());

        return response()->json([
            'success' => true,
            'message' => 'Baths retrieved successfully',
            'data' => $baths
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *      path="/v1.0/baths/{id}",
     *      operationId="getBathById",
     *      tags={"property_management.baths"},
     *      security={{"bearerAuth": {}}},
     *      summary="Get bath by ID",
     *      description="Returns a single bath",
     *      @OA\Parameter(name="id", in="path", required=true, description="Bath ID", example="1"),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
     * )
     */
    // GET BATH BY ID
    public function getBathById($id)
    {
        $bath = Bath::find($id);

        if (!$bath) {
            return response()->json([
                'success' => false,
                'message' => 'Bath not found',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'success' => true,
            'message' => 'Bath retrieved successfully',
            'data' => $bath
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/v1.0/baths",
     *      operationId="createBath",
     *      tags={"property_management.baths"},
     *      security={{"bearerAuth": {}}},
     *      summary="Create new bath",
     *      description="Creates a new bath",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"title"},
     *              @OA\Property(property="title", type="string", example="Ensuite"),
     *              @OA\Property(property="description", type="string", example="Attached bathroom")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Unprocessable Content", @OA\JsonContent())
     * )
     */
    // CREATE BATH
    public function createBath(BathRequest $request)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();
        $bath = Bath::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bath created successfully',
            'data' => $bath
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Put(
     *      path="/v1.0/baths",
     *      operationId="updateBath",
     *      tags={"property_management.baths"},
     *      security={{"bearerAuth": {}}},
     *      summary="Update existing bath",
     *      description="Updates an existing bath",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"id", "title"},
     *              @OA\Property(property="id", type="integer", example=1),
     *              @OA\Property(property="title", type="string", example="Ensuite Updated"),
     *              @OA\Property(property="description", type="string", example="Updated attached bathroom")
     *          )
     *      ),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Unprocessable Content", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
     * )
     */
    // UPDATE BATH
    public function updateBath(BathRequest $request)
    {
        if (!$request->user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        $validated = $request->validated();
        $bath = Bath::find($validated['id']);

        if (!$bath) {
            return response()->json([
                'success' => false,
                'message' => 'Bath not found',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }

        $bath->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Bath updated successfully',
            'data' => $bath
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Delete(
     *      path="/v1.0/baths/{ids}",
     *      operationId="deleteBath",
     *      tags={"property_management.baths"},
     *      security={{"bearerAuth": {}}},
     *      summary="Delete multiple baths",
     *      description="Deletes baths by comma-separated IDs",
     *      @OA\Parameter(name="ids", in="path", required=true, description="Comma-separated IDs", example="1,2,3"),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=422, description="Unprocessable Content", @OA\JsonContent())
     * )
     */
    // DELETE BATH
    public function deleteBath(Request $request, $ids)
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
        $existingIds = Bath::whereIn('id', $idsArray)
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
        Bath::destroy($existingIds);

        return response()->json([
            'success' => true,
            'message' => 'Baths deleted successfully',
            'data' => ['deleted_ids' => $existingIds]
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/v1.0/baths/{id}/toggle-active",
     *      operationId="toggleBathActive",
     *      tags={"property_management.baths"},
     *      security={{"bearerAuth": {}}},
     *      summary="Toggle bath active status",
     *      description="Toggles the active status of a bath",
     *      @OA\Parameter(name="id", in="path", required=true, description="Bath ID", example="1"),
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=403, description="Forbidden", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not found", @OA\JsonContent())
     * )
     */
    // TOGGLE BATH ACTIVE STATUS
    public function toggleBathActive($id)
    {
        if (!request()->user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized Access.',
                'data' => null
            ], Response::HTTP_FORBIDDEN);
        }

        $bath = Bath::find($id);

        if (!$bath) {
            return response()->json([
                'success' => false,
                'message' => 'Bath not found',
                'data' => null
            ], Response::HTTP_NOT_FOUND);
        }

        $bath->is_active = !$bath->is_active;
        $bath->save();

        return response()->json([
            'success' => true,
            'message' => 'Bath status toggled successfully',
            'data' => $bath
        ], Response::HTTP_OK);
    }

    /**
     * @OA\Put(
     *      path="/v1.0/baths/sort-order",
     *      operationId="updateBathSortOrder",
     *      tags={"property_management.baths"},
     *      security={{"bearerAuth": {}}},
     *      summary="Update baths sort order",
     *      description="Updates the sort order for multiple baths",
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
    // UPDATE BATH SORT ORDER
    public function updateBathSortOrder(Request $request)
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
            'ids.*' => ['integer', new ValidateBath()]
        ]);

        $ids = $validated['ids'];
        
        \Illuminate\Support\Facades\DB::transaction(function () use ($ids) {
            foreach ($ids as $index => $id) {
                Bath::where('id', $id)->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Baths sort order updated successfully',
            'data' => null
        ], Response::HTTP_OK);
    }
}
