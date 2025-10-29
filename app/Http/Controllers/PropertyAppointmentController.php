<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppointmentRequest;
use App\Http\Requests\PropertyAppointmentRequest;
use App\Http\Utils\BasicUtil;
use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\PropertyAppointment;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PropertyAppointmentController extends Controller
{
    use ErrorUtil, UserActivityUtil, BasicUtil;

    /**
     * @OA\Post(
     *      path="/v1.0/appointments",
     *      operationId="createAppointment",
     *      tags={"property_management.appointment_management"},
     *      security={
     *           {"bearerAuth": {}}
     *      },
     *      summary="This method is to create property appointment",
     *      description="This method is to create property appointment",
     *
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"job_type","employee_id","start_date","end_date","property_id"},
     *            @OA\Property(property="job_type", type="string", format="string", example="Inspection"),
     *            @OA\Property(property="employee_id", type="string", format="string", example=""),
     *            @OA\Property(property="start_date", type="string", format="date", example="2025-11-01"),
     *            @OA\Property(property="end_date", type="string", format="date", example="2025-11-05"),
     *            @OA\Property(property="description", type="string", format="string", example="Property inspection for maintenance"),
     *            @OA\Property(property="property_id", type="integer", format="int64", example=1),
     *         ),
     *      ),
     *      @OA\Response(
     *          response=201,
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
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function createAppointment(PropertyAppointmentRequest $request)
    {
        try {
            $this->storeActivity($request, "");
            return DB::transaction(function () use ($request) {
                $request_data = $request->validated();
                $request_data["created_by"] = auth()->user()->id;

                // Make sure your model name matches: PropertyAppointment
                $appointment = PropertyAppointment::create($request_data);

                return response($appointment, 201);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     * @OA\Put(
     *      path="/v1.0/appointments",
     *      operationId="updateAppointment",
     *      tags={"property_management.appointment_management"},
     *      security={
     *           {"bearerAuth": {}}
     *      },
     *      summary="This method is to update property appointment",
     *      description="This method is to update property appointment",
     *
     *      @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *            required={"id","job_type","employee_id","start_date","end_date","property_id"},
     *            @OA\Property(property="id", type="integer", format="int64", example=1),
     *            @OA\Property(property="job_type", type="string", format="string", example="Inspection"),
     *            @OA\Property(property="employee_id", type="string", format="string", example="John Doe"),
     *            @OA\Property(property="start_date", type="string", format="date", example="2025-11-01"),
     *            @OA\Property(property="end_date", type="string", format="date", example="2025-11-05"),
     *            @OA\Property(property="description", type="string", format="string", example="Updated property inspection details"),
     *            @OA\Property(property="property_id", type="integer", format="int64", example=1),
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
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function updateAppointment(PropertyAppointmentRequest $request)
    {
        try {
            $this->storeActivity($request, "");
            return DB::transaction(function () use ($request) {
                $request_data = $request->validated();

                // Find the appointment
                $appointment = PropertyAppointment::findOrFail($request_data["id"]);

                // Optional: Check if user has permission to update this appointment
                // if ($appointment->created_by != auth()->id()) {
                //     return response()->json(['message' => 'Unauthorized'], 403);
                // }

                // Remove id from update data
                unset($request_data['id']);

                // Update the appointment
                $appointment->update($request_data);
                $appointment->refresh();

                return response($appointment, 200);
            });
        } catch (Exception $e) {
            error_log($e->getMessage());
            return $this->sendError($e, 500, $request);
        }
    }
    /**
     * @OA\Get(
     *      path="/v1.0/appointments",
     *      operationId="getAppointments",
     *      tags={"property_management.appointment_management"},
     *      security={
     *           {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Number of items per page",
     *         required=false,
     *         example="10"
     *      ),
     *      @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         description="Filter appointments from this date",
     *         required=false,
     *         example="2025-10-01"
     *      ),
     *      @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         description="Filter appointments until this date",
     *         required=false,
     *         example="2025-10-31"
     *      ),
     *      @OA\Parameter(
     *         name="order_by",
     *         in="query",
     *         description="Order direction (ASC or DESC)",
     *         required=false,
     *         example="DESC"
     *      ),
     *      @OA\Parameter(
     *         name="search_key",
     *         in="query",
     *         description="Search in job_type, employee_id, and description",
     *         required=false,
     *         example="inspection"
     *      ),
     *      @OA\Parameter(
     *         name="property_id",
     *         in="query",
     *         description="Filter by property ID",
     *         required=false,
     *         example="1"
     *      ),
     *      @OA\Parameter(
     *         name="job_type",
     *         in="query",
     *         description="Filter by job type",
     *         required=false,
     *         example="Inspection"
     *      ),
     *      @OA\Parameter(
     *         name="employee_id",
     *         in="query",
     *         description="Filter by employee name",
     *         required=false,
     *         example="John"
     *      ),
     *      summary="This method is to get property appointments",
     *      description="This method is to get property appointments with filtering and pagination",
     *
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
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *          @OA\JsonContent()
     *      )
     * )
     */
    public function getAppointments(Request $request)
    {
        try {
            $this->storeActivity($request, "");

            $appointments = PropertyAppointment::filter()
                ->paginate(request('per_page', 10));

            return response()->json($appointments, 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    /**
     * @OA\Delete(
     *      path="/v1.0/appointments/{id}",
     *      operationId="deleteAppointmentById",
     *      tags={"property_management.appointment_management"},
     *      security={
     *           {"bearerAuth": {}}
     *      },
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Appointment ID",
     *         required=true,
     *         example="1"
     *      ),
     *      summary="This method is to delete appointment by id",
     *      description="This method is to delete appointment by id",
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Appointment deleted successfully")
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
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent()
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Appointment not found")
     *          )
     *      )
     * )
     */
    public function deleteAppointmentById($id, Request $request)
    {
        try {
            $this->storeActivity($request, "");

            $appointment = PropertyAppointment::where('id', $id)
                ->where('created_by', auth()->id())
                ->first();

            if (!$appointment) {
                return response()->json([
                    "message" => "Appointment not found or you don't have permission to delete it"
                ], 404);
            }

            $appointment->delete();

            return response()->json([
                "message" => "Appointment deleted successfully"
            ], 200);
        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }
}
