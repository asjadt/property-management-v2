<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\TenantCreateRequest;
use App\Http\Requests\TenantUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\Tenant;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        $new_file_name = time() . '_' . str_replace(' ', '_', $insertableData["image"]->getClientOriginalName());

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

            // for($i=0;$i<500;$i++) {
            //     $tenant =  Tenant::create([
            //         'first_Name'=> $insertableData["first_Name"] . Str::random(4),
            //         'last_Name'=> $insertableData["last_Name"] . Str::random(4),
            //         'phone'=> $insertableData["phone"] . Str::random(4),
            //         'image',
            //         'address_line_1'=>$insertableData["address_line_1"] . Str::random(4),
            //         'address_line_2',
            //         'country'=>$insertableData["country"] . Str::random(4),
            //         'city'=>$insertableData["city"] . Str::random(4),
            //         'postcode'=>$insertableData["postcode"] . Str::random(4),
            //         "lat"=>$insertableData["lat"] . Str::random(4),
            //         "long"=>$insertableData["long"] . Str::random(4),
            //         'email'=>$insertableData["email"] . Str::random(4),
            //         "created_by"=>$request->user()->id,
            //          'is_active'=>1
            //     ]);
            //     $tenant->generated_id = Str::random(4) . $tenant->id . Str::random(4);
            //     $tenant->save();
            // }

            if(!$tenant) {
                throw new Exception("something went wrong");
            }

            $tenant->generated_id = Str::random(4) . $tenant->id . Str::random(4);
            $tenant->save();

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




            $tenant  =  tap(Tenant::where(["id" => $updatableData["id"], "created_by" => $request->user()->id]))->update(
                collect($updatableData)->only([
                    'first_Name',
        'last_Name',
        'phone',
        'image',
        'address_line_1',
        'address_line_2',
        'country',
        'city',
        'postcode',
        "lat",
        "long",
        'email',
                ])->toArray()
            )
                // ->with("somthing")

                ->first();
                if(!$tenant) {
                    throw new Exception("something went wrong");
                }


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
* name="property_id",
* in="query",
* description="property_id",
* required=true,
* example="1"
* ),
*  @OA\Parameter(
*      name="property_ids[]",
*      in="query",
*      description="property_ids",
*      required=true,
*      example="1,2"
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
        $currentDate = Carbon::now();
        $endDate = $currentDate->copy()->addDays(15);


        $tenantQuery =  Tenant::leftJoin('property_tenants', 'tenants.id', '=', 'property_tenants.tenant_id')
        ->leftJoin('properties', 'property_tenants.property_id', '=', 'properties.id')
        ->where([
            "tenants.created_by" => $request->user()->id
        ]);

        if (!empty($request->search_key)) {
            $tenantQuery = $tenantQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                // $query->where("properties.name", "like", "%" . $term . "%");
                $query->orWhere("tenants.first_Name", "like", "%" . $term . "%");
                $query->orWhere("tenants.last_Name", "like", "%" . $term . "%");
                $query->orWhere("tenants.email", "like", "%" . $term . "%");
                // $query->orWhere("tenants.address_line_1", "like", "%" . $term . "%");
                // $query->orWhere("tenants.address_line_2", "like", "%" . $term . "%");



                // $query->orWhere("tenants.phone", "like", "%" . $term . "%");
                // $query->orWhere("tenants.country", "like", "%" . $term . "%");
                // $query->orWhere("tenants.city", "like", "%" . $term . "%");
                // $query->orWhere("tenants.postcode", "like", "%" . $term . "%");






            });
        }
        if(!empty($request->property_id)){
            $tenantQuery = $tenantQuery->where('properties.id',$request->property_id);
        }
        if(!empty($request->property_ids)) {
            $null_filter = collect(array_filter($request->property_ids))->values();
        $property_ids =  $null_filter->all();
            if(count($property_ids)) {
                $tenantQuery =   $tenantQuery->whereIn("properties.id",$property_ids);
            }

        }

        if (!empty($request->start_date)) {
            $tenantQuery = $tenantQuery->where('tenants.created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $tenantQuery = $tenantQuery->where('tenants.created_at', "<=", $request->end_date);
        }



        $tenants = $tenantQuery
        ->groupBy("tenants.id")

        ->select(
            "tenants.*",
            "properties.name as property_name",
            DB::raw('
         COALESCE(
             (SELECT COUNT(property_tenants.tenant_id) FROM property_tenants WHERE property_tenants.tenant_id = tenants.id),
             0
         ) AS total_properties
         '),

         DB::raw(

            '
         COALESCE(
             (SELECT SUM(invoices.total_amount) FROM invoices WHERE invoices.tenant_id = tenants.id),
             0
         ) AS total_amount
         '

         ),
         DB::raw('
         COALESCE(
             (SELECT COUNT(invoices.id) FROM invoices WHERE invoices.tenant_id = tenants.id),
             0
         ) AS total_invoices
         '),
         DB::raw(
            '
         COALESCE(
             (SELECT SUM(invoice_payments.amount) FROM invoices
             LEFT JOIN
                invoice_payments ON invoices.id = invoice_payments.invoice_id
             WHERE invoices.tenant_id = tenants.id),
             0
         ) AS total_paid
         '
         ),
         DB::raw(
            '
            COALESCE(
            COALESCE(
                (SELECT SUM(invoices.total_amount) FROM invoices WHERE invoices.tenant_id = tenants.id),
                0
            )
            -
            COALESCE(
                (SELECT SUM(invoice_payments.amount) FROM invoices
                LEFT JOIN
                   invoice_payments ON invoices.id = invoice_payments.invoice_id
                WHERE invoices.tenant_id = tenants.id),
                0
            )
         )
         as total_due

         '
            ),

            DB::raw(
                '
                COALESCE(
                COALESCE(
                    (SELECT SUM(invoices.total_amount) FROM invoices
                    WHERE  invoices.tenant_id = tenants.id
                    AND invoices.due_date >= "' . $currentDate . '"
                    AND invoices.due_date <= "' . $endDate . '"

                ),
                    0
                )
                -
                COALESCE(
                    (SELECT SUM(invoice_payments.amount) FROM invoices
                    LEFT JOIN
                       invoice_payments ON invoices.id = invoice_payments.invoice_id
                    WHERE invoices.tenant_id = tenants.id
                    AND invoices.due_date >= "' . $currentDate . '"
                    AND invoices.due_date <= "' . $endDate . '"

                ),
                    0
                )
             )
             as total_due_next_15_days

             '
            ),
            DB::raw(
                '
                COALESCE(
                COALESCE(
                    (SELECT SUM(invoices.total_amount) FROM invoices
                    WHERE  invoices.tenant_id = tenants.id
                    AND invoices.due_date < "' . today() . '"


                ),
                    0
                )
                -
                COALESCE(
                    (SELECT SUM(invoice_payments.amount) FROM invoices
                    LEFT JOIN
                       invoice_payments ON invoices.id = invoice_payments.invoice_id
                    WHERE invoices.tenant_id = tenants.id
                    AND invoices.due_date < "' . today() . '"


                ),
                    0
                )
             )
             as total_over_due

             '
            ),


        )
        ->orderBy("tenants.first_Name",$request->order_by)->paginate($perPage);

        return response()->json($tenants, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v1.0/tenants/get/all/optimized",
 *      operationId="getAllTenantsOptimized",
 *      tags={"property_management.tenant_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },


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
* name="property_id",
* in="query",
* description="property_id",
* required=true,
* example="1"
* ),
*  @OA\Parameter(
*      name="property_ids[]",
*      in="query",
*      description="property_ids",
*      required=true,
*      example="1,2"
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

 public function getAllTenantsOptimized( Request $request)
 {
     try {
         $this->storeActivity($request,"");
         $currentDate = Carbon::now();
         $endDate = $currentDate->copy()->addDays(15);


         $tenantQuery =  Tenant::leftJoin('property_tenants', 'tenants.id', '=', 'property_tenants.tenant_id')
         ->leftJoin('properties', 'property_tenants.property_id', '=', 'properties.id')
         ->where([
             "tenants.created_by" => $request->user()->id
         ]);

         if (!empty($request->search_key)) {
             $tenantQuery = $tenantQuery->where(function ($query) use ($request) {
                 $term = $request->search_key;
                 // $query->where("properties.name", "like", "%" . $term . "%");
                 $query->orWhere("tenants.first_Name", "like", "%" . $term . "%");
                 $query->orWhere("tenants.last_Name", "like", "%" . $term . "%");
                 $query->orWhere("tenants.email", "like", "%" . $term . "%");
                 // $query->orWhere("tenants.address_line_1", "like", "%" . $term . "%");
                 // $query->orWhere("tenants.address_line_2", "like", "%" . $term . "%");



                 // $query->orWhere("tenants.phone", "like", "%" . $term . "%");
                 // $query->orWhere("tenants.country", "like", "%" . $term . "%");
                 // $query->orWhere("tenants.city", "like", "%" . $term . "%");
                 // $query->orWhere("tenants.postcode", "like", "%" . $term . "%");






             });
         }
         if(!empty($request->property_id)){
             $tenantQuery = $tenantQuery->where('properties.id',$request->property_id);
         }
         if(!empty($request->property_ids)) {
             $null_filter = collect(array_filter($request->property_ids))->values();
         $property_ids =  $null_filter->all();
             if(count($property_ids)) {
                 $tenantQuery =   $tenantQuery->whereIn("properties.id",$property_ids);
             }

         }

         if (!empty($request->start_date)) {
             $tenantQuery = $tenantQuery->where('tenants.created_at', ">=", $request->start_date);
         }
         if (!empty($request->end_date)) {
             $tenantQuery = $tenantQuery->where('tenants.created_at', "<=", $request->end_date);
         }



         $tenants = $tenantQuery
         ->groupBy("tenants.id")

         ->select(
             "tenants.id",
             "tenants.generated_id",
             "tenants.first_Name",
             "tenants.last_Name",
         )
         ->orderBy("tenants.first_Name",$request->order_by)->get();

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


        $tenant = Tenant::with("properties")->where([
            "generated_id" => $id,
            "tenants.created_by" => $request->user()->id

        ])
        ->first();

        if(!$tenant) {
     return response()->json([
"message" => "no tenant found"
],404);
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

        $tenant = Tenant::where([
            "id" => $id,
            "tenants.created_by" => $request->user()->id
        ])
        ->first();

        if(!$tenant) {
     return response()->json([
"message" => "no tenant found"
],404);
        }
        $tenant->delete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}






}
