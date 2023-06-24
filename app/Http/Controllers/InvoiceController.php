<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\InvoiceCreateRequest;
use App\Http\Requests\InvoiceUpdateRequest;

use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Invoice;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    use ErrorUtil, UserActivityUtil;

  /**
    *
 * @OA\Post(
 *      path="/v1.0/invoice-image",
 *      operationId="createInvoiceImage",
 *      tags={"property_management.invoice_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store invoice logo",
 *      description="This method is to store invoice logo",
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

public function createInvoiceImage(ImageUploadRequest $request)
{
    try{
        $this->storeActivity($request,"");

        $insertableData = $request->validated();

        $location =  config("setup-config.invoice_image");

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
 *      path="/v1.0/invoices",
 *      operationId="createInvoice",
 *      tags={"property_management.invoice_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to store invoice",
 *      description="This method is to store invoice",
 *
 *  @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"name","description","logo"},
 *  *             @OA\Property(property="logo", type="string", format="string",example="image.jpg"),
  *             @OA\Property(property="invoice_title", type="string", format="string",example="invoice_title"),
 *            @OA\Property(property="invoice_summary", type="string", format="string",example="invoice_summary"),
 *            @OA\Property(property="business_name", type="string", format="string",example="business_name"),
 *  * *  @OA\Property(property="business_address", type="string", format="string",example="business_address"),
 *  * *  @OA\Property(property="invoice_payment_due", type="number", format="number",example="invoice_payment_due"),
 *  * *  @OA\Property(property="invoice_date", type="string", format="string",example="invoice_date"),
 *  * *  @OA\Property(property="footer_text", type="string", format="string",example="footer_text"),
 *  * *  @OA\Property(property="property_id", type="number", format="number",example="1"),
 *  * *  @OA\Property(property="tenant_id", type="number", format="number",example="1"),
 *     *  * *  @OA\Property(property="number", type="number", format="string",example="1"),
 *     *  * *  @OA\Property(property="long", type="string", format="string",example="1207"),
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

public function createInvoice(InvoiceCreateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return DB::transaction(function () use ($request) {


            $insertableData = $request->validated();
            $insertableData["created_by"] = $request->user()->id;
            $invoice =  Invoice::create($insertableData);
            if(!$invoice) {
                throw new Exception("something went wrong");
            }

            $invoice->invoice_items()->createMany(
                collect($insertableData["images"])->map(function ($image) {
                    return [
                        'image' => $image,
                    ];
                })
            );



            return response($invoice, 201);





        });




    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}

/**
 *
 * @OA\Put(
 *      path="/v1.0/invoices",
 *      operationId="updateInvoice",
 *      tags={"property_management.invoice_management"},
 *       security={
 *           {"bearerAuth": {}}
 *       },
 *      summary="This method is to update invoice",
 *      description="This method is to update invoice",
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

public function updateInvoice(InvoiceUpdateRequest $request)
{
    try {
        $this->storeActivity($request,"");
        return  DB::transaction(function () use ($request) {

            $updatableData = $request->validated();



            $invoice  =  tap(Invoice::where(["id" => $updatableData["id"]]))->update(
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

            return response($invoice, 200);
        });
    } catch (Exception $e) {
        error_log($e->getMessage());
        return $this->sendError($e, 500,$request);
    }
}
/**
 *
 * @OA\Get(
 *      path="/v1.0/invoices/{perPage}",
 *      operationId="getInvoices",
 *      tags={"property_management.invoice_management"},
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
 *      summary="This method is to get invoices ",
 *      description="This method is to get invoices",
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

public function getInvoices($perPage, Request $request)
{
    try {
        $this->storeActivity($request,"");

        // $automobilesQuery = AutomobileMake::with("makes");

        $invoiceQuery = new Invoice();

        if (!empty($request->search_key)) {
            $invoiceQuery = $invoiceQuery->where(function ($query) use ($request) {
                $term = $request->search_key;
                $query->where("name", "like", "%" . $term . "%");
            });
        }

        if (!empty($request->start_date)) {
            $invoiceQuery = $invoiceQuery->where('created_at', ">=", $request->start_date);
        }

        if (!empty($request->end_date)) {
            $invoiceQuery = $invoiceQuery->where('created_at', "<=", $request->end_date);
        }

        $invoices = $invoiceQuery->orderByDesc("id")->paginate($perPage);

        return response()->json($invoices, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}



/**
 *
 * @OA\Get(
 *      path="/v1.0/invoices/get/single/{id}",
 *      operationId="getInvoiceById",
 *      tags={"property_management.invoice_management"},
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

 *      summary="This method is to get invoice by id",
 *      description="This method is to get invoice by id",
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

public function getInvoiceById($id, Request $request)
{
    try {
        $this->storeActivity($request,"");


        $invoice = Invoice::where([
            "id" => $id
        ])
        ->first();

        if(!$invoice) {
     return response()->json([
"message" => "no invoice found"
],404);
        }


        return response()->json($invoice, 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}










/**
 *
 *     @OA\Delete(
 *      path="/v1.0/invoices/{id}",
 *      operationId="deleteInvoiceById",
 *      tags={"property_management.invoice_management"},
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
 *      summary="This method is to delete invoice by id",
 *      description="This method is to delete invoice by id",
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

public function deleteInvoiceById($id, Request $request)
{

    try {
        $this->storeActivity($request,"");



        $invoice = Invoice::where([
            "id" => $id
        ])
        ->first();

        if(!$invoice) {
     return response()->json([
"message" => "no invoice found"
],404);
        }
        $invoice->delete();

        return response()->json(["ok" => true], 200);
    } catch (Exception $e) {

        return $this->sendError($e, 500,$request);
    }
}
}
