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
        $this->storeActivity($request,"");
        return DB::transaction(function () use ($request) {



            $insertableData = $request->validated();
            $insertableData["created_by"] = $request->user()->id;
            $sale_item =  SaleItem::create($insertableData);
            $sale_item->generated_id = Str::random(4) . $sale_item->id . Str::random(4);
            $sale_item->save();


            return response($sale_item, 201);





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

            $updatableData = $request->validated();

            // $affiliationPrev = SaleItem::where([
            //     "id" => $updatableData["id"]
            //    ]);

            //    if(!$request->user()->hasRole('superadmin')) {
            //     $affiliationPrev =    $affiliationPrev->where([
            //         "created_by" =>$request->user()->id
            //     ]);
            // }
            // $affiliationPrev = $affiliationPrev->first();
            //  if(!$affiliationPrev) {
            //         return response()->json([
            //            "message" => "you did not create this affiliation."
            //         ],404);
            //  }




            $sale_item  =  tap(SaleItem::where([
                "id" => $updatableData["id"],
                "created_by" => $request->user()->id
                ]))->update(
                collect($updatableData)->only([
                    'name',
    'description',
    'price',

                ])->toArray()
            )
                // ->with("somthing")

                ->first();

            return response($sale_item, 200);
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

        // $automobilesQuery = AutomobileMake::with("makes");

        $sale_itemQuery =  SaleItem::where(["created_by" => $request->user()->id]);

        if (!empty($request->search_key)) {
            $sale_itemQuery = $sale_itemQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                $query->where("name", "like", "%" . $term . "%");
                $query->orWhere("description", "like", "%" . $term . "%");
                $query->orWhere("price", "like", "%" . $term . "%");
            });
        }

        if (!empty($request->start_date)) {
            $sale_itemQuery = $sale_itemQuery->where('created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $sale_itemQuery = $sale_itemQuery->where('created_at', "<=", $request->end_date);
        }

        $sale_items = $sale_itemQuery->orderBy("id",$request->order_by)->paginate($perPage);

        return response()->json($sale_items, 200);
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


        $sale_item = SaleItem::where([
            "generated_id" => $id,
            "created_by" => $request->user()->id
        ])
        ->first();

        if(!$sale_item) {
     return response()->json([
"message" => "no sale item found"
],404);
        }


        return response()->json($sale_item, 200);
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
             "message" => "you don't have a valid business"
            ],401);
         }
         if(!($business->pin == $request->header("pin"))) {
             return response()->json([
                 "message" => "invalid pin"
                ],401);
         }

        $sale_item = SaleItem::where([
            "id" => $id,
            "created_by" => $request->user()->id
        ])
        ->first();

        if(!$sale_item) {
     return response()->json([
"message" => "no sale item  found"
],404);
        }
        $sale_item->delete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

}
