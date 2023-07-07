<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Http\Requests\MultipleImageUploadRequest;
use App\Http\Requests\RepairCreateRequest;
use App\Http\Requests\RepairUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Repair;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $insertableData = $request->validated();

        $location =  config("setup-config.repair_receipt_file");

        $new_file_name = time() . '_' . $insertableData["file"]->getClientOriginalName();

        $insertableData["file"]->move(public_path($location), $new_file_name);


        return response()->json(["file" => $new_file_name,"location" => $location,"full_location"=>("/".$location."/".$new_file_name)], 200);


    } catch(Exception $e){

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

            $insertableData = $request->validated();

            $location =  config("setup-config.repair_image");

            $images = [];
            if(!empty($insertableData["images"])) {
                foreach($insertableData["images"] as $image){
                    $new_file_name = time() . '_' . $image->getClientOriginalName();
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
 *            @OA\Property(property="receipt", type="string", format="string",example="receipt"),
 *  * *  @OA\Property(property="price", type="string", format="string",example="10"),
 *  * *  @OA\Property(property="create_date", type="string", format="string",example="12/12/2012"),
 *  * *  @OA\Property(property="images", type="string", format="array",example={"a.jpg","b.jpg","c.jpg"}),

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



            $insertableData = $request->validated();
            $insertableData["created_by"] = $request->user()->id;
            $repair =  Repair::create($insertableData);

            if(!$repair) {
                throw new Exception("something went wrong");
            }

            $repair->repair_images()->createMany(
                collect($insertableData["images"])->map(function ($image) {
                    return [
                        'image' => $image,
                    ];
                })
            );


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
 *            @OA\Property(property="receipt", type="string", format="string",example="receipt"),
 *  * *  @OA\Property(property="price", type="string", format="string",example="10"),
 *  * *  @OA\Property(property="create_date", type="string", format="string",example="12/12/2012"),
 *  * *  @OA\Property(property="images", type="string", format="array",example={"a.jpg","b.jpg","c.jpg"}),

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

            $updatableData = $request->validated();





            $repair  =  tap(Repair::where(["id" => $updatableData["id"],"created_by" => $request->user()->id]))->update(
                collect($updatableData)->only([
                    'property_id',
                    'repair_category_id',
                    'item_description',
                    'receipt',
                    'price',
                    'create_date',
                ])->toArray()
            )
                // ->with("somthing")

                ->first();

                if(!$repair) {
                    throw new Exception("something went wrong");
                }

                $repair->repair_images()->createMany(
                    collect($updatableData["images"])->map(function ($image) {
                        return [
                            'image' => $image,
                        ];
                    })
                );



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
* name="search_key",
* in="query",
* description="search_key",
* required=true,
* example="search_key"
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

        // $automobilesQuery = AutomobileMake::with("makes");

        $repairQuery =  Repair::with("repair_category")->where(["created_by" => $request->user()->id]);

        if (!empty($request->search_key)) {
            $repairQuery = $repairQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                $query->where("name", "like", "%" . $term . "%");
            });
        }

        if (!empty($request->start_date)) {
            $repairQuery = $repairQuery->where('created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $repairQuery = $repairQuery->where('created_at', "<=", $request->end_date);
        }

        $repairs = $repairQuery->orderByDesc("id")->paginate($perPage);

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


        $repair = Repair::with("repair_category")
        ->where([
            "id" => $id,
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
 *           {"bearerAuth": {}}
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
