<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\TenantCreateRequest;
use App\Http\Requests\TenantUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Tenant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantController extends Controller
{
    use ErrorUtil, UserActivityUtil;
    /**
    *
 * @OA\Post(
 *      path="/v1.0/tenant-image",
 *      operationId="createTenantImage",
 *      tags={"property_management.tenant_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store tenant logo",
 *      description="This method is to store tenant logo",
 *
*  @OA\RequestBody(
    *   * @OA\MediaType(
*     mediaType="multipart/form-data",
*     @OA\Schema(
*         required={"image"},
*         @OA\Property(
*             description="image to upload",
*             property="image",
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

public function createTenantImage(ImageUploadRequest $request)
{
    try{
        $this->storeActivity($request,"");

        $insertableData = $request->validated();

        $location =  config("setup-config.tenant_image");

        $new_file_name = time() . '_' . $insertableData["image"]->getClientOriginalName();

        $insertableData["image"]->move(public_path($location), $new_file_name);


        return response()->json(["image" => $new_file_name,"location" => $location,"full_location"=>("/".$location."/".$new_file_name)], 200);


    } catch(Exception $e){

        return $this->sendError($e,500,$request);
    }
}


/**
 *
 * @OA\Post(
 *      path="/v1.0/tenants",
 *      operationId="createTenant",
 *      tags={"property_management.tenant_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store tenant",
 *      description="This method is to store tenant",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"name","description","logo"},
 *  *             @OA\Property(property="image", type="string", format="string",example="image.jpg"),
  *             @OA\Property(property="first_Name", type="string", format="string",example="Rifat"),
 *            @OA\Property(property="last_Name", type="string", format="string",example="Al"),
 *            @OA\Property(property="email", type="string", format="string",example="rifatalashwad0@gmail.com"),
 *  * *  @OA\Property(property="phone", type="string", format="boolean",example="01771034383"),
 *  * *  @OA\Property(property="address_line_1", type="string", format="boolean",example="dhaka"),
 *  * *  @OA\Property(property="address_line_2", type="string", format="boolean",example="dinajpur"),
 *  * *  @OA\Property(property="country", type="string", format="boolean",example="Bangladesh"),
 *  * *  @OA\Property(property="city", type="string", format="boolean",example="Dhaka"),
 *  * *  @OA\Property(property="postcode", type="string", format="boolean",example="1207"),
 *     *  * *  @OA\Property(property="lat", type="string", format="boolean",example="1207"),
 *     *  * *  @OA\Property(property="long", type="string", format="boolean",example="1207"),
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

public function createTenant(TenantCreateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return DB::transaction(function () use ($request) {



            $insertableData = $request->validated();
            $insertableData["created_by"] = $request->user()->id;
            $tenant =  Tenant::create($insertableData);



            return response($tenant, 201);





        });




    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 * @OA\Put(
 *      path="/v1.0/tenants",
 *      operationId="updateTenant",
 *      tags={"property_management.tenant_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to update tenant",
 *      description="This method is to update tenant",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"id","name","description","logo"},
 *     *             @OA\Property(property="id", type="number", format="number",example="1"),
 *      *  *             @OA\Property(property="image", type="string", format="string",example="image.jpg"),
 *             @OA\Property(property="first_Name", type="string", format="string",example="Rifat"),
 *            @OA\Property(property="last_Name", type="string", format="string",example="Al"),
 *            @OA\Property(property="email", type="string", format="string",example="rifatalashwad0@gmail.com"),
 *  * *  @OA\Property(property="phone", type="string", format="boolean",example="01771034383"),
 *  * *  @OA\Property(property="address_line_1", type="string", format="boolean",example="dhaka"),
 *  * *  @OA\Property(property="address_line_2", type="string", format="boolean",example="dinajpur"),
 *  * *  @OA\Property(property="country", type="string", format="boolean",example="Bangladesh"),
 *  * *  @OA\Property(property="city", type="string", format="boolean",example="Dhaka"),
 *  * *  @OA\Property(property="postcode", type="string", format="boolean",example="1207"),
 *     *  * *  @OA\Property(property="lat", type="string", format="boolean",example="1207"),
 *     *  * *  @OA\Property(property="long", type="string", format="boolean",example="1207"),
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

public function updateTenant(TenantUpdateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return  DB::transaction(function () use ($request) {

            $updatableData = $request->validated();

            // $affiliationPrev = Tenants::where([
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




            $tenant  =  tap(Tenant::where(["id" => $updatableData["id"]]))->update(
                collect($updatableData)->only([
                    "name",
                    "description",
                    "logo"
                ])->toArray()
            )
                // ->with("somthing")

                ->first();

            return response($tenant, 200);
        });
    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v1.0/tenants/{perPage}",
 *      operationId="getTenants",
 *      tags={"property_management.tenant_management"},
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
 *      summary="This method is to get tenants ",
 *      description="This method is to get tenants",
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

public function getTenants($perPage, Request $request)
{
    try {
        $this->storeActivity($request,"");

        // $automobilesQuery = AutomobileMake::with("makes");

        $tenantQuery = new Tenant();

        if (!empty($request->search_key)) {
            $tenantQuery = $tenantQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                $query->where("name", "like", "%" . $term . "%");
            });
        }

        if (!empty($request->start_date)) {
            $tenantQuery = $tenantQuery->where('created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $tenantQuery = $tenantQuery->where('created_at', "<=", $request->end_date);
        }

        $tenants = $tenantQuery->orderByDesc("id")->paginate($perPage);

        return response()->json($tenants, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}



/**
 *
 * @OA\Get(
 *      path="/v1.0/tenants/get/single/{id}",
 *      operationId="getTenantById",
 *      tags={"property_management.tenant_management"},
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

 *      summary="This method is to get tenant by id",
 *      description="This method is to get tenant by id",
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

public function getTenantById($id, Request $request)
{
    try {
        $this->storeActivity($request,"");


        $tenant = Tenant::where([
            "id" => $id
        ])
        ->first();

        if(!$tenant) {
     return response()->json([
"message" => "no tenant found"
]);
        }


        return response()->json($tenant, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}










/**
 *
 *     @OA\Delete(
 *      path="/v1.0/tenants/{id}",
 *      operationId="deleteTenantById",
 *      tags={"property_management.tenant_management"},
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
 *      summary="This method is to delete tenant by id",
 *      description="This method is to delete tenant by id",
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

public function deleteTenantById($id, Request $request)
{

    try {
        $this->storeActivity($request,"");



            $tenantQuery =   Tenant::where([
                "id" => $id
               ]);
               if(!$request->user()->hasRole('superadmin')) {
                $tenantQuery =    $tenantQuery->where([
                    "created_by" =>$request->user()->id
                ]);
            }

            $tenant = $tenantQuery->first();

            $tenant->delete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}
}
