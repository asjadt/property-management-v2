<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\LandlordRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Mail\ForgetPasswordMail;
use App\Models\Business;
use App\Models\Landlord;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class LandlordController extends Controller
{
    use ErrorUtil, UserActivityUtil, BasicUtil;
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
        try {
            $this->storeActivity($request, "");

            $request_data = $request->validated();

            $location =  config("setup-config.landlord_image");

            $new_file_name = time() . '_' . str_replace(' ', '_', $request_data["image"]->getClientOriginalName());

            $request_data["image"]->move(public_path($location), $new_file_name);


            return response()->json(["image" => $new_file_name, "location" => $location, "full_location" => ("/" . $location . "/" . $new_file_name)], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
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

    public function createLandlord(LandlordRequest $request)
    {
        try {
            $this->storeActivity($request, "");
            return DB::transaction(function () use ($request) {

                $request_data = $request->validated();

                /** @var \App\Models\User $authUser */
                $authUser = Auth::user();

                // 1. CREATE USER ACCOUNT FOR THE LANDLORD
                $user = User::create([
                    'first_Name'     => $request_data['first_Name'],
                    'last_Name'      => $request_data['last_Name'],
                    'email'          => $request_data['email'],
                    'phone'          => $request_data['phone'] ?? null,
                    'image'          => $request_data['image'] ?? null,
                    'address_line_1' => $request_data['address_line_1'] ?? null,
                    'address_line_2' => $request_data['address_line_2'] ?? null,
                    'country'        => $request_data['country'] ?? null,
                    'city'           => $request_data['city'] ?? null,
                    'postcode'       => $request_data['postcode'] ?? null,
                    'lat'            => $request_data['lat'] ?? null,
                    'long'           => $request_data['long'] ?? null,
                    'password'       => Hash::make('12345678@We'),
                    'is_active'      => true,
                    'remember_token' => Str::random(10),
                    'created_by'     => $authUser->id,
                    'business_id'    =>  $authUser->business_id,
                ]);

                $user->assignRole('landlord');

                // 2. CREATE LANDLORD RECORD
                $request_data["created_by"] = $authUser->id;
                $request_data["user_id"] = $user->id; // Link the newly created User
                $landlord =  Landlord::create($request_data);
                $landlord->generated_id = Str::random(4) . $landlord->id . Str::random(4);
                $landlord->save();

                return response($landlord, 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
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

    public function updateLandlord(LandlordRequest $request)
    {
        try {
            $this->storeActivity($request, "");

            // LANDLORD-ROLE USERS CANNOT UPDATE LANDLORD RECORDS DIRECTLY — admin only
            /** @var \App\Models\User $authUser */
            $authUser = $request->user();
            if ($authUser->hasRole('landlord')) {
                return response()->json(['message' => 'Forbidden — use the landlord portal to update your own profile'], 403);
            }

            return DB::transaction(function () use ($request, $authUser) {

                $request_data = $request->validated();

                $landlord = tap(Landlord::where([
                    "id"         => $request_data["id"],
                    "created_by" => $authUser->id,
                ]))->update(
                    collect($request_data)->only([
                        'first_Name',
                        'last_Name',
                        'phone',
                        'image',
                        'address_line_1',
                        'address_line_2',
                        'country',
                        'city',
                        'postcode',
                        'lat',
                        'long',
                        'email',
                        'files',
                    ])->toArray()
                )->first();

                // 2. SYNC WITH LINKED USER ACCOUNT IF IT EXISTS
                if ($landlord && $landlord->user_id) {
                    $user = User::find($landlord->user_id);
                    if ($user) {
                        $user->update(
                            collect($request_data)->only([
                                'first_Name',
                                'last_Name',
                                'phone',
                                'image',
                                'address_line_1',
                                'address_line_2',
                                'country',
                                'city',
                                'postcode',
                                'lat',
                                'long',
                                'email',
                            ])->toArray()
                        );
                    }
                }

                return response($landlord, 200);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     * path="/v1.0/landlords/{perPage}",
     * operationId="getLandlords",
     * tags={"property_management.landlord_management"},
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
            $this->storeActivity($request, "");
            $currentDate = Carbon::now();
            $endDate = $currentDate->copy()->addDays(15);


            // SCOPE: admin sees all their landlords; landlord-User sees only their own record
            $landlordQuery = Landlord::with('properties', 'properties.property_tenants')
                ->forAuthUser();

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

            if (!empty($request->property_id)) {
                $landlordQuery = $landlordQuery->whereHas('properties', function ($query) {
                    $query->whereIn("properties.id", [request()->input("property_id")]);
                });
            }

            if (!empty($request->property_ids)) {
                $null_filter = collect(array_filter($request->property_ids))->values();
                $property_ids =  $null_filter->all();
                if (count($property_ids)) {
                    $landlordQuery = $landlordQuery->whereHas('properties', function ($query) use ($property_ids) {
                        $query->whereIn("properties.id", $property_ids);
                    });
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
                 (SELECT COUNT(property_landlords.id) FROM property_landlords WHERE property_landlords.landlord_id = landlords.id),
                 0
             ) AS total_properties
             '),

                    DB::raw(

                        '
      COALESCE(
    (
        SELECT SUM(invoices.total_amount)
        FROM invoices
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
    ),
    0
) AS total_amount

             '

                    ),
                    DB::raw('
           COALESCE(
    (
        SELECT COUNT(invoices.id)
        FROM invoices
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
    ),
    0
) AS total_invoices
             '),
                    DB::raw(
                        '
      COALESCE(
    (
        SELECT SUM(invoice_payments.amount)
        FROM invoices
        LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
    ),
    0
) AS total_paid

             '
                    ),
                    DB::raw(
                        '
                COALESCE(
              COALESCE(
    (
        SELECT SUM(invoices.total_amount)
        FROM invoices
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
    ),
    0
)

                -
                COALESCE(
    (
        SELECT SUM(invoice_payments.amount)
        FROM invoices
        LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
    ),
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
    (
        SELECT SUM(invoices.total_amount)
        FROM invoices
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
        AND invoices.due_date >= "' . $currentDate . '"
        AND invoices.due_date <= "' . $endDate . '"
    ),
    0
)

                    -
                    COALESCE(
    (
        SELECT SUM(invoice_payments.amount)
        FROM invoices
        LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
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
    (
        SELECT SUM(invoices.total_amount)
        FROM invoices
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
        AND invoices.due_date < "' . today() . '"
    ),
    0
)

                    -
                 COALESCE(
    (
        SELECT SUM(invoice_payments.amount)
        FROM invoices
        LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
        AND invoices.due_date < "' . today() . '"
    ),
    0
)

                 )
                 as total_over_due

                 '
                    ),


                );

            // FIX: use parameterised HAVING to prevent SQL injection
            if (!empty($request->min_total_due)) {
                $landlordQuery = $landlordQuery->havingRaw('total_due >= ?', [(float) $request->min_total_due]);
            }
            if (!empty($request->max_total_due)) {
                $landlordQuery = $landlordQuery->havingRaw('total_due <= ?', [(float) $request->max_total_due]);
            }
            if (!empty($request->min_total_over_due)) {
                $landlordQuery = $landlordQuery->havingRaw('total_over_due >= ?', [(float) $request->min_total_over_due]);
            }
            if (!empty($request->max_total_over_due)) {
                $landlordQuery = $landlordQuery->havingRaw('total_over_due <= ?', [(float) $request->max_total_over_due]);
            }

            $landlords =  $landlordQuery
                ->groupBy("landlords.id")
                ->orderBy("landlords.first_Name", $request->order_by)->paginate($perPage);

            return response()->json($landlords, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Get(
     * path="/v2.0/landlords",
     * operationId="getLandlordsV2",
     * tags={"property_management.landlord_management"},
     * security={
     * {"bearerAuth": {}}},

     * * @OA\Parameter(
     *  name="per_page",
     *  in="query",
     *  description="per_page",
     *  required=false,
     *  example="6"
     *  ),
     * * @OA\Parameter(
     *  name="landlord_ids ",
     *  in="query",
     *  description="landlord_ids ",
     *  required=false,
     *  example="1,2"
     *  ),
     * *  @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=false,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=false,
     * example="2019-06-29"
     * ),
     * *  @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=false,
     * example="ASC"
     * ),
     * *  @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=false,
     * example="search_key"
     * ),
     * *  @OA\Parameter(
     * name="property_id",
     * in="query",
     * description="property_id",
     * required=false,
     * example="1"
     * ),
     *  @OA\Parameter(
     *      name="property_ids[]",
     *      in="query",
     *      description="property_ids",
     *      required=false,
     *      example="1,2"
     * ),
     *
     * *  @OA\Parameter(
     * name="min_total_due",
     * in="query",
     * description="min_total_due",
     * required=false,
     * example="1"
     * ),
     * *  @OA\Parameter(
     * name="max_total_due",
     * in="query",
     * description="max_total_due",
     * required=false,
     * example="1"
     * ),
     * *  @OA\Parameter(
     * name="min_total_over_due",
     * in="query",
     * description="min_total_over_due",
     * required=false,
     * example="1"
     * ),
     * *  @OA\Parameter(
     * name="max_total_over_due",
     * in="query",
     * description="max_total_over_due",
     * required=false,
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
     *          description="Unprocessable Content",
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

    public function getLandlordsV2(Request $request)
    {
        try {
            $this->storeActivity($request, "getLandlordsV2");

            $currentDate = Carbon::now();
            $endDate = $currentDate->copy()->addDays(15);

            // SCOPE: admin sees all their landlords; landlord-User sees only their own record
            $landlordQuery = Landlord::with(['properties', 'properties.property_tenants'])
                ->forAuthUser();

            /** -------------------------
             * Search Filter
             * ------------------------- */
            if ($request->filled('search_key')) {
                $searchTerm = $request->search_key;
                $terms = preg_split('/\s+/', $searchTerm);

                $landlordQuery->where(function ($query) use ($terms, $searchTerm) {
                    foreach ($terms as $term) {
                        $query->orWhere(function ($inner) use ($term) {
                            $inner->where('landlords.first_name', 'like', "%{$term}%")
                                ->orWhere('landlords.last_name', 'like', "%{$term}%");
                        });
                    }

                    $searchFields = [
                        'phone',
                        'address_line_1',
                        'address_line_2',
                        'country',
                        'city',
                        'postcode',
                        'email'
                    ];

                    foreach ($searchFields as $field) {
                        $query->orWhere("landlords.{$field}", 'like', "%{$searchTerm}%");
                    }
                });
            }

            /** -------------------------
             * Property Filters
             * ------------------------- */
            if ($request->filled('property_id')) {
                $landlordQuery->whereHas('properties', function ($query) use ($request) {
                    $query->where('properties.id', $request->property_id);
                });
            }

            if ($request->filled('property_ids')) {
                $propertyIds = collect($request->property_ids)->filter()->values()->all();
                if (!empty($propertyIds)) {
                    $landlordQuery->whereHas('properties', function ($query) use ($propertyIds) {
                        $query->whereIn('properties.id', $propertyIds);
                    });
                }
            }

            /** -------------------------
             * Date Filters
             * ------------------------- */
            if ($request->filled('start_date')) {
                $landlordQuery->where('landlords.created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $landlordQuery->where('landlords.created_at', '<=', $request->end_date);
            }

            /** -------------------------
             * Landlord IDs Filter
             * ------------------------- */
            if ($request->filled('landlord_ids')) {
                $ids = array_map('intval', explode(',', $request->landlord_ids));
                $landlordQuery->whereIn('landlords.id', $ids);
            }

            /** -------------------------
             * Select with Calculations
             * ------------------------- */
            $landlordQuery->select([
                'landlords.*',
                DB::raw("
                COALESCE(
                    (SELECT COUNT(property_landlords.id)
                     FROM property_landlords
                     WHERE property_landlords.landlord_id = landlords.id), 0
                ) AS total_properties
            "),
                DB::raw("
                COALESCE(
                    (SELECT SUM(invoices.total_amount)
                     FROM invoices
                     JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                     WHERE invoice_landlords.landlord_id = landlords.id), 0
                ) AS total_amount
            "),
                DB::raw("
                COALESCE(
                    (SELECT COUNT(invoices.id)
                     FROM invoices
                     JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                     WHERE invoice_landlords.landlord_id = landlords.id), 0
                ) AS total_invoices
            "),
                DB::raw("
                COALESCE(
                    (SELECT SUM(invoice_payments.amount)
                     FROM invoices
                     LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
                     JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                     WHERE invoice_landlords.landlord_id = landlords.id), 0
                ) AS total_paid
            "),
                DB::raw("
                COALESCE(
                    COALESCE(
                        (SELECT SUM(invoices.total_amount)
                         FROM invoices
                         JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                         WHERE invoice_landlords.landlord_id = landlords.id), 0
                    )
                    -
                    COALESCE(
                        (SELECT SUM(invoice_payments.amount)
                         FROM invoices
                         LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
                         JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                         WHERE invoice_landlords.landlord_id = landlords.id), 0
                    )
                ) AS total_due
            "),
                DB::raw("
                COALESCE(
                    COALESCE(
                        (SELECT SUM(invoices.total_amount)
                         FROM invoices
                         JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                         WHERE invoice_landlords.landlord_id = landlords.id
                           AND invoices.due_date BETWEEN ? AND ?), 0
                    )
                    -
                    COALESCE(
                        (SELECT SUM(invoice_payments.amount)
                         FROM invoices
                         LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
                         JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                         WHERE invoice_landlords.landlord_id = landlords.id
                           AND invoices.due_date BETWEEN ? AND ?), 0
                    )
                ) AS total_due_next_15_days
            "),
                DB::raw("
                COALESCE(
                    COALESCE(
                        (SELECT SUM(invoices.total_amount)
                         FROM invoices
                         JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                         WHERE invoice_landlords.landlord_id = landlords.id
                           AND invoices.due_date < ?), 0
                    )
                    -
                    COALESCE(
                        (SELECT SUM(invoice_payments.amount)
                         FROM invoices
                         LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
                         JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                         WHERE invoice_landlords.landlord_id = landlords.id
                           AND invoices.due_date < ?), 0
                    )
                ) AS total_over_due
            "),
            ])->addBinding([
                $currentDate,
                $endDate,
                $currentDate,
                $endDate,
                today(),
                today()
            ], 'select');

            /** -------------------------
             * Having Filters
             * ------------------------- */
            if ($request->filled('min_total_due')) {
                $landlordQuery->havingRaw("total_due >= ?", [$request->min_total_due]);
            }
            if ($request->filled('max_total_due')) {
                $landlordQuery->havingRaw("total_due <= ?", [$request->max_total_due]);
            }
            if ($request->filled('min_total_over_due')) {
                $landlordQuery->havingRaw("total_over_due >= ?", [$request->min_total_over_due]);
            }
            if ($request->filled('max_total_over_due')) {
                $landlordQuery->havingRaw("total_over_due <= ?", [$request->max_total_over_due]);
            }

            /** -------------------------
             * Execute Query
             * ------------------------- */
            $landlords = $landlordQuery
                ->groupBy('landlords.id');

            $result =  $this->retrieveData($landlords, "first_name", "landlords");
            //     ->orderBy('landlords.first_name', $request->order_by ?? 'asc')
            //     ->paginate($request->per_page);

            return response()->json($result, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/landlords/optimized/{perPage}",
     *      operationId="getLandlordsOptimized",
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
     * *  @OA\Parameter(
     * name="ids",
     * in="query",
     * description="ids",
     * required=false,
     * example=""
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

    public function getLandlordsOptimized($perPage, Request $request)
    {
        try {
            $this->storeActivity($request, "");

            $todayDate = today();

            // SCOPE: admin sees all their landlords; landlord-User sees only their own record
            $landlordQuery = Landlord::forAuthUser();

            // Search filter
            if ($request->filled('search_key')) {
                $searchTerm = $request->search_key;
                $terms = preg_split('/\s+/', $searchTerm);

                $landlordQuery->where(function ($query) use ($terms, $searchTerm) {
                    foreach ($terms as $term) {
                        $query->orWhere(function ($inner) use ($term) {
                            $inner->where('landlords.first_Name', 'like', "%{$term}%")
                                ->orWhere('landlords.last_Name', 'like', "%{$term}%");
                        });
                    }

                    $searchFields = ['phone', 'address_line_1', 'address_line_2', 'country', 'city', 'postcode', 'email'];
                    foreach ($searchFields as $field) {
                        $query->orWhere("landlords.{$field}", 'like', "%{$searchTerm}%");
                    }
                });
            }

            // IDs filter
            if ($request->filled('ids')) {
                $ids = array_map('intval', explode(',', $request->ids));
                $landlordQuery->whereIn('landlords.id', $ids);
            }

            // Property filters
            if ($request->filled('property_id')) {
                $landlordQuery->whereHas('properties', function ($query) use ($request) {
                    $query->where('properties.id', $request->property_id);
                });
            }

            if ($request->filled('property_ids')) {
                $propertyIds = collect($request->property_ids)->filter()->values()->all();
                if (!empty($propertyIds)) {
                    $landlordQuery->whereHas('properties', function ($query) use ($propertyIds) {
                        $query->whereIn('properties.id', $propertyIds);
                    });
                }
            }

            // Date filters
            if ($request->filled('start_date')) {
                $landlordQuery->where('landlords.created_at', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $landlordQuery->where('landlords.created_at', '<=', $request->end_date);
            }

            // Select fields with calculations
            $landlordQuery->select([
                'landlords.id',
                'landlords.generated_id',
                'landlords.first_Name',
                'landlords.last_Name',
                'landlords.phone',
                DB::raw("
                    COALESCE(
                        (SELECT COUNT(invoices.id)
                         FROM invoices
                         JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                         WHERE invoice_landlords.landlord_id = landlords.id
                           AND invoices.status != 'draft'), 0
                    ) AS total_invoices
                "),
                DB::raw("
                    COALESCE(
                        (SELECT SUM(invoices.total_amount)
                         FROM invoices
                         JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                         WHERE invoice_landlords.landlord_id = landlords.id
                           AND invoices.status != 'draft'), 0
                    )
                    -
                    COALESCE(
                        (SELECT SUM(invoice_payments.amount)
                         FROM invoices
                         LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
                         JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                         WHERE invoice_landlords.landlord_id = landlords.id
                           AND invoices.status != 'draft'), 0
                    ) AS total_due
                "),
                DB::raw("
                    COALESCE(
                        (SELECT SUM(invoices.total_amount)
                         FROM invoices
                         JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                         WHERE invoice_landlords.landlord_id = landlords.id
                           AND invoices.due_date < ?
                           AND invoices.status != 'draft'), 0
                    )
                    -
                    COALESCE(
                        (SELECT SUM(invoice_payments.amount)
                         FROM invoices
                         LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
                         JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
                         WHERE invoice_landlords.landlord_id = landlords.id
                           AND invoices.due_date < ?
                           AND invoices.status != 'draft'), 0
                    ) AS total_over_due
                ")
            ])->addBinding([$todayDate, $todayDate], 'select');

            // Having filters
            if ($request->filled('min_total_due')) {
                $landlordQuery->havingRaw('total_due >= ?', [$request->min_total_due]);
            }
            if ($request->filled('max_total_due')) {
                $landlordQuery->havingRaw('total_due <= ?', [$request->max_total_due]);
            }
            if ($request->filled('min_total_over_due')) {
                $landlordQuery->havingRaw('total_over_due >= ?', [$request->min_total_over_due]);
            }
            if ($request->filled('max_total_over_due')) {
                $landlordQuery->havingRaw('total_over_due <= ?', [$request->max_total_over_due]);
            }

            $landlords = $landlordQuery
                ->groupBy('landlords.id')
                ->orderBy('landlords.first_Name', $request->order_by ?? 'asc')
                ->paginate($perPage);

            return response()->json($landlords, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
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
            $this->storeActivity($request, "");

            // $automobilesQuery = AutomobileMake::with("makes");

            // SCOPE: admin sees all their landlords; landlord-User sees only their own record
            $landlordQuery = Landlord::with('properties', 'properties.property_tenants')
                ->forAuthUser();

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
                $landlordQuery = $landlordQuery->where('landlords.created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $landlordQuery = $landlordQuery->where('landlords.created_at', "<=", $request->end_date);
            }

            if (!empty($request->property_id)) {
                $landlordQuery = $landlordQuery->whereHas('properties', function ($query) {
                    $query->whereIn("properties.id", [request()->input("property_id")]);
                });
            }

            if (!empty($request->property_ids)) {
                $null_filter = collect(array_filter($request->property_ids))->values();
                $property_ids =  $null_filter->all();
                if (count($property_ids)) {
                    $landlordQuery = $landlordQuery->whereHas('properties', function ($query) use ($property_ids) {
                        $query->whereIn("properties.id", $property_ids);
                    });
                }
            }

            if (!empty($request->ids)) {
                $ids = explode(',', request()->input("ids"));
                $landlordQuery =  $landlordQuery->whereIn("landlords.id", $ids);
            }


            $currentDate = Carbon::now();
            $endDate = $currentDate->copy()->addDays(15);
            $landlordQuery = $landlordQuery

                ->select(
                    "landlords.*",
                    DB::raw('
             COALESCE(
                 (SELECT COUNT(property_landlords.id) FROM property_landlords WHERE property_landlords.landlord_id = landlords.id),
                 0
             ) AS total_properties
             '),
                    DB::raw('
             COALESCE(
    (
        SELECT COUNT(invoices.id)
        FROM invoices
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
    ),
    0
) AS total_invoices

              '),

                    DB::raw(

                        '
            COALESCE(
    (
        SELECT SUM(invoices.total_amount)
        FROM invoices
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
    ),
    0
) AS total_amount

              '

                    ),
                    DB::raw(
                        '
             COALESCE(
    (
        SELECT SUM(invoice_payments.amount)
        FROM invoices
        LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
    ),
    0
) AS total_paid

              '
                    ),
                    DB::raw(
                        '
                 COALESCE(
                COALESCE(
    (
        SELECT SUM(invoices.total_amount)
        FROM invoices
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
    ),
    0
)

                 -
                COALESCE(
    (
        SELECT SUM(invoice_payments.amount)
        FROM invoices
        LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
    ),
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
    (
        SELECT SUM(invoices.total_amount)
        FROM invoices
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
        AND invoices.due_date >= "' . $currentDate . '"
        AND invoices.due_date <= "' . $endDate . '"
    ),
    0
)

                     -
                    COALESCE(
    (
        SELECT SUM(invoice_payments.amount)
        FROM invoices
        LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
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
    (
        SELECT SUM(invoices.total_amount)
        FROM invoices
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
        AND invoices.due_date < "' . today() . '"
    ),
    0
)

                     -
                    COALESCE(
    (
        SELECT SUM(invoice_payments.amount)
        FROM invoices
        LEFT JOIN invoice_payments ON invoices.id = invoice_payments.invoice_id
        JOIN invoice_landlords ON invoice_landlords.invoice_id = invoices.id
        WHERE invoice_landlords.landlord_id = landlords.id
        AND invoices.due_date < "' . today() . '"
    ),
    0
)

                  )
                  as total_over_due

                  '
                    ),


                );

            // FIX: use parameterised HAVING to prevent SQL injection
            if (!empty($request->min_total_due)) {
                $landlordQuery = $landlordQuery->havingRaw('total_due >= ?', [(float) $request->min_total_due]);
            }
            if (!empty($request->max_total_due)) {
                $landlordQuery = $landlordQuery->havingRaw('total_due <= ?', [(float) $request->max_total_due]);
            }
            if (!empty($request->min_total_over_due)) {
                $landlordQuery = $landlordQuery->havingRaw('total_over_due >= ?', [(float) $request->min_total_over_due]);
            }
            if (!empty($request->max_total_over_due)) {
                $landlordQuery = $landlordQuery->havingRaw('total_over_due <= ?', [(float) $request->max_total_over_due]);
            }

            $landlords =  $landlordQuery
                ->groupBy("landlords.id")
                ->orderBy("landlords.first_Name", $request->order_by)->get();

            return response()->json($landlords, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Get(
     *      path="/v1.0/landlords/get/all/optimized",
     *      operationId="getAllLandlordsOptimized",
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

    public function getAllLandlordsOptimized(Request $request)
    {
        try {
            $this->storeActivity($request, "");


            // $automobilesQuery = AutomobileMake::with("makes");

            // SCOPE: admin sees all their landlords; landlord-User sees only their own record
            $landlordQuery = Landlord::forAuthUser();

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
                $landlordQuery = $landlordQuery->where('landlords.created_at', ">=", $request->start_date);
            }
            if (!empty($request->end_date)) {
                $landlordQuery = $landlordQuery->where('landlords.created_at', "<=", $request->end_date);
            }


            if (!empty($request->property_id)) {
                $landlordQuery = $landlordQuery->whereHas('properties', function ($query) {
                    $query->whereIn("properties.id", [request()->input("property_id")]);
                });
            }

            if (!empty($request->property_ids)) {
                $null_filter = collect(array_filter($request->property_ids))->values();
                $property_ids =  $null_filter->all();
                if (count($property_ids)) {
                    $landlordQuery = $landlordQuery->whereHas('properties', function ($query) use ($property_ids) {
                        $query->whereIn("properties.id", $property_ids);
                    });
                }
            }

            if (!empty($request->ids)) {
                $ids = explode(',', request()->input("ids"));
                $landlordQuery =  $landlordQuery->whereIn("landlords.id", $ids);
            }

            $currentDate = Carbon::now();
            $endDate = $currentDate->copy()->addDays(15);





            $landlords =  $landlordQuery
                ->select(
                    "landlords.id",
                    "landlords.generated_id",
                    'landlords.first_Name',
                    'landlords.last_Name',

                )
                ->groupBy("landlords.id")
                ->orderBy("landlords.first_Name", $request->order_by)->get();

            return response()->json($landlords, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
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
            $this->storeActivity($request, "");


            // SCOPE: forAuthUser() handles both admin and landlord-User visibility
            $landlord = Landlord::with('properties')
                ->where('landlords.generated_id', $id)
                ->select(
                    'landlords.*',
                    DB::raw('
            COALESCE(
                (SELECT COUNT(property_landlords.id) FROM property_landlords WHERE property_landlords.landlord_id = landlords.id),
                0
            ) AS total_properties
            '),
                )
                ->first();

            if (!$landlord) {
                return response()->json([
                    "message" => "no landlord found"
                ], 404);
            }


            return response()->json($landlord, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
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
            $this->storeActivity($request, "");

            // LANDLORD-ROLE USERS CANNOT DELETE LANDLORD RECORDS — admin only
            /** @var \App\Models\User $authUser */
            $authUser = $request->user();
            if ($authUser->hasRole('landlord')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $business = Business::where([
                "owner_id" => $authUser->id
            ])->first();

            if (!$business) {
                return response()->json([
                    "message" => "you don't have a valid business"
                ], 401);
            }


            if (!($business->pin == $request->header("pin"))) {
                return response()->json([
                    "message" => "invalid pin"
                ], 401);
            }

            $landlord = Landlord::where([
                "id"         => $id,
                "created_by" => $authUser->id
            ])
                ->first();

            if (!$landlord) {

                return response()->json([
                    "message" => "no landlord found"
                ], 404);
            }
            $landlord->delete();

            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     * Invite a landlord by creating or linking their User account
     * and sending a password-reset email.
     *
     * POST /v1.0/landlords/{id}/invite
     */
    public function inviteLandlord($id, Request $request)
    {
        try {
            $this->storeActivity($request, "");

            // ADMIN ONLY — landlord-role users cannot send invites
            /** @var \App\Models\User $authUser */
            $authUser = $request->user();
            if ($authUser->hasRole('landlord')) {
                return response()->json(['message' => 'Forbidden'], 403);
            }

            $validated = $request->validate([
                'client_site' => 'required|string',
            ]);

            // GET THE LANDLORD RECORD
            $landlord = Landlord::where([
                'id'         => $id,
                'created_by' => $authUser->id,
            ])->firstOrFail();

            return DB::transaction(function () use ($landlord, $authUser, $validated) {

                // CASE A — landlord already has a linked User
                if ($landlord->user_id) {
                    $user = User::findOrFail($landlord->user_id);
                } else {
                    // CASE B — check email collision
                    $existingUser = User::where('email', $landlord->email)->first();

                    if ($existingUser) {
                        // LINK TO EXISTING USER
                        if (!$existingUser->hasRole('landlord')) {
                            $existingUser->assignRole('landlord');
                        }
                        $landlord->user_id = $existingUser->id;
                        $landlord->save();
                        $user = $existingUser;
                    } else {
                        // CREATE NEW USER
                        $user = User::create([
                            'first_Name'     => $landlord->first_Name,
                            'last_Name'      => $landlord->last_Name,
                            'email'          => $landlord->email,
                            'phone'          => $landlord->phone,
                            'image'          => $landlord->image,
                            'address_line_1' => $landlord->address_line_1,
                            'address_line_2' => $landlord->address_line_2,
                            'country'        => $landlord->country,
                            'city'           => $landlord->city,
                            'postcode'       => $landlord->postcode,
                            'lat'            => $landlord->lat,
                            'long'           => $landlord->long,
                            'password'       => Hash::make('12345678@We'),
                            'is_active'      => false,
                            'remember_token' => Str::random(10),
                            'created_by'     => $authUser->id,
                            'business_id'    => $authUser->business_id,
                        ]);
                        $user->assignRole('landlord');
                        $landlord->user_id = $user->id;
                        $landlord->save();
                    }
                }

                // GENERATE PASSWORD-RESET TOKEN AND SEND INVITE EMAIL
                $token = Str::random(30);
                $user->resetPasswordToken   = $token;
                $user->resetPasswordExpires = Carbon::now()->addDay();
                $user->save();

                if (env('SEND_EMAIL', false)) {
                    Mail::to($user->email)->send(new ForgetPasswordMail($user, $validated['client_site']));
                }

                return response()->json(['ok' => true, 'user_id' => $user->id], 200);
            });
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }
}
