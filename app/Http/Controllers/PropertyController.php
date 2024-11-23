<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\PropertyAgreementCreateRequest;
use App\Http\Requests\PropertyCreateRequest;
use App\Http\Requests\PropertyCreateRequestV2;
use App\Http\Requests\PropertyUpdateRequest;
use App\Http\Requests\PropertyUpdateRequestV2;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\Property;
use App\Models\PropertyAgreement;
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
        try {
            $this->storeActivity($request, "");

            $request_data = $request->validated();

            $location =  config("setup-config.property_image");

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
             $this->storeActivity($request, "");
             return DB::transaction(function () use ($request) {



                 $request_data = $request->validated();
                 $request_data["created_by"] = $request->user()->id;

                 $reference_no_exists =  DB::table('properties')->where(
                     [
                         'reference_no' => $request_data['reference_no'],
                         "created_by" => $request->user()->id
                     ]
                 )->exists();
                 if ($reference_no_exists) {
                     $error =  [
                         "message" => "The given data was invalid.",
                         "errors" => ["reference_no" => ["The reference no has already been taken."]]
                     ];
                     throw new Exception(json_encode($error), 422);
                 }




                 $property =  Property::create($request_data);
                 $property->generated_id = Str::random(4) . $property->id . Str::random(4);
                 $property->save();

                 // for($i=0;$i<500;$i++) {
                 //     $property =  Property::create([

                 //          'name' => $request_data["name"] . Str::random(4),
                 //          'image',
                 //          'address'=> $request_data["address"] . Str::random(4),
                 //          'country'=> $request_data["country"] . Str::random(4),
                 //          'city'=> $request_data["city"] . Str::random(4),
                 //          'postcode'=> $request_data["postcode"] . Str::random(4),
                 //          "town"=> $request_data["town"] . Str::random(4),
                 //          "lat"=> $request_data["lat"] . Str::random(4),
                 //          "long"=> $request_data["long"] . Str::random(4),
                 //          'type' => $request_data["type"],
                 //          'reference_no'=> $request_data["reference_no"] . Str::random(4),
                 //          'landlord_id'=> $request_data["landlord_id"],
                 //          "created_by"=>$request->user()->id,
                 //          'is_active'=>1,
                 //     ]);
                 //     $property->generated_id = Str::random(4) . $property->id . Str::random(4);
                 //     $property->save();
                 // }






                 if (!empty($request_data['tenant_ids'])) {
                     $property->property_tenants()->sync($request_data['tenant_ids'], []);
                 }



                 return response($property, 201);
             });
         } catch (Exception $e) {

             return $this->sendError($e, 500, $request);
         }
     }
    /**
 * @OA\Post(
 *      path="/v2.0/properties",
 *      operationId="createPropertyV2",
 *      tags={"property_management.property_management"},
 *      security={
 *          {"bearerAuth": {}}
 *      },
 *      summary="This method is to store property",
 *      description="This method is to store property",
 *      @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"name", "image", "address", "country", "city", "postcode", "type", "reference_no"},
 *            @OA\Property(property="image", type="string", format="string", example="image.jpg"),
 *            @OA\Property(property="name", type="string", format="string", example="Rifat"),
 *            @OA\Property(property="address", type="string", format="string", example="address"),
 *            @OA\Property(property="country", type="string", format="string", example="Bangladesh"),
 *            @OA\Property(property="city", type="string", format="string", example="Dhaka"),
 *            @OA\Property(property="postcode", type="string", format="string", example="1207"),
 *            @OA\Property(property="town", type="string", format="string", example="town"),
 *            @OA\Property(property="lat", type="string", format="string", example="23.8103"),
 *            @OA\Property(property="long", type="string", format="string", example="90.4125"),
 *            @OA\Property(property="type", type="string", format="string", example="residential"),
 *            @OA\Property(property="reference_no", type="string", format="string", example="REF12345"),
 *            @OA\Property(property="landlord_id", type="string", format="numeric", example="1"),
 *            @OA\Property(property="date_of_instruction", type="string", format="date", example="2024-11-01"),
 *            @OA\Property(property="howDetached", type="string", format="string", example="fully detached"),
 *            @OA\Property(property="propertyFloor", type="string", format="string", example="Ground Floor"),
 *            @OA\Property(property="min_price", type="string", format="string", example="100000"),
 *            @OA\Property(property="max_price", type="string", format="string", example="500000"),
 *            @OA\Property(property="purpose", type="string", format="string", example="for sale"),
 *            @OA\Property(property="property_door_no", type="string", format="string", example="10A"),
 *            @OA\Property(property="property_road", type="string", format="string", example="Main Street"),
 *            @OA\Property(property="county", type="string", format="string", example="Dhaka"),
 *            @OA\Property(property="tenant_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3}),
 *         ),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthenticated",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=422,
 *          description="Unprocessable Content",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Bad Request",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Not Found",
 *          @OA\JsonContent(),
 *      )
 * )
 */

 public function createPropertyV2(PropertyCreateRequestV2 $request)
 {
     try {
         $this->storeActivity($request, "");
         return DB::transaction(function () use ($request) {


             $request_data = $request->validated();
             $request_data["created_by"] = $request->user()->id;

             $reference_no_exists =  DB::table('properties')->where(
                 [
                     'reference_no' => $request_data['reference_no'],
                     "created_by" => $request->user()->id
                 ]
             )->exists();
             if ($reference_no_exists) {
                 $error =  [
                     "message" => "The given data was invalid.",
                     "errors" => ["reference_no" => ["The reference no has already been taken."]]
                 ];
                 throw new Exception(json_encode($error), 422);
             }

             $property =  Property::create($request_data);
             $property->generated_id = Str::random(4) . $property->id . Str::random(4);
             $property->save();


             if (!empty($request_data['documents'])) {
                 foreach ($request_data['documents'] as $document) {
                     $property->documents()->create($document); // Save the document with file paths
                 }
             }

             // for($i=0;$i<500;$i++) {
             //     $property =  Property::create([

             //          'name' => $request_data["name"] . Str::random(4),
             //          'image',
             //          'address'=> $request_data["address"] . Str::random(4),
             //          'country'=> $request_data["country"] . Str::random(4),
             //          'city'=> $request_data["city"] . Str::random(4),
             //          'postcode'=> $request_data["postcode"] . Str::random(4),
             //          "town"=> $request_data["town"] . Str::random(4),
             //          "lat"=> $request_data["lat"] . Str::random(4),
             //          "long"=> $request_data["long"] . Str::random(4),
             //          'type' => $request_data["type"],
             //          'reference_no'=> $request_data["reference_no"] . Str::random(4),
             //          'landlord_id'=> $request_data["landlord_id"],
             //          "created_by"=>$request->user()->id,
             //          'is_active'=>1,
             //     ]);
             //     $property->generated_id = Str::random(4) . $property->id . Str::random(4);
             //     $property->save();
             // }


             if (!empty($request_data['tenant_ids'])) {
                 $property->property_tenants()->sync($request_data['tenant_ids'], []);
             }

             return response($property, 201);
         });
     } catch (Exception $e) {

         return $this->sendError($e, 500, $request);
     }
 }


 /**
 * @OA\Post(
 *      path="/v1.0/properties/{id}/documents",
 *      operationId="addDocumentToProperty",
 *      tags={"property_management.property_management"},
 *      security={
 *          {"bearerAuth": {}}
 *      },
 *      summary="Add document to existing property",
 *      description="This method is to add a document to an existing property",
 *      @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="Property ID",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"documents"},
 *            @OA\Property(property="documents", type="array", @OA\Items(
 *                @OA\Property(property="gas_start_date", type="string", format="date", example="2024-11-01"),
 *                @OA\Property(property="gas_end_date", type="string", format="date", example="2025-11-01"),
 *                @OA\Property(property="document_type_id", type="integer", example=1),
 *                @OA\Property(property="files", type="array", @OA\Items(type="string", example="file.pdf"))
 *            )),
 *         ),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthenticated",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Property Not Found",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=422,
 *          description="Unprocessable Content",
 *          @OA\JsonContent(),
 *      )
 * )
 */
public function addDocumentToProperty(Request $request, $property_id)
{
    try {
        $request->validate([
            'gas_start_date' => 'required|date',
            'gas_end_date' => 'required|date',
            'document_type_id' => 'required|numeric|exists:document_types,id',
            'files' => 'required|array',
            'files.*' => 'string',  // Assuming file paths or URLs are provided as strings
        ]);

        $property = Property::findOrFail($property_id);

        // Add document to the property
        $documentData = $request->only(['gas_start_date', 'gas_end_date', 'document_type_id','files']);
        $document = $property->documents()->create($documentData);



        return response()->json(['message' => 'Document added successfully.', 'document' => $document], 200);

    } catch (Exception $e) {

        return $this->sendError($e, 500, $request);
    }
}
/**
 * @OA\Delete(
 *      path="/v1.0/properties/{property_id}/documents/{document_id}",
 *      operationId="deleteDocumentFromProperty",
 *      tags={"property_management.property_management"},
 *      security={
 *          {"bearerAuth": {}}
 *      },
 *      summary="Delete a document from a property",
 *      description="This method deletes a document associated with a specific property",
 *      @OA\Parameter(
 *          name="property_id",
 *          in="path",
 *          required=true,
 *          description="Property ID",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\Parameter(
 *          name="document_id",
 *          in="path",
 *          required=true,
 *          description="Document ID",
 *          @OA\Schema(type="integer")
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Document deleted successfully",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthenticated",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Property or Document Not Found",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=500,
 *          description="Internal Server Error",
 *          @OA\JsonContent(),
 *      )
 * )
 */
public function deleteDocumentFromProperty($property_id, $document_id)
{
    try {
        // Find the property
        $property = Property::findOrFail($property_id);

        // Find the document related to the property
        $document = $property->documents()->findOrFail($document_id);

        // Delete the document
        $document->delete();

        return response()->json(['message' => 'Document deleted successfully.'], 200);

    } catch (Exception $e) {
        return response()->json(['message' => 'An error occurred.'], 500);
    }
}



   /**
 * @OA\Post(
 *      path="/v1.0/property-agreement",
 *      operationId="createPropertyAgreement",
 *      tags={"property_management.property_management"},
 *      security={
 *          {"bearerAuth": {}}
 *      },
 *      summary="Store property agreement",
 *      description="This method is to store a new property agreement, replacing any existing agreement for the same property and landlord.",
 *      @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"landlord_id", "property_id", "start_date", "end_date", "payment_arrangement", "cheque_payable_to", "agent_commision", "terms_conditions"},
 *            @OA\Property(property="landlord_id", type="integer", example=1),
 *            @OA\Property(property="property_id", type="integer", example=1),
 *            @OA\Property(property="start_date", type="string", format="date", example="2024-11-01"),
 *            @OA\Property(property="end_date", type="string", format="date", example="2025-11-01"),
 *            @OA\Property(property="payment_arrangement", type="string", enum={"By_Cash", "By_Cheque", "Bank_Transfer"}, example="By_Cash"),
 *            @OA\Property(property="cheque_payable_to", type="string", example="John Doe"),
 *            @OA\Property(property="agent_commision", type="number", format="float", example=200.00),
 *            @OA\Property(property="management_fee", type="number", format="float", example=50.00, nullable=true),
 *            @OA\Property(property="inventory_charges", type="number", format="float", example=100.00, nullable=true),
 *            @OA\Property(property="terms_conditions", type="string", example="The terms and conditions of the agreement are as follows..."),
 *         ),
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *          @OA\JsonContent(
 *              @OA\Property(property="id", type="integer", example=1),
 *              @OA\Property(property="landlord_id", type="integer", example=1),
 *              @OA\Property(property="property_id", type="integer", example=1),
 *              @OA\Property(property="start_date", type="string", format="date", example="2024-11-01"),
 *              @OA\Property(property="end_date", type="string", format="date", example="2025-11-01"),
 *              @OA\Property(property="payment_arrangement", type="string", enum={"By_Cash", "By_Cheque", "Bank_Transfer"}, example="By_Cash"),
 *              @OA\Property(property="cheque_payable_to", type="string", example="John Doe"),
 *              @OA\Property(property="agent_commision", type="number", format="float", example=200.00),
 *              @OA\Property(property="management_fee", type="number", format="float", example=50.00, nullable=true),
 *              @OA\Property(property="inventory_charges", type="number", format="float", example=100.00, nullable=true),
 *              @OA\Property(property="terms_conditions", type="string", example="The terms and conditions of the agreement are as follows..."),
 *              @OA\Property(property="created_at", type="string", format="date-time", example="2024-11-01T12:00:00Z"),
 *              @OA\Property(property="updated_at", type="string", format="date-time", example="2024-11-01T12:00:00Z"),
 *          ),
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthenticated",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=422,
 *          description="Unprocessable Content",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Bad Request",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Not Found",
 *          @OA\JsonContent(),
 *      )
 * )
 */

    public function createPropertyAgreement(PropertyAgreementCreateRequest $request)
    {
        try {
            $this->storeActivity($request, "");
            return DB::transaction(function () use ($request) {

                $request_data = $request->validated();

                  // Check if there is an existing agreement for the same property and landlord
    $existingAgreement = PropertyAgreement::
      where('property_id', $request->property_id)
    ->whereNull('deleted_at') // Make sure it's not soft deleted
    ->first();

// If an existing agreement is found, soft delete it
if ($existingAgreement) {
$existingAgreement->delete();  // Soft delete the previous agreement
}
$agreement =PropertyAgreement::create($request_data);



                return response($agreement, 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }






  /**
 * @OA\Get(
 *      path="/v1.0/property-agreement/history",
 *      operationId="getPropertyAgreementHistory",
 *      tags={"property_management.property_management"},
 *      security={
 *          {"bearerAuth": {}}
 *      },
 *      summary="Get property agreement history",
 *      description="This method retrieves the history of property agreements for a given property and landlord, including soft-deleted agreements.",
 *      @OA\Parameter(
 *          name="landlord_id",
 *          in="query",
 *          required=false,
 *          @OA\Schema(type="integer", example=1)
 *      ),
 *      @OA\Parameter(
 *          name="property_id",
 *          in="query",
 *          required=false,
 *          @OA\Schema(type="integer", example=1)
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthenticated",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=422,
 *          description="Unprocessable Content",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Bad Request",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Not Found",
 *          @OA\JsonContent(),
 *      )
 * )
 */

 public function getPropertyAgreementHistory(Request $request)
{
    try {
        // Start building the query for history
        $query = PropertyAgreement::
            whereHas('property', function ($q) {
                // Ensure the property is created by the authenticated user
                $q->where('properties.created_by', auth()->user()->id);
            })
            ->when($request->filled('landlord_id'), function ($q) use ($request) {
                // Filter by landlord_id if provided
                $q->where('landlord_id', $request->landlord_id);
            })
            ->when($request->filled('property_id'), function ($q) use ($request) {
                // Filter by property_id if provided
                $q->where('property_id', $request->property_id);
            })
            ->when($request->filled('id'), function ($q) use ($request) {
                // If specific ID is provided, return that record only
                return $q->where('id', $request->input('id'))->first();
            }, function ($q) {
                // Otherwise, fetch history (including soft-deleted agreements)
                return $q->withTrashed() // Include soft-deleted records
                    ->when(!empty($request->per_page), function ($q) {
                        // Paginate if per_page is provided
                        return $q->paginate(request()->per_page);
                    }, function ($q) {
                        // Otherwise, return all agreements
                        return $q->get();
                    });
            });

        return response()->json($query, 200);
    } catch (Exception $e) {
        return $this->sendError($e, 500, $request);
    }
}

/**
 * @OA\Get(
 *      path="/v1.0/property-agreement/current",
 *      operationId="getCurrentPropertyAgreement",
 *      tags={"property_management.property_management"},
 *      security={
 *          {"bearerAuth": {}}
 *      },
 *      summary="Get current property agreement",
 *      description="This method retrieves the current property agreement for a given property and landlord.",
 *      @OA\Parameter(
 *          name="landlord_id",
 *          in="query",
 *          required=false,
 *          @OA\Schema(type="integer", example=1)
 *      ),
 *      @OA\Parameter(
 *          name="property_id",
 *          in="query",
 *          required=false,
 *          @OA\Schema(type="integer", example=1)
 *      ),
 *      @OA\Response(
 *          response=200,
 *          description="Successful operation",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=401,
 *          description="Unauthenticated",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=422,
 *          description="Unprocessable Content",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=403,
 *          description="Forbidden",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=400,
 *          description="Bad Request",
 *          @OA\JsonContent(),
 *      ),
 *      @OA\Response(
 *          response=404,
 *          description="Not Found",
 *          @OA\JsonContent(),
 *      )
 * )
 */
public function getCurrentPropertyAgreement(Request $request)
{
    try {


        // Start building the query
        $agreements = PropertyAgreement::
        whereHas("property", function($q) {
           $q->where("properties.created_by",auth()->user()->id);
        })
        ->when($request->filled('landlord_id'), function ($q) use ($request) {
            $q->where('landlord_id', $request->landlord_id);
        })->when($request->filled('property_id'), function ($q) use ($request) {
            $q->where('property_id', $request->property_id);
        })
            ->when($request->filled("id"), function ($query) use ($request) {
                return $query
                    ->where("id", $request->input("id"))
                    ->first();
            }, function ($query) {
                return $query->when(!empty(request()->per_page), function ($query) {
                    return $query->paginate(request()->per_page);
                }, function ($query) {
                    return $query->get();
                });
            });



        return response()->json($agreements, 200);
    } catch (Exception $e) {
        return $this->sendError($e, 500, $request);
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
            $this->storeActivity($request, "");

            return  DB::transaction(function () use ($request) {

                $updatableData = $request->validated();


                $reference_no_exists =  DB::table('properties')->where(
                    [
                        'reference_no' => $updatableData['reference_no'],
                        "created_by" => $request->user()->id
                    ]
                )
                    ->whereNotIn('id', [$updatableData["id"]])->exists();
                if ($reference_no_exists) {
                    $error =  [
                        "message" => "The given data was invalid.",
                        "errors" => ["reference_no" => ["The reference no has already been taken."]]
                    ];
                    throw new Exception(json_encode($error), 422);
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

                if (!$property) {
                    return response()->json([
                        "message" => "no property found"
                    ], 404);
                }
                $property->property_tenants()->detach();
                if (!empty($updatableData['tenant_ids'])) {

                    $property->property_tenants()->sync($updatableData['tenant_ids'], []);
                }

                return response($property, 200);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    /**
 * @OA\Put(
 *      path="/v2.0/properties",
 *      operationId="updatePropertyV2",
 *      tags={"property_management.property_management"},
 *      security={
 *          {"bearerAuth": {}}
 *      },
 *      summary="This method updates an existing property",
 *      description="This method updates an existing property",
 *      @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            required={"id", "name", "address", "country", "city", "postcode", "type", "reference_no"},
 *            required={"name", "image", "address", "country", "city", "postcode", "type", "reference_no"},
 *            @OA\Property(property="image", type="string", format="string", example="image.jpg"),
 *            @OA\Property(property="name", type="string", format="string", example="Rifat"),
 *            @OA\Property(property="address", type="string", format="string", example="address"),
 *            @OA\Property(property="country", type="string", format="string", example="Bangladesh"),
 *            @OA\Property(property="city", type="string", format="string", example="Dhaka"),
 *            @OA\Property(property="postcode", type="string", format="string", example="1207"),
 *            @OA\Property(property="town", type="string", format="string", example="town"),
 *            @OA\Property(property="lat", type="string", format="string", example="23.8103"),
 *            @OA\Property(property="long", type="string", format="string", example="90.4125"),
 *            @OA\Property(property="type", type="string", format="string", example="residential"),
 *            @OA\Property(property="reference_no", type="string", format="string", example="REF12345"),
 *            @OA\Property(property="landlord_id", type="string", format="numeric", example="1"),
 *            @OA\Property(property="date_of_instruction", type="string", format="date", example="2024-11-01"),
 *            @OA\Property(property="howDetached", type="string", format="string", example="fully detached"),
 *            @OA\Property(property="propertyFloor", type="string", format="string", example="Ground Floor"),
 *            @OA\Property(property="min_price", type="string", format="string", example="100000"),
 *            @OA\Property(property="max_price", type="string", format="string", example="500000"),
 *            @OA\Property(property="purpose", type="string", format="string", example="for sale"),
 *            @OA\Property(property="property_door_no", type="string", format="string", example="10A"),
 *            @OA\Property(property="property_road", type="string", format="string", example="Main Street"),
 *            @OA\Property(property="county", type="string", format="string", example="Dhaka"),
 *            @OA\Property(property="tenant_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3}),
 *         ),
 *      ),
 *      @OA\Response(response=200, description="Successful operation"),
 *      @OA\Response(response=404, description="Property not found"),
 *      @OA\Response(response=422, description="Validation Error"),
 * )
 */
public function updatePropertyV2(PropertyUpdateRequestV2 $request)
{
    try {
        // Find the property by ID
        $property = Property::findOrFail($request->input('id'));

        // Store activity (if needed)
        $this->storeActivity($request, "updated");

        // Validate request data
        $request_data = $request->validated();

        // Update property fields using fill()
        $property->fill($request_data);
        $property->updated_by = $request->user()->id; // Update the 'updated_by' field
        $property->save();

        // Update documents if provided
        if (!empty($request_data['documents'])) {
            $property->documents()->delete(); // Remove existing documents (if applicable)
            foreach ($request_data['documents'] as $document) {
                $property->documents()->create($document);
            }
        }

        // Sync tenant IDs if provided
        if (!empty($request_data['tenant_ids'])) {
            $property->property_tenants()->sync($request_data['tenant_ids']);
        }

        return response()->json($property, 200);
    } catch (Exception $e) {
        return $this->sendError($e, 500, $request);
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
            $this->storeActivity($request, "");

            // $automobilesQuery = AutomobileMake::with("makes");

            $propertyQuery =  Property::with("property_tenants", "landlord")
                ->leftJoin('property_tenants', 'properties.id', '=', 'property_tenants.property_id')
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
                $propertyQuery =  $propertyQuery->where("properties.landlord_id", $request->landlord_id);
            }
            if (!empty($request->tenant_id)) {
                $propertyQuery =  $propertyQuery->where("tenants.id", $request->tenant_id);
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

            $properties = $propertyQuery->orderBy("properties.address", $request->order_by)
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

            return $this->sendError($e, 500, $request);
        }
    }



    /**
 * @OA\Post(
 *      path="/v1.0/properties/{id}/add-more-images",
 *      summary="Add more images to a property",
 *      tags={"property_management"},
 *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *      @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            @OA\Property(property="images", type="array", @OA\Items(type="string", format="url"), example={"https://example.com/image3.jpg", "https://example.com/image4.jpg"})
 *         ),
 *      ),
 *      @OA\Response(response=200, description="Images added successfully"),
 *      @OA\Response(response=404, description="Property not found"),
 *      @OA\Response(response=422, description="Validation Error")
 * )
 */

    public function addMoreImages(Request $request, $id)
{
    $request->validate([
        'images' => 'required|array',
        'images.*' => 'string|url',
    ]);

    $property = Property::findOrFail($id);

    // Merge new images with existing ones
    $existingImages = $property->images ?? [];
    $newImages = array_merge($existingImages, $request->input('images'));
    $property->images = array_unique($newImages);

    $property->save();

    return response()->json(['message' => 'Images added successfully', 'images' => $property->images]);
}


/**
 * @OA\Delete(
 *      path="/v2.0/properties/{id}/delete-images",
 *      summary="Delete specific images from a property",
 *      tags={"property_management"},
 *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *      @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *            @OA\Property(property="images", type="array", @OA\Items(type="string", format="url"), example={"https://example.com/image1.jpg"})
 *         ),
 *      ),
 *      @OA\Response(response=200, description="Images deleted successfully"),
 *      @OA\Response(response=404, description="Property not found"),
 *      @OA\Response(response=422, description="Validation Error")
 * )
 */

public function deleteImages(Request $request, $id)
{
    $request->validate([
        'images' => 'required|array',
        'images.*' => 'string|url',
    ]);

    $property = Property::findOrFail($id);

    // Remove specified images
    $existingImages = $property->images ?? [];
    $imagesToDelete = $request->input('images');

    $updatedImages = array_diff($existingImages, $imagesToDelete);
    $property->images = array_values($updatedImages); // Re-index the array

    $property->save();

    return response()->json(['message' => 'Images deleted successfully', 'images' => $property->images]);
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

    public function getAllProperties(Request $request)
    {
        try {
            $this->storeActivity($request, "");

            // $automobilesQuery = AutomobileMake::with("makes");

            $propertyQuery =  Property::with("property_tenants", "landlord")
                ->leftJoin('property_tenants', 'properties.id', '=', 'property_tenants.property_id')
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
                $propertyQuery =  $propertyQuery->where("properties.landlord_id", $request->landlord_id);
            }
            if (!empty($request->tenant_id)) {
                $propertyQuery =  $propertyQuery->where("tenants.id", $request->tenant_id);
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
                ->select("properties.*")

                ->orderBy("properties.address", $request->order_by)->get();

            return response()->json($properties, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     *
     * @OA\Get(
     *      path="/v1.0/properties/get/all/optimized",
     *      operationId="getAllPropertiesOptimized",
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

    public function getAllPropertiesOptimized(Request $request)
    {
        try {
            $this->storeActivity($request, "");

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
                $propertyQuery =  $propertyQuery->where("properties.landlord_id", $request->landlord_id);
            }
            if (!empty($request->tenant_id)) {
                $propertyQuery =  $propertyQuery->where("tenants.id", $request->tenant_id);
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
                ->select(
                    "properties.id",
                    "properties.generated_id",
                    "properties.address",

                )

                ->orderBy("properties.address", $request->order_by)->get();

            return response()->json($properties, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
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
            $this->storeActivity($request, "");


            $property = Property::with(
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

            if (!$property) {
                return response()->json([
                    "message" => "no property found"
                ], 404);
            }


            return response()->json($property, 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
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
            $this->storeActivity($request, "");

            $business = Business::where([
                "owner_id" => $request->user()->id
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


            $property = Property::where([
                "id" => $id,
                "created_by" => $request->user()->id
            ])
                ->first();

            if (!$property) {
                return response()->json([
                    "message" => "no property found"
                ], 404);
            }
            $property->delete();

            return response()->json(["ok" => true], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
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
            $this->storeActivity($request, "");

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


            return response()->json(["reference_no" => $reference_no], 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
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
            $this->storeActivity($request, "");

            $reference_no_exists =  DB::table('properties')->where(
                [
                    'reference_no' => $reference_no,
                    "created_by" => $request->user()->id
                ]
            )->exists();



            return response()->json(["reference_no_exists" => $reference_no_exists], 200);
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }
}
