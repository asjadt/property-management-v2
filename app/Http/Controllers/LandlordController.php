<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\LandlordCreateRequest;
use App\Http\Requests\LandlordUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\Landlord;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LandlordController extends Controller
{
    use ErrorUtil, UserActivityUtil;
        /**
        *
     * @OA\Post(
     *      path="/v1.0/landlord-image",
     *      operationId="createLandlordImage",
     *      tags={"property_management.landlord_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store landlord logo",
     *      description="This method is to store landlord logo",
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

    public function createLandlordImage(ImageUploadRequest $request)
    {
        try{
            $this->storeActivity($request,"");

            $insertableData = $request->validated();

            $location =  config("setup-config.landlord_image");

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
     *      path="/v1.0/landlords",
     *      operationId="createLandlord",
     *      tags={"property_management.landlord_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store landlord",
     *      description="This method is to store landlord",
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

    public function createLandlord(LandlordCreateRequest $request)
    {
        try {
            $this->storeActivity($request,"");
            return DB::transaction(function () use ($request) {



                $insertableData = $request->validated();
                $insertableData["created_by"] = $request->user()->id;
                $landlord =  Landlord::create($insertableData);
                $landlord->generated_id = Str::random(4) . $landlord->id . Str::random(4);
                $landlord->save();

                // for($i=0;$i<500;$i++) {
                //     $landlord =  Landlord::create([
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
                //     $landlord->generated_id = Str::random(4) . $landlord->id . Str::random(4);
                //     $landlord->save();
                // }



                return response($landlord, 201);





            });




        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }

   /**
     *
     * @OA\Put(
     *      path="/v1.0/landlords",
     *      operationId="updateLandlord",
     *      tags={"property_management.landlord_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update landlord",
     *      description="This method is to update landlord",
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

    public function updateLandlord(LandlordUpdateRequest $request)
    {
        try {
            $this->storeActivity($request,"");
            return  DB::transaction(function () use ($request) {

                $updatableData = $request->validated();

                // $affiliationPrev = Landlord::where([
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




                $landlord  =  tap(Landlord::where([
                    "id" => $updatableData["id"],
                    "created_by" => $request->user()->id
                    ]))->update(
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
        'email'
                    ])->toArray()
                )
                    // ->with("somthing")

                    ->first();

                return response($landlord, 200);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500,$request);
        }
    }
 /**
     *
     * @OA\Get(
     *      path="/v1.0/landlords/{perPage}",
     *      operationId="getLandlords",
     *      tags={"property_management.landlord_management"},
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
 *
 * *  @OA\Parameter(
* name="min_total_due",
* in="query",
* description="min_total_due",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="max_total_due",
* in="query",
* description="max_total_due",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="min_total_over_due",
* in="query",
* description="min_total_over_due",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="max_total_over_due",
* in="query",
* description="max_total_over_due",
* required=true,
* example="1"
* ),

     *      summary="This method is to get landlords ",
     *      description="This method is to get landlords",
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

    public function getLandlords($perPage, Request $request)
    {
        try {
            $this->storeActivity($request,"");
            $currentDate = Carbon::now();
            $endDate = $currentDate->copy()->addDays(15);


            $landlordQuery =  Landlord::with('properties',"properties.property_tenants")->leftJoin('properties', 'landlords.id', '=', 'properties.landlord_id')
            ->where(["landlords.created_by" => $request->user()->id]);

            if (!empty($request->search_key)) {
                $landlordQuery = $landlordQuery->where(function ($query) use ($request) {
                    $term = $request->search_key;
                    $terms = preg_split('/\s+/', $term); // Split search term by any whitespace

    foreach ($terms as $individualTerm) {
        $query->orWhere(function ($innerQuery) use ($individualTerm) {
            $innerQuery->where("landlords.first_Name", "like", "%" . $individualTerm . "%");
            $innerQuery->orWhere("landlords.last_Name", "like", "%" . $individualTerm . "%");
        });
    }



                    $query->orWhere("landlords.phone", "like", "%" . $term . "%");
                    $query->orWhere("landlords.address_line_1", "like", "%" . $term . "%");
                    $query->orWhere("landlords.address_line_2", "like", "%" . $term . "%");
                    $query->orWhere("landlords.country", "like", "%" . $term . "%");
                    $query->orWhere("landlords.city", "like", "%" . $term . "%");
                    $query->orWhere("landlords.postcode", "like", "%" . $term . "%");
                    $query->orWhere("landlords.email", "like", "%" . $term . "%");


                });
            }
            if(!empty($request->property_id)){
                $landlordQuery = $landlordQuery->where('properties.id',$request->property_id);
            }
            if(!empty($request->property_ids)) {
                $null_filter = collect(array_filter($request->property_ids))->values();
            $property_ids =  $null_filter->all();
                if(count($property_ids)) {
                    $landlordQuery =   $landlordQuery->whereIn("properties.id",$property_ids);
                }

            }
            if (!empty($request->start_date)) {
                $landlordQuery = $landlordQuery->where('landlords.created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $landlordQuery = $landlordQuery->where('landlords.created_at', "<=", $request->end_date);
            }

            $landlordQuery = $landlordQuery

            ->select(
                "landlords.*",
                DB::raw('
             COALESCE(
                 (SELECT COUNT(properties.id) FROM properties WHERE properties.landlord_id = landlords.id),
                 0
             ) AS total_properties
             '),

             DB::raw(

                '
             COALESCE(
                 (SELECT SUM(invoices.total_amount) FROM invoices WHERE invoices.landlord_id = landlords.id),
                 0
             ) AS total_amount
             '

             ),
             DB::raw('
             COALESCE(
                 (SELECT COUNT(invoices.id) FROM invoices WHERE invoices.landlord_id = landlords.id),
                 0
             ) AS total_invoices
             '),
             DB::raw(
                '
             COALESCE(
                 (SELECT SUM(invoice_payments.amount) FROM invoices
                 LEFT JOIN
                    invoice_payments ON invoices.id = invoice_payments.invoice_id
                 WHERE invoices.landlord_id = landlords.id),
                 0
             ) AS total_paid
             '
             ),
             DB::raw(
                '
                COALESCE(
                COALESCE(
                    (SELECT SUM(invoices.total_amount) FROM invoices WHERE invoices.landlord_id = landlords.id),
                    0
                )
                -
                COALESCE(
                    (SELECT SUM(invoice_payments.amount) FROM invoices
                    LEFT JOIN
                       invoice_payments ON invoices.id = invoice_payments.invoice_id
                    WHERE invoices.landlord_id = landlords.id),
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
                        WHERE  invoices.landlord_id = landlords.id
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
                        WHERE invoices.landlord_id = landlords.id
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
                        WHERE  invoices.landlord_id = landlords.id
                        AND invoices.due_date < "' . today() . '"


                    ),
                        0
                    )
                    -
                    COALESCE(
                        (SELECT SUM(invoice_payments.amount) FROM invoices
                        LEFT JOIN
                           invoice_payments ON invoices.id = invoice_payments.invoice_id
                        WHERE invoices.landlord_id = landlords.id
                        AND invoices.due_date < "' . today() . '"


                    ),
                        0
                    )
                 )
                 as total_over_due

                 '
                ),


            );

            if(!empty($request->min_total_due)) {
                $landlordQuery = $landlordQuery->havingRaw("total_due >= " .$request->min_total_due . "");
            }
            if(!empty($request->max_total_due)) {
                $landlordQuery = $landlordQuery->havingRaw("total_due <= " .$request->max_total_due . "");
            }

            if(!empty($request->min_total_over_due)) {
                $landlordQuery = $landlordQuery->havingRaw("total_over_due >= " .$request->min_total_over_due . "");
            }
            if(!empty($request->max_total_over_due)) {
                $landlordQuery = $landlordQuery->havingRaw("total_over_due <= " .$request->max_total_over_due . "");
            }

          $landlords =  $landlordQuery
          ->groupBy("landlords.id")
          ->orderBy("landlords.first_Name",$request->order_by)->paginate($perPage);

            return response()->json($landlords, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }
/**
     *
     * @OA\Get(
     *      path="/v1.0/landlords/get/all",
     *      operationId="getAllLandlords",
     *      tags={"property_management.landlord_management"},
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
* name="min_total_due",
* in="query",
* description="min_total_due",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="max_total_due",
* in="query",
* description="max_total_due",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="min_total_over_due",
* in="query",
* description="min_total_over_due",
* required=true,
* example="1"
* ),
 * *  @OA\Parameter(
* name="max_total_over_due",
* in="query",
* description="max_total_over_due",
* required=true,
* example="1"
* ),
     *      summary="This method is to get all landlords ",
     *      description="This method is to get all landlords",
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

     public function getAllLandlords(Request $request)
     {
         try {
             $this->storeActivity($request,"");

             // $automobilesQuery = AutomobileMake::with("makes");

             $landlordQuery =  Landlord::with('properties',"properties.property_tenants")
             ->leftJoin('properties', 'landlords.id', '=', 'properties.landlord_id')
             ->where(["created_by" => $request->user()->id]);

             if (!empty($request->search_key)) {
                 $landlordQuery = $landlordQuery->where(function ($query) use ($request) {
                     $term = $request->search_key;
                     $terms = preg_split('/\s+/', $term); // Split search term by any whitespace

                     foreach ($terms as $individualTerm) {
                         $query->orWhere(function ($innerQuery) use ($individualTerm) {
                             $innerQuery->where("landlords.first_Name", "like", "%" . $individualTerm . "%");
                             $innerQuery->orWhere("landlords.last_Name", "like", "%" . $individualTerm . "%");
                         });
                     }


                     $query->orWhere("landlords.phone", "like", "%" . $term . "%");


                    //  $query->orWhere("landlords.address_line_1", "like", "%" . $term . "%");
                    //  $query->orWhere("landlords.address_line_2", "like", "%" . $term . "%");
                    //  $query->orWhere("landlords.country", "like", "%" . $term . "%");
                    //  $query->orWhere("landlords.city", "like", "%" . $term . "%");
                    //  $query->orWhere("landlords.postcode", "like", "%" . $term . "%");
                    //  $query->orWhere("landlords.email", "like", "%" . $term . "%");
                 });
             }

             if (!empty($request->start_date)) {
                 $landlordQuery = $landlordQuery->where('created_at', ">=", $request->start_date);
             }
             if (!empty($request->end_date)) {
                 $landlordQuery = $landlordQuery->where('created_at', "<=", $request->end_date);
             }
             if(!empty($request->property_id)){
                $landlordQuery = $landlordQuery->where('properties.id',$request->property_id);
            }
            if(!empty($request->property_ids)) {
                $null_filter = collect(array_filter($request->property_ids))->values();
            $property_ids =  $null_filter->all();
                if(count($property_ids)) {
                    $landlordQuery =   $landlordQuery->whereIn("properties.id",$property_ids);
                }

            }
             $currentDate = Carbon::now();
             $endDate = $currentDate->copy()->addDays(15);
             $landlordQuery = $landlordQuery

             ->select(
                 "landlords.*",
                 DB::raw('
              COALESCE(
                  (SELECT COUNT(properties.id) FROM properties WHERE properties.landlord_id = landlords.id),
                  0
              ) AS total_properties
              '),
              DB::raw('
              COALESCE(
                  (SELECT COUNT(invoices.id) FROM invoices WHERE invoices.landlord_id = landlords.id),
                  0
              ) AS total_invoices
              '),

              DB::raw(

                 '
              COALESCE(
                  (SELECT SUM(invoices.total_amount) FROM invoices WHERE invoices.landlord_id = landlords.id),
                  0
              ) AS total_amount
              '

              ),
              DB::raw(
                 '
              COALESCE(
                  (SELECT SUM(invoice_payments.amount) FROM invoices
                  LEFT JOIN
                     invoice_payments ON invoices.id = invoice_payments.invoice_id
                  WHERE invoices.landlord_id = landlords.id),
                  0
              ) AS total_paid
              '
              ),
              DB::raw(
                 '
                 COALESCE(
                 COALESCE(
                     (SELECT SUM(invoices.total_amount) FROM invoices WHERE invoices.landlord_id = landlords.id),
                     0
                 )
                 -
                 COALESCE(
                     (SELECT SUM(invoice_payments.amount) FROM invoices
                     LEFT JOIN
                        invoice_payments ON invoices.id = invoice_payments.invoice_id
                     WHERE invoices.landlord_id = landlords.id),
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
                         WHERE  invoices.landlord_id = landlords.id
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
                         WHERE invoices.landlord_id = landlords.id
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
                         WHERE  invoices.landlord_id = landlords.id
                         AND invoices.due_date < "' . today() . '"


                     ),
                         0
                     )
                     -
                     COALESCE(
                         (SELECT SUM(invoice_payments.amount) FROM invoices
                         LEFT JOIN
                            invoice_payments ON invoices.id = invoice_payments.invoice_id
                         WHERE invoices.landlord_id = landlords.id
                         AND invoices.due_date < "' . today() . '"


                     ),
                         0
                     )
                  )
                  as total_over_due

                  '
                 ),


             );

             if(!empty($request->min_total_due)) {
                $landlordQuery = $landlordQuery->havingRaw("total_due >= " .$request->min_total_due . "");
            }
            if(!empty($request->max_total_due)) {
                $landlordQuery = $landlordQuery->havingRaw("total_due <= " .$request->max_total_due . "");
            }
            if(!empty($request->min_total_over_due)) {
                $landlordQuery = $landlordQuery->havingRaw("total_over_due >= " .$request->min_total_over_due . "");
            }
            if(!empty($request->max_total_over_due)) {
                $landlordQuery = $landlordQuery->havingRaw("total_over_due <= " .$request->max_total_over_due . "");
            }

           $landlords =  $landlordQuery
           ->groupBy("landlords.id")
           ->orderBy("landlords.first_Name",$request->order_by)->get();

             return response()->json($landlords, 200);
         } catch (Exception $e) {

             return $this->sendError($e, 500,$request);
         }
     }



 /**
     *
     * @OA\Get(
     *      path="/v1.0/landlords/get/single/{id}",
     *      operationId="getLandlordById",
     *      tags={"property_management.landlord_management"},
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

     *      summary="This method is to get landlord by id",
     *      description="This method is to get landlord by id",
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

    public function getLandlordById($id, Request $request)
    {
        try {
            $this->storeActivity($request,"");


            $landlord = Landlord::with("properties")->where([
                "generated_id" => $id,
                "created_by" => $request->user()->id
            ])
            ->select(
                "landlords.*",
                DB::raw('
             COALESCE(
                 (SELECT COUNT(properties.id) FROM properties WHERE properties.landlord_id = landlords.id),
                 0
             ) AS total_properties
             '))
            ->first();

            if(!$landlord) {
         return response()->json([
"message" => "no landlord found"
],404);
            }


            return response()->json($landlord, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }










   /**
 * @OA\Delete(
 *     path="/v1.0/landlords/{id}",
 *     operationId="deleteLandlordById",
 *     tags={"property_management.landlord_management"},
 *     security={
 *         {"bearerAuth": {}},
 *        {"pin": {}}
 *     },
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         description="id",
 *         required=true,
 *         example="1"
 *     ),

 *     summary="This method is to delete landlord by id",
 *     description="This method is to delete landlord by id",
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthenticated",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=422,
 *         description="Unprocessable Content",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Forbidden",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Bad Request",
 *         @OA\JsonContent()
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Not Found",
 *         @OA\JsonContent()
 *     )
 * )
 */
    public function deleteLandlordById($id, Request $request)
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

            $landlord = Landlord::where([
                "id" => $id,
                "created_by" => $request->user()->id
            ])
            ->first();

            if(!$landlord) {
         return response()->json([
"message" => "no landlord found"
],404);
            }
            $landlord->delete();

            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }

}
