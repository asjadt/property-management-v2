<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientCreateRequest;
use App\Http\Requests\ClientUpdateRequest;
use App\Http\Requests\ImageUploadRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\Client;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientController extends Controller
{
    use ErrorUtil, UserActivityUtil;
    /**
    *
 * @OA\Post(
 *      path="/v1.0/client-image",
 *      operationId="createClientImage",
 *      tags={"property_management.client_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store client logo",
 *      description="This method is to store client logo",
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

public function createClientImage(ImageUploadRequest $request)
{
    try{
        $this->storeActivity($request,"");

        $insertableData = $request->validated();

        $location =  config("setup-config.client_image");

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
 *      path="/v1.0/clients",
 *      operationId="createClient",
 *      tags={"property_management.client_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store client",
 *      description="This method is to store client",
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

public function createClient(ClientCreateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return DB::transaction(function () use ($request) {



            $insertableData = $request->validated();
            $insertableData["created_by"] = $request->user()->id;
            $client =  Client::create($insertableData);

            if(!$client) {
                throw new Exception("something went wrong");
            }

            $client->generated_id = Str::random(4) . $client->id . Str::random(4);
            $client->save();

            return response($client, 201);





        });




    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 * @OA\Put(
 *      path="/v1.0/clients",
 *      operationId="updateClient",
 *      tags={"property_management.client_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to update client",
 *      description="This method is to update client",
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

public function updateClient(ClientUpdateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return  DB::transaction(function () use ($request) {

            $updatableData = $request->validated();

            // $affiliationPrev = Clients::where([
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




            $client  =  tap(Client::where(["id" => $updatableData["id"], "created_by" => $request->user()->id]))->update(
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
                if(!$client) {
                    throw new Exception("something went wrong");
                }


            return response($client, 200);
        });
    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v1.0/clients/{perPage}",
 *      operationId="getClients",
 *      tags={"property_management.client_management"},
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
 *      summary="This method is to get clients ",
 *      description="This method is to get clients",
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

public function getClients($perPage, Request $request)
{
    try {
        $this->storeActivity($request,"");

        // $automobilesQuery = AutomobileMake::with("makes");

        $clientQuery =  Client::where([
            "clients.created_by" => $request->user()->id
        ]);

        if (!empty($request->search_key)) {
            $clientQuery = $clientQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                // $query->where("properties.name", "like", "%" . $term . "%");
                $query->orWhere("clients.first_Name", "like", "%" . $term . "%");
                $query->orWhere("clients.last_Name", "like", "%" . $term . "%");
                $query->orWhere("clients.email", "like", "%" . $term . "%");
                // $query->orWhere("clients.address_line_1", "like", "%" . $term . "%");
                // $query->orWhere("clients.address_line_2", "like", "%" . $term . "%");



                // $query->orWhere("clients.phone", "like", "%" . $term . "%");
                // $query->orWhere("clients.country", "like", "%" . $term . "%");
                // $query->orWhere("clients.city", "like", "%" . $term . "%");
                // $query->orWhere("clients.postcode", "like", "%" . $term . "%");






            });
        }
        // if(!empty($request->property_id)){
        //     $clientQuery = $clientQuery->where('properties.id',$request->property_id);
        // }
        // if(!empty($request->property_ids)) {
        //     $null_filter = collect(array_filter($request->property_ids))->values();
        // $property_ids =  $null_filter->all();
        //     if(count($property_ids)) {
        //         $clientQuery =   $clientQuery->whereIn("properties.id",$property_ids);
        //     }

        // }

        if (!empty($request->start_date)) {
            $clientQuery = $clientQuery->where('clients.created_at', ">=", $request->start_date);
        }
        if (!empty($request->end_date)) {
            $clientQuery = $clientQuery->where('clients.created_at', "<=", $request->end_date);
        }



        $clients = $clientQuery
        ->groupBy("clients.id")
        ->select(
            "clients.*",

        )
        ->orderBy("clients.first_Name",$request->order_by)->paginate($perPage);

        return response()->json($clients, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}



/**
 *
 * @OA\Get(
 *      path="/v1.0/clients/get/single/{id}",
 *      operationId="getClientById",
 *      tags={"property_management.client_management"},
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

 *      summary="This method is to get client by id",
 *      description="This method is to get client by id",
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

public function getClientById($id, Request $request)
{
    try {
        $this->storeActivity($request,"");


        $client = Client::where([
            "generated_id" => $id,
            "clients.created_by" => $request->user()->id

        ])
        ->first();

        if(!$client) {
     return response()->json([
"message" => "no client found"
],404);
        }


        return response()->json($client, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}










/**
 *
 *     @OA\Delete(
 *      path="/v1.0/clients/{id}",
 *      operationId="deleteClientById",
 *      tags={"property_management.client_management"},
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
 *      summary="This method is to delete client by id",
 *      description="This method is to delete client by id",
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

public function deleteClientById($id, Request $request)
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

        $client = Client::where([
            "id" => $id,
            "clients.created_by" => $request->user()->id
        ])
        ->first();

        if(!$client) {
     return response()->json([
"message" => "no client found"
],404);
        }
        $client->delete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}



}
