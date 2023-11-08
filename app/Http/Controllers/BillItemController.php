<?php

namespace App\Http\Controllers;

use App\Http\Requests\BillItemCreateRequest;
use App\Http\Requests\BillItemUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\BillItem;
use App\Models\Business;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BillItemController extends Controller
{
    use ErrorUtil, UserActivityUtil;


    /**
     *
     * @OA\Post(
     *      path="/v1.0/bill-items",
     *      operationId="createBillItem",
     *      tags={"property_management.bill_item_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store bill item ",
     *      description="This method is to store bill item ",
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

    public function createBillItem(BillItemCreateRequest $request)
    {
        try {
            $this->storeActivity($request,"");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('bill_item_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }


                $insertableData = $request->validated();
                $insertableData["created_by"] = $request->user()->id;
                $bill_item =  BillItem::create($insertableData);
                $bill_item->generated_id = Str::random(4) . $bill_item->id . Str::random(4);
                $bill_item->save();


                return response($bill_item, 201);





            });




        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }

    /**
     *
     * @OA\Put(
     *      path="/v1.0/bill-items",
     *      operationId="updateBillItem",
     *      tags={"property_management.bill_item_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update bill item ",
     *      description="This method is to update bill item ",
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

    public function updateBillItem(BillItemUpdateRequest $request)
    {
        try {
            $this->storeActivity($request,"");
            return  DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('bill_item_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                $updatableData = $request->validated();

                // $affiliationPrev = BillItem::where([
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




                $bill_item  =  tap(BillItem::where([
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

                return response($bill_item, 200);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500,$request);
        }
    }
    /**
     *
     * @OA\Get(
     *      path="/v1.0/bill-items/{perPage}",
     *      operationId="getBillItems",
     *      tags={"property_management.bill_item_management"},
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
     *      summary="This method is to get bill items ",
     *      description="This method is to get bill items",
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

    public function getBillItems($perPage, Request $request)
    {
        try {
            $this->storeActivity($request,"");
            if (!$request->user()->hasPermissionTo('bill_item_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            // $automobilesQuery = AutomobileMake::with("makes");

            $bill_itemQuery =  BillItem::leftJoin('business_defaults', function($join) use($request) {
                $join->on('bill_items.id', '=', 'business_defaults.entity_id')
                     ->where('business_defaults.entity_type', '=', 'bill_item')
                     ->where('business_defaults.business_owner_id', '=', $request->user()->id);
            });

            if (!empty($request->search_key)) {
                $bill_itemQuery = $bill_itemQuery->where(function ($query) use ($request) {
                    $term = $request->search_key;
                    $query->where("bill_items.name", "like", "%" . $term . "%");
                    $query->orWhere("bill_items.description", "like", "%" . $term . "%");
                    $query->orWhere("bill_items.price", "like", "%" . $term . "%");
                });
            }

            if (!empty($request->start_date)) {
                $bill_itemQuery = $bill_itemQuery->where('bill_items.created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $bill_itemQuery = $bill_itemQuery->where('bill_items.created_at', "<=", $request->end_date);
            }

            $bill_items = $bill_itemQuery->orderBy("bill_items.id",$request->order_by)->select("bill_items.*",    DB::raw('CASE WHEN business_defaults.id IS NOT NULL THEN 1 ELSE 0 END AS is_default'))->paginate($perPage);

            return response()->json($bill_items, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }



    /**
     *
     * @OA\Get(
     *      path="/v1.0/bill-items/get/single/{id}",
     *      operationId="getBillItemById",
     *      tags={"property_management.bill_item_management"},
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

     *      summary="This method is to get bill item  by id",
     *      description="This method is to get bill item by id",
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

    public function getBillItemById($id, Request $request)
    {
        try {
            $this->storeActivity($request,"");
            if (!$request->user()->hasPermissionTo('bill_item_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $bill_item = BillItem::where([
                "generated_id" => $id,
                // "created_by" => $request->user()->id
            ])
            // ->orderBy(,"ASC")
            ->first();

            if(!$bill_item) {
         return response()->json([
    "message" => "no bill item found"
    ],404);
            }


            return response()->json($bill_item, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }










    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/bill-items/{id}",
     *      operationId="deleteBillItemById",
     *      tags={"property_management.bill_item_management"},
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
     *      summary="This method is to delete bill item  by id",
     *      description="This method is to delete bill item  by id",
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

    public function deleteBillItemById($id, Request $request)
    {

        try {
            $this->storeActivity($request,"");

            if (!$request->user()->hasPermissionTo('bill_item_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }
            if (!Hash::check($request->header("password"), $request->user()->password)) {
                return response()->json([
                    "message" => "Invalid password"
                ], 401);
            }

            $bill_item = BillItem::where([
                "id" => $id,
                "created_by" => $request->user()->id
            ])
            ->first();

            if(!$bill_item) {
         return response()->json([
    "message" => "no bill item  found"
    ],404);
            }
            $bill_item->delete();

            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }

}
