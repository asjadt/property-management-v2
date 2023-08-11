<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\PropertyCreateRequest;
use App\Http\Requests\PropertyUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\Property;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    use ErrorUtil, UserActivityUtil;
    /**
    *
 * @OA\Post(
 *      path="/v1.0/property-image",
 *      operationId="createPropertyImage",
 *      tags={"property_management.property_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store property logo",
 *      description="This method is to store property logo",
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

public function createPropertyImage(ImageUploadRequest $request)
{
    try{
        $this->storeActivity($request,"");

        $insertableData = $request->validated();

        $location =  config("setup-config.property_image");

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
 *      path="/v1.0/properties",
 *      operationId="createProperty",
 *      tags={"property_management.property_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store property",
 *      description="This method is to store property",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"name","description","logo"},
 *  *             @OA\Property(property="image", type="string", format="string",example="image.jpg"),
  *             @OA\Property(property="name", type="string", format="string",example="Rifat"),

 *            @OA\Property(property="address", type="string", format="string",example="address"),
 *  * *  @OA\Property(property="country", type="string", format="string",example="country"),
 *  * *  @OA\Property(property="city", type="string", format="string",example="Dhaka"),
 *  * *  @OA\Property(property="postcode", type="string", format="string",example="1207"),
 *  *  * *  @OA\Property(property="town", type="string", format="string",example="town"),
 *
 *     *  * *  @OA\Property(property="lat", type="string", format="string",example="1207"),
 *     *  * *  @OA\Property(property="long", type="string", format="string",example="1207"),
 *  *     *  * *  @OA\Property(property="type", type="string", format="string",example="type"),

 *  *     *  * *  @OA\Property(property="reference_no", type="string", format="string",example="reference_no"),
 *  *     *  * *  @OA\Property(property="landlord_id", type="string", format="string",example="1"),
 *  *  *  *     *  * *  @OA\Property(property="tenant_ids", type="string", format="array",example={1,2,3}),

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

public function createProperty(PropertyCreateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return DB::transaction(function () use ($request) {



            $insertableData = $request->validated();
            $insertableData["created_by"] = $request->user()->id;

            $reference_no_exists =  DB::table( 'properties' )->where([
                'reference_no'=> $insertableData['reference_no'],
                "created_by" => $request->user()->id
             ]
             )->exists();
             if ($reference_no_exists) {
                $error =  [
                       "message" => "The given data was invalid.",
                       "errors" => ["reference_no"=>["The reference no has already been taken."]]
                ];
                   throw new Exception(json_encode($error),422);
               }




            $property =  Property::create($insertableData);
            $property->generated_id = Str::random(4) . $property->id . Str::random(4);
            $property->save();


            if(!empty($insertableData['tenant_ids'])) {
                $property->property_tenants()->sync($insertableData['tenant_ids'],[]);
            }



            return response($property, 201);





        });




    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 * @OA\Put(
 *      path="/v1.0/properties",
 *      operationId="updateProperty",
 *      tags={"property_management.property_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to update property",
 *      description="This method is to update property",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"id","name","description","logo"},
 *     *             @OA\Property(property="id", type="number", format="number",example="1"),
 *  *             @OA\Property(property="image", type="string", format="string",example="image.jpg"),
  *             @OA\Property(property="name", type="string", format="string",example="Rifat"),

 *            @OA\Property(property="address", type="string", format="string",example="address"),
 *  * *  @OA\Property(property="country", type="string", format="string",example="country"),
 *  * *  @OA\Property(property="city", type="string", format="string",example="Dhaka"),
 *  * *  @OA\Property(property="postcode", type="string", format="string",example="1207"),
 *  *  * *  @OA\Property(property="town", type="string", format="string",example="town"),
 *
 *     *  * *  @OA\Property(property="lat", type="string", format="string",example="1207"),
 *     *  * *  @OA\Property(property="long", type="string", format="string",example="1207"),
 *  *     *  * *  @OA\Property(property="type", type="string", format="string",example="type"),

 *  *     *  * *  @OA\Property(property="reference_no", type="string", format="string",example="reference_no"),
 *  *     *  * *  @OA\Property(property="landlord_id", type="string", format="string",example="1"),
 *  *  *     *  * *  @OA\Property(property="tenant_ids", type="string", format="array",example={1,2,3}),
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

public function updateProperty(PropertyUpdateRequest $request)
{
    try {
        $this->storeActivity($request,"");

        return  DB::transaction(function () use ($request) {

            $updatableData = $request->validated();


            $reference_no_exists =  DB::table( 'properties' )->where([
                'reference_no'=> $updatableData['reference_no'],
                "created_by" => $request->user()->id
             ]
             )
             ->whereNotIn('id', [$updatableData["id"]])->exists();
             if ($reference_no_exists) {
                $error =  [
                       "message" => "The given data was invalid.",
                       "errors" => ["reference_no"=>["The reference no has already been taken."]]
                ];
                   throw new Exception(json_encode($error),422);
               }


            $property  =  tap(Property::where([
                "id" => $updatableData["id"],
                "created_by" => $request->user()->id
                ]))->update(
                collect($updatableData)->only([
                'name',
        'image',
        'address',
        'country',
        'city',
        'postcode',
        "town",
        "lat",
        "long",
        'type',
        'reference_no',
        'landlord_id',
                ])->toArray()
            )
                // ->with("somthing")

                ->first();

                if(!$property) {
                    return response()->json([
                        "message" => "no property found"
                        ],404);

                }
                if(!empty($updatableData['tenant_ids'])) {
                    $property->property_tenants()->sync($updatableData['tenant_ids'],[]);
                }

            return response($property, 200);
        });
    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v1.0/properties/{perPage}",
 *      operationId="getProperties",
 *      tags={"property_management.property_management"},
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
* name="address",
* in="query",
* description="address",
* required=true,
* example="address"
* ),
 * *  @OA\Parameter(
* name="landlord_id",
* in="query",
* description="landlord_id",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="tenant_id",
* in="query",
* description="tenant_id",
* required=true,
* example="1"
* ),
 *      summary="This method is to get properties ",
 *      description="This method is to get properties",
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

public function getProperties($perPage, Request $request)
{
    try {
        $this->storeActivity($request,"");

        // $automobilesQuery = AutomobileMake::with("makes");

        $propertyQuery =  Property::leftJoin('property_tenants', 'properties.id', '=', 'property_tenants.property_id')
        ->leftJoin('tenants', 'property_tenants.tenant_id', '=', 'tenants.id')
        ->where(["properties.created_by" => $request->user()->id]);

        if (!empty($request->search_key)) {
            $propertyQuery = $propertyQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                // $query->where("name", "like", "%" . $term . "%");
                $query->where("properties.reference_no", "like", "%" . $term . "%");
                $query->orWhere("properties.address", "like", "%" . $term . "%");
                $query->orWhere("properties.type", "like", "%" . $term . "%");
            });
        }
        if (!empty($request->landlord_id)) {
            $propertyQuery =  $propertyQuery->where("properties.landlord_id", $request->landlord_id );
        }
        if (!empty($request->tenant_id)) {
            $propertyQuery =  $propertyQuery->where("tenants.id", $request->tenant_id );
        }
        if (!empty($request->address)) {
            $propertyQuery =  $propertyQuery->orWhere("properties.address", "like", "%" . $request->address . "%");
        }

        if (!empty($request->start_date)) {
            $propertyQuery = $propertyQuery->where('properties.created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $propertyQuery = $propertyQuery->where('properties.created_at', "<=", $request->end_date);
        }

        $properties = $propertyQuery->orderBy("properties.address",$request->order_by)
        ->groupBy("properties.id")
        ->select(
            "properties.*",
            DB::raw('
            COALESCE(
                (SELECT COUNT(invoices.id) FROM invoices WHERE invoices.property_id = properties.id),
                0
            ) AS total_invoice
        '),
            )
        ->paginate($perPage);

        return response()->json($properties, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 * @OA\Get(
 *      path="/v1.0/properties/get/all",
 *      operationId="getAllProperties",
 *      tags={"property_management.property_management"},
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
* name="address",
* in="query",
* description="address",
* required=true,
* example="address"
* ),
 * *  @OA\Parameter(
* name="landlord_id",
* in="query",
* description="landlord_id",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="tenant_id",
* in="query",
* description="tenant_id",
* required=true,
* example="1"
* ),
 *      summary="This method is to get properties ",
 *      description="This method is to get properties",
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

 public function getAllProperties( Request $request)
 {
     try {
         $this->storeActivity($request,"");

         // $automobilesQuery = AutomobileMake::with("makes");

         $propertyQuery =  Property::leftJoin('property_tenants', 'properties.id', '=', 'property_tenants.property_id')
         ->leftJoin('tenants', 'property_tenants.tenant_id', '=', 'tenants.id')
         ->where(["properties.created_by" => $request->user()->id]);

         if (!empty($request->search_key)) {
             $propertyQuery = $propertyQuery->where(function ($query) use ($request) {
                 $term = $request->search_key;

                 $query->where("properties.reference_no", "like", "%" . $term . "%");
                 $query->orWhere("properties.address", "like", "%" . $term . "%");
                 $query->orWhere("properties.type", "like", "%" . $term . "%");


                //  $query->orWhere("properties.name", "like", "%" . $term . "%");

                //  $query->orWhere("properties.country", "like", "%" . $term . "%");
                //  $query->orWhere("properties.city", "like", "%" . $term . "%");
                //  $query->orWhere("properties.postcode", "like", "%" . $term . "%");
                //  $query->orWhere("properties.town", "like", "%" . $term . "%");


             });
         }

         if (!empty($request->landlord_id)) {
            $propertyQuery =  $propertyQuery->where("properties.landlord_id", $request->landlord_id );
        }
        if (!empty($request->tenant_id)) {
            $propertyQuery =  $propertyQuery->where("tenants.id", $request->tenant_id );
        }
         if (!empty($request->address)) {
             $propertyQuery =  $propertyQuery->where("properties.address", "like", "%" . $request->address . "%");
         }

         if (!empty($request->start_date)) {
             $propertyQuery = $propertyQuery->where('properties.created_at', ">=", $request->start_date);
         }
         if (!empty($request->end_date)) {
             $propertyQuery = $propertyQuery->where('properties.created_at', "<=", $request->end_date);
         }

         $properties = $propertyQuery
         ->groupBy("properties.id")
         ->select("properties.id","properties.address")

         ->orderBy("properties.address",$request->order_by)->get();

         return response()->json($properties, 200);
     } catch (Exception $e) {

         return $this->sendError($e, 500,$request);
     }
 }



/**
 *
 * @OA\Get(
 *      path="/v1.0/properties/get/single/{id}",
 *      operationId="getPropertyById",
 *      tags={"property_management.property_management"},
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

 *      summary="This method is to get property by id",
 *      description="This method is to get property by id",
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

public function getPropertyById($id, Request $request)
{
    try {
        $this->storeActivity($request,"");


        $property = Property::
        with(
            "property_tenants",
            "landlord",
            "repairs.repair_category",
            "invoices"

            )
        ->where([
            "generated_id" => $id,
            "created_by" => $request->user()->id
        ])
        ->first();

        if(!$property) {
     return response()->json([
"message" => "no property found"
],404);
        }


        return response()->json($property, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}










/**
 *
 *     @OA\Delete(
 *      path="/v1.0/properties/{id}",
 *      operationId="deletePropertyById",
 *      tags={"property_management.property_management"},
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
 *      summary="This method is to delete property by id",
 *      description="This method is to delete property by id",
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

public function deletePropertyById($id, Request $request)
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


        $property = Property::where([
            "id" => $id,
            "created_by" => $request->user()->id
        ])
        ->first();

        if(!$property) {
     return response()->json([
"message" => "no property found"
],404);
        }
        $property->delete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}




/**
 *
 * @OA\Get(
 *      path="/v1.0/properties/generate/property-reference_no",
 *      operationId="generatePropertyReferenceNumber",
 *      tags={"property_management.property_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },



 *      summary="This method is to generate reference number",
 *      description="This method is to generate reference number",
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
public function generatePropertyReferenceNumber(Request $request)
{
    try {
        $this->storeActivity($request,"");

        $business = Business::where(["owner_id" => $request->user()->id])->first();


        $prefix = "";
        if ($business) {
            preg_match_all('/\b\w/', $business->name, $matches);

            $prefix = implode('', array_map(function ($match) {
                return strtoupper($match[0]);
            }, $matches[0]));

            // If you want to get only the first two letters from each word:
            $prefix = substr($prefix, 0, 2 * count($matches[0]));
        }

        $current_number = 1; // Start from 0001

        do {
            $reference_no = $prefix . "-" . str_pad($current_number, 4, '0', STR_PAD_LEFT);
            $current_number++; // Increment the current number for the next iteration
        } while (
            DB::table('properties')->where([
                'reference_no' => $reference_no,
                "created_by" => $request->user()->id
            ])->exists()
        );


return response()->json(["reference_no" => $reference_no],200);

    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
    }
}



/**
*
* @OA\Get(
*      path="/v1.0/properties/validate/property-reference_no/{reference_no}",
*      operationId="validatePropertyReferenceNumber",
*      tags={"property_management.property_management"},
*       security={
*           {"bearerAuth": {}}
*       },

*              @OA\Parameter(
*         name="reference_no",
*         in="path",
*         description="reference_no",
*         required=true,
*  example="1"
*      ),

*      summary="This method is to validate reference number",
*      description="This method is to validate reference number",
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
public function validatePropertyReferenceNumber($reference_no, Request $request)
{
   try {
       $this->storeActivity($request,"");

       $reference_no_exists =  DB::table( 'properties' )->where([
          'reference_no'=> $reference_no,
          "created_by" => $request->user()->id
       ]
       )->exists();



return response()->json(["reference_no_exists" => $reference_no_exists],200);

   } catch (Exception $e) {
       error_log($e->getMessage());
       return $this->sendError($e, 500,$request);
   }
}

}
