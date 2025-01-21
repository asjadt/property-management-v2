<?php





namespace App\Http\Controllers;

use App\Http\Requests\RentCreateRequest;
use App\Http\Requests\RentUpdateRequest;
use App\Http\Requests\GetIdRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\BusinessUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Rent;
use App\Models\DisabledRent;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RentController extends Controller
{

    use ErrorUtil, UserActivityUtil, BasicUtil;


    /**
     *
     * @OA\Post(
     * path="/v1.0/rents",
     * operationId="createRent",
     * tags={"rents"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to store rents",
     * description="This method is to store rents",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="tenancy_agreement_id", type="string", format="string", example="tenancy_agreement_id"),
     * @OA\Property(property="payment_date", type="string", format="string", example="payment_date"),
     *      *      *  * @OA\Property(property="rent_taken_by", type="string", format="string", example="rent_taken_by"),
     *    *  * @OA\Property(property="remarks", type="string", format="string", example="remarks"),
     * @OA\Property(property="payment_status", type="string", format="string", example="payment_status"),
     * @OA\Property(property="rent_amount", type="string", format="string", example="rent_amount"),
     * @OA\Property(property="paid_amount", type="string", format="string", example="paid_amount"),
     * @OA\Property(property="month", type="string", format="string", example="month"),
     * @OA\Property(property="year", type="string", format="string", example="year"),
     *
     *
     *
     * ),
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function createRent(RentCreateRequest $request)
    {

        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $request_data = $request->validated();


            $request_data["created_by"] = auth()->user()->id;

            if($request_data["paid_amount"] == 0) {
                $request_data["payment_status"] = "unpaid";
            } else if($request_data["rent_amount"] == $request_data["paid_amount"]) {
                $request_data["payment_status"] = "paid";
            } else if($request_data["rent_amount"] > $request_data["paid_amount"]){
                $request_data["payment_status"] = "partially_paid";
            }






            $rent = Rent::create($request_data);



            DB::commit();
            return response($rent, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Put(
     * path="/v1.0/rents",
     * operationId="updateRent",
     * tags={"rents"},
     * security={
     * {"bearerAuth": {}}
     * },
     * summary="This method is to update rents ",
     * description="This method is to update rents ",
     *
     * @OA\RequestBody(
     * required=true,
     * @OA\JsonContent(
     * @OA\Property(property="id", type="number", format="number", example="1"),
     * @OA\Property(property="tenancy_agreement_id", type="string", format="string", example="tenancy_agreement_id"),
     * @OA\Property(property="payment_date", type="string", format="string", example="payment_date"),
     *  * @OA\Property(property="remarks", type="string", format="string", example="remarks"),
     *      *  * @OA\Property(property="rent_taken_by", type="string", format="string", example="rent_taken_by"),
     * @OA\Property(property="payment_status", type="string", format="string", example="payment_status"),
     * @OA\Property(property="rent_amount", type="string", format="string", example="rent_amount"),
     * @OA\Property(property="paid_amount", type="string", format="string", example="paid_amount"),

     * @OA\Property(property="month", type="string", format="string", example="month"),
     * @OA\Property(property="year", type="string", format="string", example="year"),
     *
     * ),
     * ),
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function updateRent(RentUpdateRequest $request)
    {
        DB::beginTransaction();
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");

            $request_data = $request->validated();


            $rent_query_params = [
                "id" => $request_data["id"],
            ];

            $rent = Rent::where($rent_query_params)->first();

            if ($rent) {
                $rent->fill($request_data);
                $rent->save();
            } else {
                return response()->json([
                    "message" => "something went wrong."
                ], 500);
            }

            DB::commit();
            return response($rent, 201);
        } catch (Exception $e) {
            DB::rollBack();
            return $this->sendError($e, 500, $request);
        }
    }




    public function query_filters($query)
    {


        return $query->where('rents.created_by', auth()->user()->id)
        ->when(request()->filled("tenant_ids"), function ($query) {
            return $query->whereHas("tenancy_agreement.tenants",function($query) {
                $tenant_ids = explode(',', request()->input("tenant_ids"));
                   $query->whereIn("tenants.id",$tenant_ids);
            });
        })
        ->when(request()->filled("property_ids"), function ($query) {
            return $query->whereHas("tenancy_agreement",function($query) {
                $property_ids = explode(',', request()->input("property_ids"));
                   $query->whereIn("tenancy_agreements.property_id", $property_ids);
            });
        })
            ->when(request()->filled("start_payment_date"), function ($query) {
                return $query->whereDate(
                    'rents.payment_date',
                    ">=",
                    request()->input("start_payment_date")
                );
            })

            ->when(request()->filled("end_payment_date"), function ($query) {
                return $query->whereDate('rents.payment_date', "<=", request()->input("end_payment_date"));
            })
            ->when(request()->filled("payment_status"), function ($query) {
                return $query->where(
                    'rents.payment_status',
                    request()->input("payment_status")
                );
            })
            ->when(request()->filled("search_key"), function ($query) {
                return $query->where(function ($query) {
                    $term = request()->input("search_key");
                    $query

                        ->orWhere("rents.payment_status", "like", "%" . $term . "%");
                });
            })
            ->when(request()->filled("start_date"), function ($query) {
                return $query->whereDate('rents.created_at', ">=", request()->input("start_date"));
            })
            ->when(request()->filled("end_date"), function ($query) {
                return $query->whereDate('rents.created_at', "<=", request()->input("end_date"));
            });
    }



    /**
     *
     * @OA\Get(
     * path="/v1.0/rents",
     * operationId="getRents",
     * tags={"rents"},
     * security={
     * {"bearerAuth": {}}
     * },

     * @OA\Parameter(
     * name="start_payment_date",
     * in="query",
     * description="start_payment_date",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="end_payment_date",
     * in="query",
     * description="end_payment_date",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="payment_status",
     * in="query",
     * description="payment_status",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="per_page",
     * in="query",
     * description="per_page",
     * required=false,
     * example=""
     * ),

     * @OA\Parameter(
     * name="is_active",
     * in="query",
     * description="is_active",
     * required=false,
     * example=""
     * ),
     * @OA\Parameter(
     * name="start_date",
     * in="query",
     * description="start_date",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="end_date",
     * in="query",
     * description="end_date",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="search_key",
     * in="query",
     * description="search_key",
     * required=false,
     * example=""
     * ),
     * * @OA\Parameter(
     * name="order_by",
     * in="query",
     * description="order_by",
     * required=false,
     * example="ASC"
     * ),
     * * @OA\Parameter(
     * name="id",
     * in="query",
     * description="id",
     * required=false,
     * example=""
     * ),
     *    * @OA\Parameter(
     * name="tenant_ids",
     * in="query",
     * description="id",
     * required=false,
     * example=""
     * ),
     * *    * @OA\Parameter(
     * name="property_ids",
     * in="query",
     * description="id",
     * required=false,
     * example=""
     * ),
     *
     * summary="This method is to get rents ",
     * description="This method is to get rents ",
     *
     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function getRents(Request $request)
    {
        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");



            $query = Rent::query();
            $query = $this->query_filters($query);
            $rents = $this->retrieveData($query, "id", "rents");




            return response()->json($rents, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }
    /**
     *
     * @OA\Delete(
     * path="/v1.0/rents/{ids}",
     * operationId="deleteRentsByIds",
     * tags={"rents"},
     * security={
     * {"bearerAuth": {}}
     * },
     * @OA\Parameter(
     * name="ids",
     * in="path",
     * description="ids",
     * required=true,
     * example="1,2,3"
     * ),
     * summary="This method is to delete rent by id",
     * description="This method is to delete rent by id",
     *

     * @OA\Response(
     * response=200,
     * description="Successful operation",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=401,
     * description="Unauthenticated",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=422,
     * description="Unprocesseble Content",
     * @OA\JsonContent(),
     * ),
     * @OA\Response(
     * response=403,
     * description="Forbidden",
     * @OA\JsonContent()
     * ),
     * * @OA\Response(
     * response=400,
     * description="Bad Request",
     * *@OA\JsonContent()
     * ),
     * @OA\Response(
     * response=404,
     * description="not found",
     * *@OA\JsonContent()
     * )
     * )
     * )
     */

    public function deleteRentsByIds(Request $request, $ids)
    {

        try {
            $this->storeActivity($request, "DUMMY activity", "DUMMY description");


            $idsArray = explode(',', $ids);
            $existingIds = Rent::whereIn('id', $idsArray)
                ->where('rents.created_by', auth()->user()->id)
                ->select('id')
                ->get()
                ->pluck('id')
                ->toArray();

            $nonExistingIds = array_diff($idsArray, $existingIds);

            if (!empty($nonExistingIds)) {

                return response()->json([
                    "message" => "Some or all of the specified data do not exist."
                ], 404);
            }

            Rent::destroy($existingIds);


            return response()->json(["message" => "data deleted sussfully", "deleted_ids" => $existingIds], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }

    }
}
