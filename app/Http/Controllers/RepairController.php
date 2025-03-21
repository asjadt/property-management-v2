<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\MultipleFileUploadRequest;
use App\Http\Requests\MultipleImageUploadRequest;
use App\Http\Requests\RepairCreateRequest;
use App\Http\Requests\RepairUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\Repair;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RepairController extends Controller
{
    use ErrorUtil, UserActivityUtil;

  /**
    *
 * @OA\Post(
 *      path="/v1.0/repair-receipts-file",
 *      operationId="createRepairReceiptFile",
 *      tags={"property_management.repair_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store reciept image",
 *      description="This method is to store reciept image",
 *
*  @OA\RequestBody(
    *   * @OA\MediaType(
*     mediaType="multipart/form-data",
*     @OA\Schema(
*         required={"file"},
*         @OA\Property(
*             description="file to upload",
*             property="file",
*             type="file",
*             collectionFormat="multi",
*         )
*     )
* )



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

public function createRepairReceiptFile(FileUploadRequest $request)
{
    try{
        $this->storeActivity($request,"");

        $request_data = $request->validated();

        $location =  config("setup-config.repair_receipt_file");

        $new_file_name = time() . '_' . str_replace(' ', '_', $request_data["file"]->getClientOriginalName());


        $request_data["file"]->move(public_path($location), $new_file_name);


        return response()->json(["file" => $new_file_name,"location" => $location,"full_location"=>("/".$location."/".$new_file_name)], 200);


    } catch(Exception $e){

        return $this->sendError($e,500,$request);
    }
}
 /**
        *
     * @OA\Post(
     *      path="/v1.0/repair-receipts-file/multiple",
     *      operationId="createRepairReceiptFileMultiple",
     *      tags={"property_management.repair_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *      summary="This method is to store multiple repair file",
     *      description="This method is to store multiple repair file",
     *
   *  @OA\RequestBody(
        *   * @OA\MediaType(
*     mediaType="multipart/form-data",
*     @OA\Schema(
*         required={"files[]"},
*         @OA\Property(
*             description="array of files to upload",
*             property="files[]",
*             type="array",
*             @OA\Items(
*                 type="file"
*             ),
*             collectionFormat="multi",
*         )
*     )
* )



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

     public function createRepairReceiptFileMultiple(MultipleFileUploadRequest $request)
     {
         try{
             $this->storeActivity($request,"");

             $request_data = $request->validated();

             $location =  config("setup-config.repair_receipt_file");

             $files = [];
             if(!empty($request_data["files"])) {
                 foreach($request_data["files"] as $file){
                     $new_file_name = time() . '_' . $file->getClientOriginalName();
                     $new_file_name = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
                     $file->move(public_path($location), $new_file_name);

                     array_push($files,("/".$location."/".$new_file_name));


                 }
             }


             return response()->json(["images" => $files], 201);


         } catch(Exception $e){
             error_log($e->getMessage());
         return $this->sendError($e,500,$request);
         }
     }
 /**
        *
     * @OA\Post(
     *      path="/v1.0/repair-images/multiple",
     *      operationId="createRepairImageMultiple",
     *      tags={"property_management.repair_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *      summary="This method is to store multiple repair request",
     *      description="This method is to store multiple repair request",
     *
   *  @OA\RequestBody(
        *   * @OA\MediaType(
*     mediaType="multipart/form-data",
*     @OA\Schema(
*         required={"images[]"},
*         @OA\Property(
*             description="array of images to upload",
*             property="images[]",
*             type="array",
*             @OA\Items(
*                 type="file"
*             ),
*             collectionFormat="multi",
*         )
*     )
* )



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

    public function createRepairImageMultiple(MultipleImageUploadRequest $request)
    {
        try{
            $this->storeActivity($request,"");

            $request_data = $request->validated();

            $location =  config("setup-config.repair_image");

            $images = [];
            if(!empty($request_data["images"])) {
                foreach($request_data["images"] as $image){
                    $new_file_name = time() . '_' . str_replace(' ', '_', $image->getClientOriginalName());
                    $image->move(public_path($location), $new_file_name);

                    array_push($images,("/".$location."/".$new_file_name));


                }
            }


            return response()->json(["images" => $images], 201);


        } catch(Exception $e){
            error_log($e->getMessage());
        return $this->sendError($e,500,$request);
        }
    }




/**
 *
 * @OA\Post(
 *      path="/v1.0/repairs",
 *      operationId="createRepair",
 *      tags={"property_management.repair_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store repair",
 *      description="This method is to store repair",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"name","description","logo"},
 *  *             @OA\Property(property="property_id", type="number", format="number",example="1"),
  *             @OA\Property(property="repair_category_id", type="string", format="string",example="1"),
 *            @OA\Property(property="item_description", type="string", format="string",example="item_description"),
 *
 *  *            @OA\Property(property="status", type="string", format="string",example="status"),
 *

 *  * *  @OA\Property(property="price", type="string", format="string",example="10"),
 *  * *  @OA\Property(property="create_date", type="string", format="string",example="2019-06-29"),
 *  * *  @OA\Property(property="images", type="string", format="array",example={"a.jpg","b.jpg","c.jpg"}),
 *  *  *  * *  @OA\Property(property="receipt", type="string", format="array",example={"a.jpg","b.jpg","c.jpg"}),

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

public function createRepair(RepairCreateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return DB::transaction(function () use ($request) {




            $request_data = $request->validated();
            $request_data["receipt"]   = json_encode($request_data["receipt"] );
            $request_data["created_by"] = $request->user()->id;
            $repair =  Repair::create($request_data);

            if(!$repair) {
                throw new Exception("something went wrong");
            }
            $repair->generated_id = Str::random(4) . $repair->id . Str::random(4);
            $repair->save();


    if(!empty($request_data["images"])) {
        $repair->repair_images()->createMany(
            collect($request_data["images"])->map(function ($image) {
                return [
                    'image' => $image,
                ];
            })
        );
    }


            $repair->load(["repair_category","property"]);

            return response($repair, 201);





        });




    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 * @OA\Put(
 *      path="/v1.0/repairs",
 *      operationId="updateRepair",
 *      tags={"property_management.repair_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to update repair",
 *      description="This method is to update repair",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"id","name","description","logo"},
 *     *             @OA\Property(property="id", type="number", format="number",example="1"),
 *  *             @OA\Property(property="property_id", type="number", format="number",example="1"),
  *             @OA\Property(property="repair_category_id", type="string", format="string",example="1"),
 *            @OA\Property(property="item_description", type="string", format="string",example="item_description"),
 *  *            @OA\Property(property="status", type="string", format="string",example="status"),
 *

 *  * *  @OA\Property(property="price", type="string", format="string",example="10"),
 *  * *  @OA\Property(property="create_date", type="string", format="string",example="2019-06-29"),
 *  * *  @OA\Property(property="images", type="string", format="array",example={"a.jpg","b.jpg","c.jpg"}),
 *  *  * *  @OA\Property(property="receipt", type="string", format="array",example={"a.jpg","b.jpg","c.jpg"}),

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

public function updateRepair(RepairUpdateRequest $request)
{
    try {
        $this->storeActivity($request,"");

        return  DB::transaction(function () use ($request) {


            $request_data = $request->validated();
            $request_data["receipt"]   = json_encode($request_data["receipt"] );




            $repair  =  tap(Repair::where(["id" => $request_data["id"],"created_by" => $request->user()->id]))->update(
                collect($request_data)->only([
                    'property_id',
                    'repair_category_id',
                    'item_description',
                    'status',
                    'receipt',
                    'price',
                    'create_date',
                ])->toArray()
            )
                 ->with("repair_category","property")

                ->first();

                if(!$repair) {
                    throw new Exception("something went wrong");
                }

                $repair->repair_images()->delete();
                if(!empty($request_data["images"])) {
                    $repair->repair_images()->createMany(
                        collect($request_data["images"])->map(function ($image) {
                            return [
                                'image' => $image,
                            ];
                        })
                    );
                }






            return response($repair, 200);
        });
    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v1.0/repairs/{perPage}",
 *      operationId="getRepairs",
 *      tags={"property_management.repair_management"},
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
 *
 *  * *      * *  @OA\Parameter(
* name="repair_id",
* in="query",
* description="repair_id",
* required=true,
* example="1"
* ),
 *
 * *      * *  @OA\Parameter(
* name="property_id",
* in="query",
* description="property_id",
* required=true,
* example="1"
* ),
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
 * *  @OA\Parameter(
* name="repair_category",
* in="query",
* description="repair_category",
* required=true,
* example="repair_category"
* ),

 *      summary="This method is to get repairs ",
 *      description="This method is to get repairs",
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

public function getRepairs($perPage, Request $request)
{
    try {
        $this->storeActivity($request,"");




        $repairQuery =  Repair::with("repair_category","property")
        ->leftJoin('invoice_items', 'invoice_items.repair_id', '=', 'repairs.id')
        ->leftJoin('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
        ->leftJoin('properties', 'properties.id', '=', 'repairs.property_id')
        ->leftJoin('repair_categories', 'repair_categories.id', '=', 'repairs.repair_category_id')
        ->where(["repairs.created_by" => $request->user()->id])

        ;

        if (!empty($request->invoice_not_issued)) {
            if($request->invoice_not_issued == 1) {
               $repairQuery = $repairQuery->whereNull('invoice_items.repair_id');
            }

        }
        if (!empty($request->search_key)) {
            $repairQuery = $repairQuery->where(function ($query) use ($request) {
                $term = $request->search_key;


                $query->orWhere("repairs.item_description", "like", "%" . $term . "%");
                // $query->where("properties.reference_no", "like", "%" . $term . "%");
                // $query->orWhere("properties.address", "like", "%" . $term . "%");
                // $query->orWhere("repair_categories.name", "like", "%" . $term . "%");
      // $query->orWhere("repairs.item_description", "like", "%" . $term . "%");
                // $query->orWhere("properties.type", "like", "%" . $term . "%");

            });
        }
        if (!empty($request->repair_category)) {
            $repairQuery = $repairQuery->where('repair_categories.name', $request->repair_category);
        }

        if (!empty($request->status)) {
            $repairQuery = $repairQuery->where('repairs.status', $request->status);
        }



        if (!empty($request->start_date)) {
            $repairQuery = $repairQuery->where('repairs.created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $repairQuery = $repairQuery->where('repairs.created_at', "<=", $request->end_date);
        }
        if (!empty($request->property_id)) {
            $repairQuery = $repairQuery->where('repairs.property_id', $request->property_id);
        }


        $repairs = $repairQuery
        ->select("repairs.*",
        DB::raw('CASE
        WHEN (SELECT COUNT(invoice_items.id) FROM invoice_items WHERE invoice_items.repair_id = repairs.id) = 0 THEN 0
        ELSE 1
    END AS is_invoice_issued'),
    DB::raw('CASE
    WHEN invoices.status = "paid" THEN "paid"
    WHEN invoices.status = "partial" THEN "partial"
    ELSE "due"
    END AS payment_status')



        )
        ->groupBy("repairs.id")
        ->
        orderBy("repairs.id",$request->order_by)
        ->paginate($perPage);

        return response()->json($repairs, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}



/**
 *
 * @OA\Get(
 *      path="/v1.0/repairs/get/single/{id}",
 *      operationId="getRepairById",
 *      tags={"property_management.repair_management"},
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

 *      summary="This method is to get repair by id",
 *      description="This method is to get repair by id",
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

public function getRepairById($id, Request $request)
{
    try {
        $this->storeActivity($request,"");


        $repair = Repair::with("repair_category","property","repair_images")
        ->where([
            "generated_id" => $id,
            "created_by" => $request->user()->id
        ])
        ->first();

        if(!$repair) {
     return response()->json([
"message" => "no repair found"
],404);
        }


        return response()->json($repair, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}










/**
 *
 *     @OA\Delete(
 *      path="/v1.0/repairs/{id}",
 *      operationId="deleteRepairById",
 *      tags={"property_management.repair_management"},
 *       security={
 *           {"bearerAuth": {}},
 *            {"pin": {}}
 *       },
 *              @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="id",
 *         required=true,
 *  example="1"
 *      ),
 *      summary="This method is to delete repair by id",
 *      description="This method is to delete repair by id",
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

public function deleteRepairById($id, Request $request)
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



        $repair = Repair::where([
            "id" => $id,
            "created_by" => $request->user()->id
        ])
        ->first();

        if(!$repair) {
     return response()->json([
"message" => "no repair found"
],404);
        }
        $repair->delete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

}
