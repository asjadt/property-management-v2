<?php

namespace App\Http\Controllers;

use App\Models\Business;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BusinessController extends Controller
{
    /**
     * @OA\Get(
     *      path="/v1.0/businesses",
     *      operationId="getAllBusinesses",
     *      tags={"business_management"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(name="per_page", in="query", required=false, example="10"),
     *      @OA\Parameter(name="page", in="query", required=false, example="1"),
     *      @OA\Parameter(name="order_by", in="query", required=false, example="id"),
     *      @OA\Parameter(name="sort_order", in="query", required=false, example="DESC"),
     *      summary="Get all businesses with pagination",
     *      description="Get all businesses with pagination",
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=401, description="Unauthenticated", @OA\JsonContent()),
     *      @OA\Response(response=500, description="Server Error", @OA\JsonContent())
     * )
     */
    public function getAllBusinesses(Request $request)
    {
        try {
            // GET BUSINESSES QUERY
            $query = Business::with('owner')->query();

            // FETCH DATA WITH PAGINATION
            $result = retrieve_data($query, "id", (new Business)->getTable());

            return response()->json([
                'success' => true,
                'message' => 'Businesses retrieved successfully.',
                'meta'    => $result['meta'],
                'data'    => $result['data'],
            ], Response::HTTP_OK);
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * @OA\Get(
     *      path="/v1.0/businesses/{id}",
     *      operationId="getBusinessById",
     *      tags={"business_management"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(name="id", in="path", required=true, example="1"),
     *      summary="Get a single business by ID",
     *      description="Get a single business by ID",
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not Found", @OA\JsonContent()),
     *      @OA\Response(response=500, description="Server Error", @OA\JsonContent())
     * )
     */
    public function getBusinessById($id)
    {
        try {
            // FETCH SINGLE BUSINESS
            $business = Business::find($id);

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found.',
                    'data'    => null
                ], Response::HTTP_NOT_FOUND);
            }

            return response()->json([
                'success' => true,
                'message' => 'Business retrieved successfully.',
                'data'    => $business
            ], Response::HTTP_OK);
        } catch (Exception $e) {
           throw $e;
        }
    }

    /**
     * @OA\Delete(
     *      path="/v1.0/businesses/{id}",
     *      operationId="businessDelete",
     *      tags={"business_management"},
     *      security={{"bearerAuth": {}}},
     *      @OA\Parameter(name="id", in="path", required=true, example="1"),
     *      summary="Delete a single business by ID",
     *      description="Delete a single business by ID",
     *      @OA\Response(response=200, description="Successful operation", @OA\JsonContent()),
     *      @OA\Response(response=404, description="Not Found", @OA\JsonContent()),
     *      @OA\Response(response=500, description="Server Error", @OA\JsonContent())
     * )
     */
    public function businessDelete($id)
    {
        try {
            // CHECK IF BUSINESS EXISTS
            $business = Business::find($id);

            if (!$business) {
                return response()->json([
                    'success' => false,
                    'message' => 'Business not found.',
                    'data'    => null
                ], Response::HTTP_NOT_FOUND);
            }

            // DELETE BUSINESS
            $business->delete();

            return response()->json([
                'success' => true,
                'message' => 'Business deleted successfully.',
                'data'    => null
            ], Response::HTTP_OK);
        } catch (Exception $e) {
           throw $e;
        }
    }
}
