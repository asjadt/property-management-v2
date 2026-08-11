<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaleItemCreateRequest;
use App\Http\Requests\SaleItemUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\SaleItem;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class SaleItemController extends Controller
{
    use ErrorUtil, UserActivityUtil;


/**
 *
 * @OA\Post(
 *      path="/v1.0/sale-items",
 *      operationId="createSaleItem",
 *      tags={"property_management.sale_item_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store sale item ",
 *      description="This method is to store sale item ",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"name","description","logo"},
 *  *             @OA\Property(property="name", type="string", format="string",example="name"),
  *             @OA\Property(property="description", type="string", format="string",example="description"),

 *            @OA\Property(property="price", type="number", format="number",example="10.10"),

 *
 *         ),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *       @OA\JsonContent(),
 *       ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthenticated",
 * @OA\JsonContent(),
 *      ),
 *        @OA\Response(
 *          response=422,
 *          description="Unprocesseble Content",
 *    @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden",
 *   @OA\JsonContent()
 * ),
 *  * @OA\Response(
 *      response=400,
 *      description="Bad Request",
 *   *@OA\JsonContent()
 *   ),
 * @OA\Response(
 *      response=404,
 *      description="not found",
 *   *@OA\JsonContent()
 *   )
 *      )
 *     )
 */

public function createSaleItem(SaleItemCreateRequest $request)
{
    try {
        return DB::transaction(function () use ($request) {



            $request_data = $request->validated();
            $request_data["created_by"] = $request->user()->id;

            if ($request->user()->hasRole('superadmin')) {
                $request_data['is_default'] = $request->filled('business_id') ? 0 : 1;
                $request_data['business_id'] = $request->filled('business_id') ? $request->input('business_id') : null;
            } else {
                $request_data['is_default'] = 0;
                $request_data['business_id'] = $request->user()->business_id;
            }

            $sale_item =  SaleItem::create($request_data);
            $sale_item->generated_id = Str::random(4) . $sale_item->id . Str::random(4);
            $sale_item->save();


            return response()->json([
                "success" => true,
                "message" => "Sale item created successfully",
                "data" => $sale_item
            ], Response::HTTP_CREATED);
        });

    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 * @OA\Put(
 *      path="/v1.0/sale-items",
 *      operationId="updateSaleItem",
 *      tags={"property_management.sale_item_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to update sale item ",
 *      description="This method is to update sale item ",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"id","name","description","logo"},
 *     *             @OA\Property(property="id", type="number", format="number",example="1"),
  *  *             @OA\Property(property="name", type="string", format="string",example="name"),
  *             @OA\Property(property="description", type="string", format="string",example="description"),


 *            @OA\Property(property="price", type="number", format="number",example="10.10"),
 *
 *         ),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *       @OA\JsonContent(),
 *       ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthenticated",
 * @OA\JsonContent(),
 *      ),
 *        @OA\Response(
 *          response=422,
 *          description="Unprocesseble Content",
 *    @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden",
 *   @OA\JsonContent()
 * ),
 *  * @OA\Response(
 *      response=400,
 *      description="Bad Request",
 *   *@OA\JsonContent()
 *   ),
 * @OA\Response(
 *      response=404,
 *      description="not found",
 *   *@OA\JsonContent()
 *   )
 *      )
 *     )
 */

public function updateSaleItem(SaleItemUpdateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return  DB::transaction(function () use ($request) {

            $request_data = $request->validated();

            $sale_item = SaleItem::where('id', $request_data['id'])->first();

            if (!$sale_item) {
                return response()->json([
                    "success" => false,
                    "message" => "No sale item found",
                    "data" => null
                ], Response::HTTP_NOT_FOUND);
            }

            if ($sale_item->is_default == 1 && !$request->user()->hasRole('superadmin')) {
                return response()->json([
                    'success' => false,
                    "message" => "You cannot update a default sale item",
                    "data" => null
                ], Response::HTTP_FORBIDDEN);
            }

            if (!$request->user()->hasRole('superadmin') && $sale_item->business_id !== $request->user()->business_id) {
                return response()->json([
                    'success' => false,
                    "message" => "You cannot update a sale item of another business",
                    "data" => null
                ], Response::HTTP_FORBIDDEN);
            }

            $sale_item->update(
                collect($request_data)->only([
                    'name',
                    'description',
                    'price',
                ])->toArray()
            );

            return response()->json([
                "success" => true,
                "message" => "Sale item updated successfully",
                "data" => $sale_item
            ], Response::HTTP_OK);
        });
    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v1.0/sale-items/{perPage}",
 *      operationId="getSaleItems",
 *      tags={"property_management.sale_item_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },

 *              @OA\Parameter(
 *         name="perPage",
 *         in="path",
 *         description="perPage",
 *         required=true,
 *  example="6"
 *      ),
 *      * *  @OA\Parameter(
* name="start_date",
* in="query",
* description="start_date",
* required=true,
* example="2019-06-29"
* ),
 * *  @OA\Parameter(
* name="end_date",
* in="query",
* description="end_date",
* required=true,
* example="2019-06-29"
* ),
 * *  @OA\Parameter(
* name="order_by",
* in="query",
* description="order_by",
* required=true,
* example="ASC"
* ),
 * *  @OA\Parameter(
* name="search_key",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
* ),
 *      summary="This method is to get sale items ",
 *      description="This method is to get sale items",
 *

 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *       @OA\JsonContent(),
 *       ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthenticated",
 * @OA\JsonContent(),
 *      ),
 *        @OA\Response(
 *          response=422,
 *          description="Unprocesseble Content",
 *    @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden",
 *   @OA\JsonContent()
 * ),
 *  * @OA\Response(
 *      response=400,
 *      description="Bad Request",
 *   *@OA\JsonContent()
 *   ),
 * @OA\Response(
 *      response=404,
 *      description="not found",
 *   *@OA\JsonContent()
 *   )
 *      )
 *     )
 */

public function getSaleItems($perPage, Request $request)
{
    try {
        $this->storeActivity($request,"");

        $request->merge(['per_page' => $perPage]);
        $sale_itemQuery = SaleItem::saleItemQuery();
        $sale_items = retrieve_data($sale_itemQuery, "id", "sale_items");

        return response()->json([
            "success" => true,
            "message" => "Successfully fetched sale items",
            "meta" => $sale_items['meta'],
            "data" => $sale_items['data']
        ], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 * @OA\Get(
 *      path="/v1.0/sale-items",
 *      operationId="getSaleItemsList",
 *      tags={"property_management.sale_item_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      @OA\Parameter(
 *         name="start_date",
 *         in="query",
 *         description="start_date",
 *         required=false,
 *         example="2019-06-29"
 *      ),
 *      @OA\Parameter(
 *         name="end_date",
 *         in="query",
 *         description="end_date",
 *         required=false,
 *         example="2019-06-29"
 *      ),
 *      @OA\Parameter(
 *         name="order_by",
 *         in="query",
 *         description="order_by",
 *         required=false,
 *         example="ASC"
 *      ),
 *      @OA\Parameter(
 *         name="search_key",
 *         in="query",
 *         description="search_key",
 *         required=false,
 *         example="search_key"
 *      ),
 *      @OA\Parameter(
 *         name="per_page",
 *         in="query",
 *         description="per_page",
 *         required=false,
 *         example="10"
 *      ),
 *      summary="This method is to get sale items list without path param",
 *      description="This method is to get sale items list without path param",
 *
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *          @OA\JsonContent()
 *       )
 *     )
 */
public function getSaleItemsList(Request $request)
{
    try {
        $this->storeActivity($request,"");
        $sale_itemQuery = SaleItem::saleItemQuery();
        $sale_items = retrieve_data($sale_itemQuery, "id", "sale_items");

        return response()->json([
            "success" => true,
            "message" => "Successfully fetched sale items",
            "meta" => $sale_items['meta'],
            "data" => $sale_items['data']
        ], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}



/**
 *
 * @OA\Get(
 *      path="/v1.0/sale-items/get/single/{id}",
 *      operationId="getSaleItemById",
 *      tags={"property_management.sale_item_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },

 *              @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="id",
 *         required=true,
 *  example="1"
 *      ),

 *      summary="This method is to get sale item  by id",
 *      description="This method is to get sale item by id",
 *

 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *       @OA\JsonContent(),
 *       ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthenticated",
 * @OA\JsonContent(),
 *      ),
 *        @OA\Response(
 *          response=422,
 *          description="Unprocesseble Content",
 *    @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden",
 *   @OA\JsonContent()
 * ),
 *  * @OA\Response(
 *      response=400,
 *      description="Bad Request",
 *   *@OA\JsonContent()
 *   ),
 * @OA\Response(
 *      response=404,
 *      description="not found",
 *   *@OA\JsonContent()
 *   )
 *      )
 *     )
 */

public function getSaleItemById($id, Request $request)
{
    try {
        $this->storeActivity($request,"");


        $sale_item = SaleItem::saleItemQuery()
        ->where("generated_id", $id)
        ->first();

        if(!$sale_item) {
            return response()->json([
                "success" => false,
                "message" => "No sale item found",
                "data" => null
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            "success" => true,
            "message" => "Successfully fetched sale item",
            "data" => $sale_item
        ], Response::HTTP_OK);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}










/**
 *
 *     @OA\Delete(
 *      path="/v1.0/sale-items/{id}",
 *      operationId="deleteSaleItemById",
 *      tags={"property_management.sale_item_management"},
 *       security={
 *           {"bearerAuth": {}},
 *           {"pin": {}}
 *       },
 *              @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="id",
 *         required=true,
 *  example="1"
 *      ),
 *      summary="This method is to delete sale item  by id",
 *      description="This method is to delete sale item  by id",
 *

 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *       @OA\JsonContent(),
 *       ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthenticated",
 * @OA\JsonContent(),
 *      ),
 *        @OA\Response(
 *          response=422,
 *          description="Unprocesseble Content",
 *    @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden",
 *   @OA\JsonContent()
 * ),
 *  * @OA\Response(
 *      response=400,
 *      description="Bad Request",
 *   *@OA\JsonContent()
 *   ),
 * @OA\Response(
 *      response=404,
 *      description="not found",
 *   *@OA\JsonContent()
 *   )
 *      )
 *     )
 */

public function deleteSaleItemById($id, Request $request)
{

    try {
        $this->storeActivity($request,"");

        $business = Business::where([
            "owner_id" => $request->user()->id
          ])->first();

        if(!$business) {
            return response()->json([
                "success" => false,
                "message" => "You don't have a valid business",
                "data" => null
            ], Response::HTTP_UNAUTHORIZED);
         }

         if(!($business->pin == $request->header("pin"))) {
             return response()->json([
                 "success" => false,
                 "message" => "Invalid pin",
                 "data" => null
             ], Response::HTTP_UNAUTHORIZED);
         }

        $sale_item = SaleItem::where('id', $id)->first();

        if(!$sale_item) {
             return response()->json([
                 "success" => false,
                 "message" => "No sale item found",
                 "data" => null
             ], Response::HTTP_NOT_FOUND);
        }

        if ($sale_item->is_default == 1 && !$request->user()->hasRole('superadmin')) {
            return response()->json([
                'success' => false,
                "message" => "You cannot delete a default sale item",
                "data" => null
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$request->user()->hasRole('superadmin') && $sale_item->business_id !== $request->user()->business_id) {
            return response()->json([
                'success' => false,
                "message" => "You cannot delete a sale item of another business",
                "data" => null
            ], Response::HTTP_FORBIDDEN);
        }

        $sale_item->delete();

        return response()->json([
            "success" => true,
            "message" => "Sale item deleted successfully",
            "data" => null
        ], Response::HTTP_OK);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

}
