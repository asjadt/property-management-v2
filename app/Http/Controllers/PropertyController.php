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
            $property =  Property::create($insertableData);


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

        $propertyQuery =  Property::where(["created_by" => $request->user()->id]);

        if (!empty($request->search_key)) {
            $propertyQuery = $propertyQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                $query->where("name", "like", "%" . $term . "%");
                $query->orWhere("address", "like", "%" . $term . "%");
            });
        }

        if (!empty($request->address)) {
            $propertyQuery =  $propertyQuery->orWhere("address", "like", "%" . $request->address . "%");
        }

        if (!empty($request->start_date)) {
            $propertyQuery = $propertyQuery->where('created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $propertyQuery = $propertyQuery->where('created_at', "<=", $request->end_date);
        }

        $properties = $propertyQuery->orderByDesc("id")->paginate($perPage);

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

         $propertyQuery =  Property::where(["created_by" => $request->user()->id]);

         if (!empty($request->search_key)) {
             $propertyQuery = $propertyQuery->where(function ($query) use ($request) {
                 $term = $request->search_key;
                 $query->where("name", "like", "%" . $term . "%");
                 $query->orWhere("address", "like", "%" . $term . "%");
             });
         }

         if (!empty($request->address)) {
             $propertyQuery =  $propertyQuery->orWhere("address", "like", "%" . $request->address . "%");
         }

         if (!empty($request->start_date)) {
             $propertyQuery = $propertyQuery->where('created_at', ">=", $request->start_date);
         }
         if (!empty($request->end_date)) {
             $propertyQuery = $propertyQuery->where('created_at', "<=", $request->end_date);
         }

         $properties = $propertyQuery
         ->select("id","address")
         ->orderBy("address",'asc')->get();

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
 *           {"bearerAuth": {}}
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
            $prefix = preg_replace_callback('/\b\w/', function ($matches) {
                return strtoupper($matches[0]);
            }, $business->name);
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

       $reference_no_exists =  DB::table( 'properties' )->where( [
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
