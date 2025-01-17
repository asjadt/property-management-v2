<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Http\Requests\MultipleImageUploadRequest;

use App\Http\Requests\PropertyCreateRequest;
use App\Http\Requests\PropertyCreateRequestV2;
use App\Http\Requests\PropertyUpdateLandlordRequest;
use App\Http\Requests\PropertyUpdateRequest;
use App\Http\Requests\PropertyUpdateRequestV2;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Business;
use App\Models\Property;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PropertyController extends Controller
{
    use ErrorUtil, UserActivityUtil, BasicUtil;
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
     *      path="/v1.0/property-image/multiple",
     *      operationId="createPropertyImageMultiple",
     *      tags={"property_management.property_management"},
     *       security={
     *           {"bearerAuth": {}}
     *       },

     *      summary="This method is to store multiple image request",
     *      description="This method is to store multiple image request",
     *
     *  @OA\RequestBody(
     *   * @OA\MediaType(
     *     mediaType="multipart/form-data",
     *     @OA\Schema(
     *         required={"images[]"},
     *         @OA\Property(
     *             description="array of images to upload",
     *             property="images[]",
     *             type="array",
     *             @OA\Items(
     *                 type="file"
     *             ),
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

    public function createPropertyImageMultiple(MultipleImageUploadRequest $request)
    {
        try {
            $this->storeActivity($request, "");

            $request_data = $request->validated();

            $location =  config("setup-config.property_image");

            $images = [];
            if (!empty($request_data["images"])) {
                foreach ($request_data["images"] as $image) {
                    $new_file_name = time() . '_' . str_replace(' ', '_', $image->getClientOriginalName());
                    $image->move(public_path($location), $new_file_name);

                    array_push($images, ("/" . $location . "/" . $new_file_name));
                }
            }

            return response()->json(["images" => $images], 201);
        } catch (Exception $e) {
            error_log($e->getMessage());
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

     *  *  *  *     *  * *  @OA\Property(property="tenant_ids", type="string", format="array",example={1,2,3}),
     *      *  *  *  *     *  * *  @OA\Property(property="landlord_ids", type="string", format="array",example={1,2,3}),
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


                $property->property_landlords()->sync($request_data['landlord_ids'], []);


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
     *            @OA\Property(property="date_of_instruction", type="string", format="date", example="2024-11-01"),
     *            @OA\Property(property="howDetached", type="string", format="string", example="fully detached"),
    *            @OA\Property(property="no_of_beds", type="string", format="string", example="one"),
    *            @OA\Property(property="no_of_baths", type="string", format="string", example="one"),
     *            @OA\Property(property="is_garden", type="string", format="string", example="1"),
     *
     *
     *
     *
     *            @OA\Property(property="propertyFloor", type="string", format="string", example="Ground Floor"),
     *  *            @OA\Property(property="category", type="string", format="string", example="Ground Floor"),

     *            @OA\Property(property="price", type="string", format="string", example="500000"),
     *            @OA\Property(property="purpose", type="string", format="string", example="for sale"),
     *            @OA\Property(property="property_door_no", type="string", format="string", example="10A"),
     *            @OA\Property(property="property_road", type="string", format="string", example="Main Street"),
     *      *            @OA\Property(property="is_dss", type="string", format="string", example="1"),
     *
     *            @OA\Property(property="county", type="string", format="string", example="Dhaka"),
     *            @OA\Property(property="tenant_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3}),
     *            @OA\Property(property="landlord_ids", type="array", @OA\Items(type="integer"), example={1, 2, 3}),
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

                $property =  Property::create(
                    collect($request_data)
                    ->only(
                        [
                        'name',
                        'image',
                        // 'images',
                        'address',
                        'country',
                        'city',
                        'postcode',
                        "town",
                        "lat",
                        "long",
                        'type',
                        'reference_no',
                        'is_active',
                        'date_of_instruction',
                        'howDetached',
                        "no_of_beds",
                        "no_of_baths",
                        "is_garden",
                        'propertyFloor',
                        'category',
                        'price',
                        'purpose',
                        'property_door_no',
                        'property_road',
                        'is_dss',
                        'county',
                        "created_by"
                        ]

                    )
                    ->toArray()

            );
                $property->generated_id = Str::random(4) . $property->id . Str::random(4);

                $request_data["images"] = $this->storeUploadedFiles($request_data["images"], "", "images", false,$property->id);


                $property->images = ($request_data["images"]);
                $property->save();




                $request_data["documents"] = $this->storeUploadedFiles($request_data["documents"], "files", "documents", true,$property->id);


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

                if (!empty($request_data['maintenance_item_type_ids'])) {
                    $property->maintenance_item_types()->sync($request_data['maintenance_item_type_ids'], []);
                }


                if (!empty($request_data['tenant_ids'])) {
                    $property->property_tenants()->sync($request_data['tenant_ids'], []);
                }


                $property->property_landlords()->sync($request_data['landlord_ids']);





                return response($property, 201);
            });
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     * @OA\Post(
     *      path="/v1.0/properties/documents",
     *      operationId="addDocumentToProperty",
     *      tags={"property_management.property_management"},
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      summary="Add document to existing property",
     *      description="This method is to add a document to an existing property",

     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"documents"},
     *            @OA\Property(property="documents", type="array", @OA\Items(
     *                @OA\Property(property="gas_start_date", type="string", format="date", example="2024-11-01"),
     *                @OA\Property(property="gas_end_date", type="string", format="date", example="2025-11-01"),
     *  *                @OA\Property(property="description", type="string", format="date", example="description"),
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
    public function addDocumentToProperty(Request $request,)
    {
        try {
            try {
                $request->validate([
                    'id' => "required|numeric|exists:properties,id",
                    'documents' => 'required|array',
                    'documents.*.gas_start_date' => 'required|date',
                    'documents.*.gas_end_date' => 'required|date',
                    'documents.*.description' => 'nullable|string',

                    'documents.*.document_type_id' => 'required|numeric|exists:document_types,id',
                    'documents.*.files' => 'required|array',
                    'documents.*.files.*' => 'string', // File paths or URLs
                ]);
            } catch (ValidationException $e) {
                return response()->json(['errors' => $e->errors()], 422);
            }



            $property = Property::where(["id" => $request->id])
                ->first();

            if (empty($property)) {
                return response()->json(['message' => 'Data not found!'], 400);
            }


            $documents = $request->input('documents');
            $documents = $this->storeUploadedFiles($documents, "files", "documents", true,$property->id);


            // Loop through each document in the request and add it to the property
            foreach ($documents as $documentData) {
                $property->documents()->create([
                    'gas_start_date' => $documentData['gas_start_date'],
                    'gas_end_date' => $documentData['gas_end_date'],
                    'description' => $documentData['description'],
                    'document_type_id' => $documentData['document_type_id'],
                    'files' => json_encode($documentData['files']),  // Assuming files are stored as a JSON array
                ]);
            }

            return response()->json(['message' => 'Documents added successfully!'], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     * @OA\Put(
     *      path="/v1.0/properties/documents",
     *      operationId="updateDocumentInProperty",
     *      tags={"property_management.property_management"},
     *      security={
     *          {"bearerAuth": {}}
     *      },
     *      summary="Update an existing document for a property",
     *      description="This method is to update an existing document for a property",
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"documents"},
     *            @OA\Property(property="documents", type="array", @OA\Items(
     *                @OA\Property(property="gas_start_date", type="string", format="date", example="2024-11-01"),
     *                @OA\Property(property="gas_end_date", type="string", format="date", example="2025-11-01"),
     *  *                @OA\Property(property="description", type="string", format="date", example="description"),
     *
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
     *          description="Property or Document Not Found",
     *          @OA\JsonContent(),
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Content",
     *          @OA\JsonContent(),
     *      )
     * )
     */

    public function updateDocumentInProperty(Request $request)
    {
        try {

            try {
                $request->validate([
                    'id' => "required|numeric|exists:properties,id",
                    'document_id' => "required|numeric|exists:property_documents,id",
                    'gas_start_date' => 'required|date',
                    'gas_end_date' => 'required|date',
                    'description' => 'nullable|string',

                    'document_type_id' => 'required|numeric|exists:document_types,id',
                    'files' => 'required|array',
                    'files.*' => 'string',  // Assuming file paths or URLs are provided as strings
                ]);
            } catch (ValidationException $e) {
                return response()->json(['errors' => $e->errors()], 422);
            }

            $property = Property::where(["id" => $request->id])->first();

            $document = $property->documents()
                ->where('property_documents.id', $request->document_id)
                ->first();


            if (empty($document)) {
                return response()->json(['message' => "invalid document id"], 400);
            }

            $requestDocumentData = $request->only(['gas_start_date', 'gas_end_date', 'description', 'document_type_id', 'files']);




            if (isset($requestDocumentData["files"])) {
                $requestDocumentData["files"] =  $this->storeUploadedFiles(
                $requestDocumentData["files"],
                 "",
                 "documents",
                 false,
                $property->id
            );


                $newDocs = $requestDocumentData["files"];

                $existingDocs = $document->files;

                if(!is_array($existingDocs)) {
                   $existingDocs = json_decode($existingDocs);

                }

                foreach ($existingDocs as $existingDoc) {

                    LOG::info(json_encode($newDocs));
                    LOG::info(json_encode($existingDoc));

                     if(!in_array($existingDoc,$newDocs)) {
                        $filePath = public_path(("/" . str_replace(' ', '_', auth()->user()->my_business->name) . "/" . base64_encode($property->id) . "/documents/" . $existingDoc));

                        if (File::exists($filePath)) {
                            File::delete($filePath);
                        }
                     }
                }

            }



// Fill the document object with the provided data
$document->fill($requestDocumentData);

// Save the updated document
$document->save();

            return response()->json(['message' => 'Document updated successfully.', 'document' => $document], 200);
        } catch (Exception $e) {

            return $this->sendError($e, 500, $request);
        }
    }


    /**
     * @OA\Delete(
     *      path="/v1.0/properties/{property_id}/documents/{document_id}",
     *      operationId="deleteDocumentFromProperty",
     *      tags={"property_management.property_management"},
      *       security={
     *           {"bearerAuth": {}},
     *           {"pin": {}}
     *       },
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

            $business = Business::where([
                "owner_id" => request()->user()->id
            ])->first();

            if (!$business) {
                return response()->json([
                    "message" => "you don't have a valid business"
                ], 401);
            }

            if (!($business->pin == request()->header("pin"))) {
                return response()->json([
                    "message" => "invalid pin"
                ], 401);
            }

            // Find the property
            $property = Property::findOrFail($property_id);

            // Find the document related to the property
            $document = $property->documents()->findOrFail($document_id);



            $document_files = $document->files;
            if(!is_array($document_files)) {
                  $document_files = json_decode($document_files);
            }

            foreach ($document_files as $file) {

    $filePath = public_path(("/" . str_replace(' ', '_', auth()->user()->my_business->name) . "/" . base64_encode($property->id) . "/documents/" . $file));

                   if (File::exists($filePath)) {
                       File::delete($filePath);
                   }

           }


            // Delete the document
            $document->delete();



            return response()->json(['message' => 'Document deleted successfully.'], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
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

     *                @OA\Property(property="reference_no", type="string", format="string",example="reference_no"),

     *                @OA\Property(property="tenant_ids", type="string", format="array",example={1,2,3}),
     *   @OA\Property(property="landlord_ids", type="string", format="array",example={1,2,3})
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

                $request_data = $request->validated();


                $reference_no_exists =  DB::table('properties')->where(
                    [
                        'reference_no' => $request_data['reference_no'],
                        "created_by" => $request->user()->id
                    ]
                )
                    ->whereNotIn('id', [$request_data["id"]])->exists();
                if ($reference_no_exists) {
                    $error =  [
                        "message" => "The given data was invalid.",
                        "errors" => ["reference_no" => ["The reference no has already been taken."]]
                    ];
                    throw new Exception(json_encode($error), 422);
                }


                $property  =  tap(Property::where([
                    "id" => $request_data["id"],
                    "created_by" => $request->user()->id
                ]))->update(
                    collect($request_data)->only([
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
                        'reference_no'
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
                if (!empty($request_data['tenant_ids'])) {
                    $property->property_tenants()->sync($request_data['tenant_ids'], []);
                }

                $property->property_landlords()->sync($request_data['landlord_ids']);

                return response($property, 200);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     *
     * @OA\Put(
     *      path="/v2.0/properties-update",
     *      operationId="updatePropertyV2",
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
     *                @OA\Property(property="tenant_ids", type="string", format="array",example={1,2,3}),
     *                @OA\Property(property="landlord_ids", type="string", format="array",example={1,2,3}),
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

            if (isset($request_data["images"])) {
                $request_data["images"] =  $this->storeUploadedFiles(
                $request_data["images"],
                 "",
                 "images",
                 false,
                $property->id
            );

                $newDocs = $request_data["images"];

                $existingDocs = $property->images;

                if(!is_array($existingDocs)) {
                   $existingDocs = json_decode($existingDocs);
                }

                foreach ($existingDocs as $existingDoc) {
                     if(!in_array($existingDoc,$newDocs)) {
                        $filePath = public_path(("/" . str_replace(' ', '_', auth()->user()->my_business->name) . "/" . base64_encode($property->id) . "/images/" . $existingDoc));

                        if (File::exists($filePath)) {
                            File::delete($filePath);
                        }
                     }
                }
            }

            $property->fill($request_data);

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


            $property->property_landlords()->sync($request_data['landlord_ids']);



            if (!empty($request_data['maintenance_item_type_ids'])) {
                $property->maintenance_item_types()->sync($request_data['maintenance_item_type_ids'], []);
            }

            return response()->json($property, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }



 /**
     *
     * @OA\Put(
     *      path="/v1.0/properties-update-landlord",
     *      operationId="updatePropertyLandlord",
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
     *                @OA\Property(property="landlord_ids", type="string", format="array",example={1,2,3}),
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
    public function updatePropertyLandlord(PropertyUpdateLandlordRequest $request)
    {
        try {

            // Find the property by ID
            $property = Property::findOrFail($request->input('id'));

            // Store activity (if needed)
            $this->storeActivity($request, "updated");

            // Validate request data
            $request_data = $request->validated();

            $property->property_landlords()->sync($request_data['landlord_ids']);


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
 * @OA\Parameter(
 *     name="perPage",
 *     in="query",
 *     description="Number of results per page",
 *     required=false,
 *     example="15"
 * ),
 * @OA\Parameter(
 *     name="search_key",
 *     in="query",
 *     description="Search term to filter properties by reference number, address, or type",
 *     required=false,
 *     example="keyword"
 * ),
 * @OA\Parameter(
 *     name="landlord_ids",
 *     in="query",
 *     description="Filter properties by landlord ID",
 *     required=false,
 *     example="1"
 * ),
 * @OA\Parameter(
 *     name="tenant_ids",
 *     in="query",
 *     description="Filter properties by tenant ID",
 *     required=false,
 *     example="1"
 * ),
 * @OA\Parameter(
 *     name="category",
 *     in="query",
 *     description="Filter properties by category",
 *     required=false,
 *     example="residential"
 * ),
 * @OA\Parameter(
 *     name="address",
 *     in="query",
 *     description="Search properties by address",
 *     required=false,
 *     example="123 Main St"
 * ),
 * @OA\Parameter(
 *     name="start_date",
 *     in="query",
 *     description="Filter properties by the creation start date",
 *     required=false,
 *     example="2023-01-01"
 * ),
 * @OA\Parameter(
 *     name="end_date",
 *     in="query",
 *     description="Filter properties by the creation end date",
 *     required=false,
 *     example="2023-12-31"
 * ),
 * @OA\Parameter(
 *     name="reference_no",
 *     in="query",
 *     description="Filter properties by reference number",
 *     required=false,
 *     example="ABC123"
 * ),
 * @OA\Parameter(
 *     name="start_date_of_instruction",
 *     in="query",
 *     description="Filter properties by the instruction start date",
 *     required=false,
 *     example="2023-01-01"
 * ),
 * @OA\Parameter(
 *     name="end_date_of_instruction",
 *     in="query",
 *     description="Filter properties by the instruction end date",
 *     required=false,
 *     example="2023-12-31"
 * ),
 * @OA\Parameter(
 *     name="start_no_of_beds",
 *     in="query",
 *     description="Filter properties by the minimum number of beds",
 *     required=false,
 *     example="2"
 * ),
 * @OA\Parameter(
 *     name="end_no_of_beds",
 *     in="query",
 *     description="Filter properties by the maximum number of beds",
 *     required=false,
 *     example="4"
 * ),
 * @OA\Parameter(
 *     name="is_garden",
 *     in="query",
 *     description="Filter properties that have a garden",
 *     required=false,
 *     example="true"
 * ),
 * @OA\Parameter(
 *     name="is_dss",
 *     in="query",
 *     description="Filter properties that are DSS (Department of Social Services) approved",
 *     required=false,
 *     example="true"
 * ),
 * @OA\Parameter(
 *     name="document_type_id",
 *     in="query",
 *     description="Filter properties by document type ID",
 *     required=false,
 *     example="1"
 * ),
 * @OA\Parameter(
 *     name="is_document_expired",
 *     in="query",
 *     description="Filter properties by document type ID",
 *     required=false,
 *     example="1"
 * ),
 *  * @OA\Parameter(
 *     name="document_expired_in",
 *     in="query",
 *     description="Filter properties by document type ID",
 *     required=false,
 *     example="1"
 * ),
 * * @OA\Parameter(
 *     name="is_next_follow_up_date_passed",
 *     in="query",
 *     description="Filter properties by document type ID",
 *     required=false,
 *     example="1"
 * ),
 *  * @OA\Parameter(
 *     name="next_follow_up_date_in",
 *     in="query",
 *     description="Filter properties by document type ID",
 *     required=false,
 *     example="1"
 * ),
 *
 *
 * @OA\Parameter(
 *     name="start_inspection_date",
 *     in="query",
 *     description="Filter inspections by start date",
 *     required=false,
 *     example="2023-01-01"
 * ),
 * @OA\Parameter(
 *     name="end_inspection_date",
 *     in="query",
 *     description="Filter inspections by end date",
 *     required=false,
 *     example="2023-12-31"
 * ),
 * @OA\Parameter(
 *     name="start_next_inspection_date",
 *     in="query",
 *     description="Filter next inspections by start date",
 *     required=false,
 *     example="2023-01-01"
 * ),
 * @OA\Parameter(
 *     name="end_next_inspection_date",
 *     in="query",
 *     description="Filter next inspections by end date",
 *     required=false,
 *     example="2023-12-31"
 * ),
 *
 *     @OA\Parameter(
 *     name="inspection_duration",
 *     in="query",
 *     description="Filter inspections by the inspector's name or ID",
 *     required=false,
 *     example="inspection duration"
 * ),
 *
 * @OA\Parameter(
 *     name="inspected_by",
 *     in="query",
 *     description="Filter inspections by the inspector's name or ID",
 *     required=false,
 *     example="John Doe"
 * ),
 * @OA\Parameter(
 *     name="maintenance_item_type_id",
 *     in="query",
 *     description="Filter inspections by maintenance item type ID",
 *     required=false,
 *     example="1"
 * ),
 * @OA\Parameter(
 *     name="start_next_follow_up_date",
 *     in="query",
 *     description="Filter follow-ups by start date",
 *     required=false,
 *     example="2023-01-01"
 * ),
 * @OA\Parameter(
 *     name="end_next_follow_up_date",
 *     in="query",
 *     description="Filter follow-ups by end date",
 *     required=false,
 *     example="2023-12-31"
 * ),
 * @OA\Parameter(
 *     name="order_by",
 *     in="query",
 *     description="Order the results by a specific column (e.g., 'address')",
 *     required=false,
 *     example="address"
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

            $propertyQuery =  Property::with("property_landlords","property_tenants")

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

            if (!empty($request->landlord_ids)) {
                $propertyQuery =  $propertyQuery->whereHas("property_landlords", function($query) {
                    $landlord_ids = explode(',', request()->input("landlord_ids"));
                   $query
                   ->whereIn("property_landlords.landlord_id", $landlord_ids)
                   ;
                });
            }

            if (!empty($request->tenant_ids)) {
                $propertyQuery =  $propertyQuery->whereHas("property_tenants", function($query) {
                    $tenant_ids = explode(',', request()->input("tenant_ids"));
                   $query
                   ->whereIn("property_tenants.tenant_id", $tenant_ids)
                   ;
                });
            }


            if (!empty($request->category)) {
                $propertyQuery =  $propertyQuery->where("properties.category", $request->category);
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

            if (!empty($request->reference_no)) {
                $propertyQuery =  $propertyQuery->where("properties.reference_no", "like", "%" . $request->reference_no . "%");
            }

            if (!empty($request->category)) {
                $propertyQuery =  $propertyQuery->where("properties.category", $request->category);
            }

            if (!empty($request->start_date_of_instruction)) {
                $propertyQuery =  $propertyQuery->whereDate("properties.date_of_instruction", ">=", $request->start_date_of_instruction);
            }

            if (!empty($request->end_date_of_instruction)) {
                $propertyQuery =  $propertyQuery->whereDate("properties.date_of_instruction", "<=", $request->end_date_of_instruction);
            }



            if (!empty($request->start_no_of_beds)) {
                $propertyQuery =  $propertyQuery->where("properties.no_of_beds", ">=", $request->start_no_of_beds);
            }

            if (!empty($request->end_no_of_beds)) {
                $propertyQuery =  $propertyQuery->where("properties.no_of_beds", "<=", $request->end_no_of_beds);
            }

            if (request()->boolean("is_garden")) {
                $propertyQuery =  $propertyQuery->where("properties.is_garden",1);
            }
            if (request()->boolean("is_dss")) {
                $propertyQuery =  $propertyQuery->where("properties.is_dss",1);
            }

            $propertyQuery = $propertyQuery->when(request()->filled("document_type_id"), function ($query) {
                $query->whereHas("documents", function ($subQuery) {
                    $subQuery->where("property_documents.document_type_id", request()->input("document_type_id"));
                });
            })
            ->when(request()->filled("is_document_expired"), function ($query) {
                $query->whereHas("documents", function ($subQuery) {
                    $subQuery->whereDate('property_documents.gas_end_date', '<', Carbon::today());
                });
            })
            ->when(request()->filled("document_expired_in"), function ($query) {
                $expiryDays = request()->input('document_expired_in'); // Get the number of days passed from the front end

                // Check if a valid number of days is provided
                if (is_numeric($expiryDays) && $expiryDays > 0) {
                    $query->whereHas('documents', function ($subQuery) use ($expiryDays) {
                        $subQuery->whereDate('property_documents.gas_end_date', '>=', Carbon::today())
                                 ->whereDate('property_documents.gas_end_date', '<=', Carbon::today()->addDays($expiryDays));
                    });
                }


            })
            ->when(request()->boolean("is_next_follow_up_date_passed"), function ($query) {
                $query->whereHas("inspections.maintenance_item", function ($subQuery) {
                    $subQuery->whereDate('maintenance_items.next_follow_up_date', '<', Carbon::today());
                });
            })

            ->when(request()->filled("next_follow_up_date_in"), function ($query) {
                $expiryDays = request()->input('next_follow_up_date_in'); // Get the number of days passed from the front end

                // Check if a valid number of days is provided
                if (is_numeric($expiryDays) && $expiryDays > 0) {
                    $query->whereHas('inspections.maintenance_item', function ($subQuery) use ($expiryDays) {
                        $subQuery->whereDate('maintenance_items.next_follow_up_date', '>=', Carbon::today())
                                 ->whereDate('maintenance_items.next_follow_up_date', '<=', Carbon::today()->addDays($expiryDays));
                    });
                }
            })
            ;





            $propertyQuery = $propertyQuery
            ->when(
                request()->only(['start_inspection_date', 'end_inspection_date']),
                function ($query) {
                    $query->whereHas('inspections', function ($query) {
                        $query->when(request()->filled('start_inspection_date'), function ($query) {
                            $query->whereDate('tenant_inspections.date', '>=', request()->input('start_inspection_date'));
                        });
                        $query->when(request()->filled('end_inspection_date'), function ($query) {
                            $query->whereDate('tenant_inspections.date', '<=', request()->input('end_inspection_date'));
                        });
                    });
                }
            )
            ->when(
                request()->only(['start_next_inspection_date', 'end_next_inspection_date']),
                function ($query) {
                    $query->whereHas('inspections', function ($query) {
                        $query->when(request()->filled('start_next_inspection_date'), function ($query) {
                            $query->whereDate('tenant_inspections.next_inspection_date', '>=', request()->input('start_next_inspection_date'));
                        });
                        $query->when(request()->filled('end_next_inspection_date'), function ($query) {
                            $query->whereDate('tenant_inspections.next_inspection_date', '<=', request()->input('end_next_inspection_date'));
                        });
                    });
                }
            )
            ->when(
                request()->filled('inspected_by'),
                function ($query) {
                    $query->whereHas('inspections', function ($query) {
                        $query->where('tenant_inspections.inspected_by', 'like', '%' . request()->input('inspected_by') . '%');
                    });
                }
            )
            ->when(
                request()->filled('inspection_duration'),
                function ($query) {
                    $query->whereHas('inspections', function ($query) {
                        $query->where('tenant_inspections.inspection_duration', 'like', '%' . request()->input('inspection_duration') . '%');
                    });
                }
            )
            ->when(
                request()->filled('maintenance_item_type_id'),
                function ($query) {
                    $query->whereHas('inspections.maintenance_item', function ($query) {
                        $query->where('maintenance_items.maintenance_item_type_id', request()->input('maintenance_item_type_id'));
                    });
                }
            )
            ->when(
                request()->only(['start_next_follow_up_date', 'end_next_follow_up_date']),
                function ($query) {
                    $query->whereHas('inspections.maintenance_item', function ($query) {
                        $query->when(request()->filled('start_next_follow_up_date'), function ($query) {
                            $query->whereDate('maintenance_items.next_follow_up_date', '>=', request()->input('start_next_follow_up_date'));
                        });
                        $query->when(request()->filled('end_next_follow_up_date'), function ($query) {
                            $query->whereDate('maintenance_items.next_follow_up_date', '<=', request()->input('end_next_follow_up_date'));
                        });
                    });
                }
            );


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

        try {
            $request->validate([
                'images' => 'required|array',
                'images.*' => 'string|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

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
     *      path="/v1.0/properties/{id}/delete-images",
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

        try {
            $request->validate([
                'images' => 'required|array',
                'images.*' => 'string|string',
            ]);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }

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
     *
     *   @OA\Parameter(
     * name="reference_no",
     * in="query",
     * description="reference_no",
     * required=true,
     * example="reference_no"
     * ),
     *
     *
     * *  @OA\Parameter(
     * name="landlord_ids",
     * in="query",
     * description="landlord_ids",
     * required=true,
     * example="1"
     * ),
     * *  @OA\Parameter(
     * name="tenant_ids",
     * in="query",
     * description="tenant_ids",
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

            $propertyQuery =  Property::with("property_landlords","property_tenants")

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


            if (!empty($request->landlord_ids)) {
                $propertyQuery =  $propertyQuery->whereHas("property_landlords", function($query) {
                    $landlord_ids = explode(',', request()->input("landlord_ids"));
                   $query
                   ->whereIn("property_landlords.landlord_id", $landlord_ids)
                   ;
                });
            }

            if (!empty($request->tenant_ids)) {
                $propertyQuery =  $propertyQuery->whereHas("property_tenants", function($query) {
                    $tenant_ids = explode(',', request()->input("tenant_ids"));
                   $query
                   ->whereIn("property_tenants.tenant_id", $tenant_ids)
                   ;
                });
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
     * name="landlord_ids",
     * in="query",
     * description="landlord_ids",
     * required=true,
     * example="1"
     * ),
     * *  @OA\Parameter(
     * name="tenant_ids",
     * in="query",
     * description="tenant_ids",
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

            $propertyQuery =  Property::where(["properties.created_by" => $request->user()->id]);

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

            if (!empty($request->landlord_ids)) {
                $propertyQuery =  $propertyQuery->whereHas("property_landlords", function($query) {
                    $landlord_ids = explode(',', request()->input("landlord_ids"));
                   $query
                   ->whereIn("property_landlords.landlord_id", $landlord_ids)
                   ;
                });
            }

            if (!empty($request->tenant_ids)) {
                $propertyQuery =  $propertyQuery->whereHas("property_tenants", function($query) {
                    $tenant_ids = explode(',', request()->input("tenant_ids"));
                   $query
                   ->whereIn("property_tenants.tenant_id", $tenant_ids)
                   ;
                });
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
                "property_landlords",
                "repairs.repair_category",
                "invoices",
                "documents",
                "maintenance_item_types",
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


                $updatedFiles = []; // Create a new array for modified files
                if(!is_array($property->images)) {
                    $images = json_decode($property->images);
                } else {
                    $images = $property->images;
                }


                foreach ($images as $image) {
                    // Modify the file name
                    $updatedFiles[] = "/" . str_replace(' ', '_', auth()->user()->my_business->name) . "/" . base64_encode($property->id) . "/images/" . $image;
                }


                // Replace the files property with the updated array if needed
                $property->images = $updatedFiles; // Use a new attribute to avoid issues


            foreach ($property->documents as $document) {

                $updatedFiles = []; // Create a new array for modified files
                if(!is_array($document->files)) {
                    $files = json_decode($document->files);
                } else {
                    $files = $document->files;
                }

                foreach ($files as $file) {
                    // Modify the file name
                    $updatedFiles[] = "/" . str_replace(' ', '_', auth()->user()->my_business->name) . "/" . base64_encode($property->id) . "/documents/" . $file;
                }

                // Replace the files property with the updated array if needed
                $document->file_names = $updatedFiles; // Use a new attribute to avoid issues
                unset($document->files);
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

              // Construct the folder path
        $businessFolderName = str_replace(' ', '_', auth()->user()->my_business->name);
        $propertyFolderName = base64_encode($property->id); // Base64 encoding the property ID
        $folderPath = public_path("{$businessFolderName}/{$propertyFolderName}");

        // Delete the property folder if it exists
        if (File::exists($folderPath)) {
            if (File::deleteDirectory($folderPath)) {
                Log::info("Folder {$folderPath} successfully deleted.");
            } else {
                Log::warning("Failed to delete folder {$folderPath}.");
            }
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
