<?php

namespace App\Http\Controllers;

use App\Http\Requests\FuelStationCreateRequest;
use App\Http\Requests\FuelStationUpdateRequest;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\FuelStation;
use App\Models\FuelStationOption;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuelStationController extends Controller
{
    use ErrorUtil,UserActivityUtil;

    /**
     *
     * @OA\Post(
     *      path="/v1.0/fuel-station",
     *      operationId="createFuelStation",
     *      tags={"fuel_station_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to store fuel station",
     *      description="This method is to store fuel station",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"name","address","opening_time","closing_time","description"},
     *    @OA\Property(property="name", type="string", format="string",example="car"),
     *    @OA\Property(property="address", type="string", format="string",example="car"),
     *    @OA\Property(property="opening_time", type="string", format="string",example="10:10"),
     * *    @OA\Property(property="closing_time", type="string", format="string",example="10:10"),
     * *    @OA\Property(property="description", type="string", format="number",example="description"),
     *    *  * *    @OA\Property(property="lat", type="string", format="string",example="23.704263332849386"),
     *  * *    @OA\Property(property="long", type="string", format="string",example="90.44707059805279"),
     *
     *      *  * *  @OA\Property(property="country", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="city", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="postcode", type="string", format="string",example="1"),
     *     *  * *  @OA\Property(property="address_line_1", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="address_line_2", type="string", format="string",example="1"),
     *   *  * *  @OA\Property(property="additional_information", type="string", format="string",example="1"),
     *
     * *  * *    @OA\Property(property="options", type="string", format="array",example={
     * {"option_id":1,"is_active":true},
     *  * {"option_id":2,"is_active":true},
     * }),
     *
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

    public function createFuelStation(FuelStationCreateRequest $request)
    {
        try {
            $this->storeActivity($request,"");
            return DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('fuel_station_create')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }

                $request_data = $request->validated();

                $request_data["created_by"] = $request->user()->id;

                $fuel_station =  FuelStation::create($request_data);

                if(empty($request_data["options"])) {
                    $request_data["options"]  = [];
                }

                foreach($request_data["options"] as $option) {

                    FuelStationOption::create([
                        "fuel_station_id"=>$fuel_station->id,
                        "option_id"=> $option["option_id"],
                        "is_active"=> $option["is_active"],
                    ]);

                }

                return response($fuel_station, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500,$request);
        }
    }

    /**
     *
     * @OA\Put(
     *      path="/v1.0/fuel-station",
     *      operationId="updateFuelStation",
     *      tags={"fuel_station_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *      summary="This method is to update fuel station",
     *      description="This method is to update fuel station",
     *
     *  @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","name","address","opening_time","closing_time","description"},
     *    @OA\Property(property="id", type="number", format="number", example="1"),
     *    @OA\Property(property="name", type="string", format="string",example="car"),
     *    @OA\Property(property="address", type="string", format="string",example="car"),
     *    @OA\Property(property="opening_time", type="string", format="string",example="10:10"),
     * *    @OA\Property(property="closing_time", type="string", format="string",example="10:10"),
     * *    @OA\Property(property="description", type="string", format="number",example="description"),
     *
     *  *    *  * *    @OA\Property(property="lat", type="string", format="string",example="23.704263332849386"),
     *  * *    @OA\Property(property="long", type="string", format="string",example="90.44707059805279"),
     *  *      *  * *  @OA\Property(property="country", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="city", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="postcode", type="string", format="string",example="1"),
     *     *  * *  @OA\Property(property="address_line_1", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="address_line_2", type="string", format="string",example="1"),
     *   *  * *  @OA\Property(property="additional_information", type="string", format="string",example="1"),
     *
     *   * *  * *    @OA\Property(property="options", type="string", format="array",example={
      * {"option_id":1,"is_active":true},
     *  * {"option_id":2,"is_active":true},
     * }),
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

    public function updateFuelStation(FuelStationUpdateRequest $request)
    {
        try {
            $this->storeActivity($request,"");
            return  DB::transaction(function () use ($request) {
                if (!$request->user()->hasPermissionTo('fuel_station_update')) {
                    return response()->json([
                        "message" => "You can not perform this action"
                    ], 401);
                }
                $updatableData = $request->validated();

                $fuelStationPrev = FuelStation::where([
                    "id" => $updatableData["id"]
                   ]);

                   if(!$request->user()->hasRole('superadmin')) {
                    $fuelStationPrev =    $fuelStationPrev->where([
                        "created_by" =>$request->user()->id
                    ]);
                }
                $fuelStationPrev = $fuelStationPrev->first();
                 if(!$fuelStationPrev) {
                        return response()->json([
                           "message" => "you did not create this fuel station."
                        ],404);
                 }

                $fuel_station  =  tap(FuelStation::where(["id" => $updatableData["id"]]))->update(
                    collect($updatableData)->only([
                        "name",
                        "address",
                        "opening_time",
                        "closing_time",
                        "description",
                        "lat",
                        "long",
        "country",
        "city",
        "postcode",
        "additional_information",
        "address_line_1",
        "address_line_2",
                    ])->toArray()
                )
                     ->with("options")

                    ->first();

                    if(!$fuel_station){
                        return response()->json([
                            "message" => "no fuel station found"
                        ],
                        404);
                    }


                    FuelStationOption::where([
                        "fuel_station_id"=>$fuel_station->id,
                    ])
                    ->delete();


                    if(empty($updatableData["options"])) {
                        $updatableData["options"]  = [];
                    }

                    foreach($updatableData["options"] as $option) {

                        FuelStationOption::create([
                            "fuel_station_id"=>$fuel_station->id,
                            "option_id"=> $option["option_id"],
                            "is_active"=> $option["is_active"],
                        ]);

                    }




                return response($fuel_station, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500,$request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/fuel-station/{perPage}",
     *      operationId="getFuelStations",
     *      tags={"fuel_station_management"},
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
     *      *
     *   *              @OA\Parameter(
     *         name="time",
     *         in="query",
     *         description="current time",
     *         required=true,
     *  example="10:10"
     *      ),
     *
     *
     *     * *  @OA\Parameter(
* name="country",
* in="query",
* description="country",
* required=true,
* example="country"
* ),
     * *  @OA\Parameter(
* name="city",
* in="query",
* description="city",
* required=true,
* example="city"
* ),
     *
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
* name="start_lat",
* in="query",
* description="start_lat",
* required=true,
* example="3"
* ),
     * *  @OA\Parameter(
* name="end_lat",
* in="query",
* description="end_lat",
* required=true,
* example="2"
* ),
     * *  @OA\Parameter(
* name="start_long",
* in="query",
* description="start_long",
* required=true,
* example="1"
* ),
     * *  @OA\Parameter(
* name="end_long",
* in="query",
* description="end_long",
* required=true,
* example="4"
* ),
*  @OA\Parameter(
*      name="active_option_ids[]",
*      in="query",
*      description="active_option_ids",
*      required=true,
*      example="1,2"
* ),

  *      *  * *  @OA\Property(property="country", type="string", format="string",example="1"),
     *  * *  @OA\Property(property="city", type="string", format="string",example="1"),
     *
     *
     *      summary="This method is to get fuel stations ",
     *      description="This method is to get fuel stations",
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

    public function getFuelStations($perPage, Request $request)
    {
        try {
            $this->storeActivity($request,"");
            if (!$request->user()->hasPermissionTo('fuel_station_view')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            // $automobilesQuery = AutomobileMake::with("makes");

            $fuelStationQuery = FuelStation::with("options.option")
            ->leftJoin('fuel_station_options', 'fuel_station_options.fuel_station_id', '=', 'fuel_stations.id');

            if(!$request->user()->hasRole('superadmin')) {
                $fuelStationQuery =    $fuelStationQuery->where([
                    "created_by" =>$request->user()->id
                ]);
            }


            if (!empty($request->search_key)) {
                $fuelStationQuery = $fuelStationQuery->where(function ($query) use ($request) {
                    $term = $request->search_key;
                    $query->where("fuel_stations.name", "like", "%" . $term . "%");
                    $query->orWhere("fuel_stations.country", "like", "%" . $term . "%");
                    $query->orWhere("fuel_stations.city", "like", "%" . $term . "%");

                });
            }

            if (!empty($request->start_date)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.created_at', "<=", $request->end_date);
            }

            if (!empty($request->start_lat)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.lat', ">=", $request->start_lat);
            }
            if (!empty($request->end_lat)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.lat', "<=", $request->end_lat);
            }
            if (!empty($request->start_long)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.lat', ">=", $request->start_long);
            }
            if (!empty($request->end_long)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.lat', "<=", $request->end_long);
            }

            if (!empty($request->time)) {
                $fuelStationQuery = $fuelStationQuery->where(function ($query) use ($request) {
                    $term = $request->time;
                    $query->whereTime("fuel_stations.opening_time","<=", $term);
                    $query->whereTime("fuel_stations.closing_time",">", $term);

                });
            }


            if (!empty($request->country)) {
                $fuelStationQuery =   $fuelStationQuery->where("country", "like", "%" . $request->country . "%");

            }
            if (!empty($request->city)) {
                $fuelStationQuery =   $fuelStationQuery->where("city", "like", "%" . $request->city . "%");

            }


            if(!empty($request->active_option_ids)) {

                $null_filter = collect(array_filter($request->active_option_ids))->values();
                $active_option_ids =  $null_filter->all();


                if(count($active_option_ids)) {
                    $fuelStationQuery =   $fuelStationQuery
                    ->whereIn("fuel_station_options.option_id",
                    $active_option_ids)
                    ->where("fuel_station_options.is_active",1);
                }

            }


            $fuelStations = $fuelStationQuery
            ->distinct("fuel_stations.id")
            ->select("fuel_stations.*")
            ->orderByDesc("fuel_stations.id")
            ->paginate($perPage);
            return response()->json($fuelStations, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }

      /**
     *
     * @OA\Get(
     *      path="/v1.0/client/fuel-station/{perPage}",
     *      operationId="getFuelStationsClient",
     *      tags={"client.fuel_station_management"},
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
     *   *              @OA\Parameter(
     *         name="time",
     *         in="query",
     *         description="current time",
     *         required=true,
     *  example="10:10"
     *      ),
     *
     *
     *     * *  @OA\Parameter(
* name="country",
* in="query",
* description="country",
* required=true,
* example="country"
* ),
     * *  @OA\Parameter(
* name="city",
* in="query",
* description="city",
* required=true,
* example="city"
* ),
     *
     *
     *
     *
     *
     *
     *
     *
     *
     *
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
* name="start_lat",
* in="query",
* description="start_lat",
* required=true,
* example="3"
* ),
     * *  @OA\Parameter(
* name="end_lat",
* in="query",
* description="end_lat",
* required=true,
* example="2"
* ),
     * *  @OA\Parameter(
* name="start_long",
* in="query",
* description="start_long",
* required=true,
* example="1"
* ),
     * *  @OA\Parameter(
* name="end_long",
* in="query",
* description="end_long",
* required=true,
* example="4"
* ),
*  @OA\Parameter(
*      name="active_option_ids[]",
*      in="query",
*      description="active_option_ids",
*      required=true,
*      example="1,2"
* ),
     *      summary="This method is to get fuel stations client ",
     *      description="This method is to get fuel stations client",
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

    public function getFuelStationsClient($perPage, Request $request)
    {
        try {
            $this->storeActivity($request,"");

            $fuelStationQuery = FuelStation::with("options.option")
            ->leftJoin('fuel_station_options', 'fuel_station_options.fuel_station_id', '=', 'fuel_stations.id');


            if (!empty($request->search_key)) {
                $fuelStationQuery = $fuelStationQuery->where(function ($query) use ($request) {
                    $term = $request->search_key;
                    $query->where("fuel_stations.name", "like", "%" . $term . "%");
                    $query->orWhere("fuel_stations.country", "like", "%" . $term . "%");
                    $query->orWhere("fuel_stations.city", "like", "%" . $term . "%");

                });
            }

            if (!empty($request->start_date)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.created_at', "<=", $request->end_date);
            }

            if (!empty($request->start_lat)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.lat', ">=", $request->start_lat);
            }
            if (!empty($request->end_lat)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.lat', "<=", $request->end_lat);
            }
            if (!empty($request->start_long)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.lat', ">=", $request->start_long);
            }
            if (!empty($request->end_long)) {
                $fuelStationQuery = $fuelStationQuery->where('fuel_stations.lat', "<=", $request->end_long);
            }

            if (!empty($request->time)) {
                $fuelStationQuery = $fuelStationQuery->where(function ($query) use ($request) {
                    $term = $request->time;
                    $query->whereTime("fuel_stations.opening_time","<=", $term);
                    $query->whereTime("fuel_stations.closing_time",">", $term);

                });
            }


            if (!empty($request->country)) {
                $fuelStationQuery =   $fuelStationQuery->where("country", "like", "%" . $request->country . "%");

            }
            if (!empty($request->city)) {
                $fuelStationQuery =   $fuelStationQuery->where("city", "like", "%" . $request->city . "%");

            }


            if(!empty($request->active_option_ids)) {

                $null_filter = collect(array_filter($request->active_option_ids))->values();
                $active_option_ids =  $null_filter->all();


                if(count($active_option_ids)) {
                    $fuelStationQuery =   $fuelStationQuery
                    ->whereIn("fuel_station_options.option_id",
                    $active_option_ids)
                    ->where("fuel_station_options.is_active",1);
                }

            }

            $fuelStations = $fuelStationQuery
            ->distinct("fuel_stations.id")
            ->select("fuel_stations.*")
            ->orderByDesc("fuel_stations.id")
            ->paginate($perPage);
            return response()->json($fuelStations, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }

          /**
     *
     * @OA\Get(
     *      path="/v1.0/client/fuel-station/get/single/{id}",
     *      operationId="getFuelStationByIdClient",
     *      tags={"client.fuel_station_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },
     *    *              @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id",
     *         required=true,
     *  example="1"
     *      ),
     *      summary="This method is to get fuel station by id client ",
     *      description="This method is to get fuel station by id client",
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

    public function getFuelStationByIdClient($id, Request $request)
    {
        try {
            $this->storeActivity($request,"");

            $fuelStation = FuelStation::with("options.option")
            ->where([
                "id" => $id
            ])
            ->first();

            if(!$fuelStation) {
    return response()->json([
        "message" => "no fuel station found"
    ],
404);
            }

            return response()->json($fuelStation, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }

    /**
     *
     *     @OA\Delete(
     *      path="/v1.0/fuel-station/{id}",
     *      operationId="deleteFuelStationById",
     *      tags={"fuel_station_management"},
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
     *      summary="This method is to delete fuel station by id",
     *      description="This method is to delete fuel station by id",
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

    public function deleteFuelStationById($id, Request $request)
    {

        try {
            $this->storeActivity($request,"");
            if (!$request->user()->hasPermissionTo('fuel_station_delete')) {
                return response()->json([
                    "message" => "You can not perform this action"
                ], 401);
            }

            $fuelStationQuery =   FuelStation::where([
                "id" => $id
               ]);
               if(!$request->user()->hasRole('superadmin')) {
                $fuelStationQuery =    $fuelStationQuery->where([
                    "created_by" =>$request->user()->id
                ]);
            }

            $fuelStation = $fuelStationQuery->first();

            $fuelStation->delete();


            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500,$request);
        }
    }
}
