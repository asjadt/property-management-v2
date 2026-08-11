<?php

namespace App\Http\Controllers;

use App\Http\Utils\ErrorUtil;
use App\Http\Utils\UserActivityUtil;
use App\Models\Invoice;
use App\Models\Landlord;
use App\Models\LandlordRentPayable;
use App\Models\Property;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class LandlordPortalController extends Controller
{
    use ErrorUtil, UserActivityUtil;

    /**
     * Resolve the Landlord record for the currently-authenticated landlord-User.
     * Returns null if the caller is not a landlord-User.
     */
    private function resolveAuthLandlord(): ?Landlord
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();

        if (!$authUser->hasRole('landlord')) {
            return null;
        }

        return Landlord::where('user_id', $authUser->id)->first();
    }

    // -------------------------------------------------------------------------
    // GET /v1.0/landlord-portal/profile
    // -------------------------------------------------------------------------

    /**
     * Return the landlord profile for the currently-logged-in landlord-User.
     */
    public function getMyProfile(Request $request)
    {
        try {
            $this->storeActivity($request, '');

            $landlord = $this->resolveAuthLandlord();

            if (!$landlord) {
                return response()->json([
                    'success' => false,
                    'message' => 'No landlord profile linked to this account.',
                    'data'    => [],
                ], Response::HTTP_NOT_FOUND);
            }

            // LOAD RELATIONSHIPS
            $landlord->load('properties');

            return response()->json([
                'success' => true,
                'message' => 'Landlord profile retrieved successfully.',
                'data'    => $landlord,
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    // -------------------------------------------------------------------------
    // PUT /v1.0/landlord-portal/profile
    // -------------------------------------------------------------------------

    /**
     * Allow the landlord to update their own profile fields.
     */
    public function updateMyProfile(Request $request)
    {
        try {
            $this->storeActivity($request, '');

            $landlord = $this->resolveAuthLandlord();
            if (!$landlord) {
                return response()->json([
                    'success' => false,
                    'message' => 'No landlord profile linked to this account.',
                    'data'    => [],
                ], Response::HTTP_NOT_FOUND);
            }

            // VALIDATE — landlord can update profile info, not ownership fields
            $validated = $request->validate([
                'first_Name'     => 'required|string|max:255',
                'last_Name'      => 'required|string|max:255',
                'phone'          => 'nullable|string',
                'image'          => 'nullable|string',
                'address_line_1' => 'nullable|string',
                'address_line_2' => 'nullable|string',
                'country'        => 'nullable|string',
                'city'           => 'nullable|string',
                'postcode'       => 'nullable|string',
                'lat'            => 'nullable|string',
                'long'           => 'nullable|string',
            ]);

            DB::transaction(function () use ($landlord, $validated) {
                $landlord->update($validated);
            });

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully.',
                'data'    => $landlord->fresh(),
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    // -------------------------------------------------------------------------
    // GET /v1.0/landlord-portal/properties
    // -------------------------------------------------------------------------

    /**
     * Return all properties linked to the authenticated landlord.
     */
    public function getMyProperties(Request $request)
    {
        try {
            $this->storeActivity($request, '');

            $landlord = $this->resolveAuthLandlord();
            if (!$landlord) {
                return response()->json([
                    'success' => false,
                    'message' => 'No landlord profile linked to this account.',
                    'data'    => [],
                ], Response::HTTP_NOT_FOUND);
            }

            // GET ALL PROPERTIES FOR THIS LANDLORD VIA PIVOT
            $properties = Property::whereHas('property_landlords', function ($q) use ($landlord) {
                $q->where('property_landlords.landlord_id', $landlord->id);
            })->get();

            return response()->json([
                'success' => true,
                'message' => 'Properties retrieved successfully.',
                'data'    => $properties,
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    // -------------------------------------------------------------------------
    // GET /v1.0/landlord-portal/invoices
    // -------------------------------------------------------------------------

    /**
     * Return all invoices linked to the authenticated landlord.
     */
    public function getMyInvoices(Request $request)
    {
        try {
            $this->storeActivity($request, '');

            $landlord = $this->resolveAuthLandlord();
            if (!$landlord) {
                return response()->json([
                    'success' => false,
                    'message' => 'No landlord profile linked to this account.',
                    'data'    => [],
                ], Response::HTTP_NOT_FOUND);
            }

            // GET INVOICES WHERE THIS LANDLORD IS LINKED VIA invoice_landlords
            $invoices = Invoice::with('invoice_items', 'invoice_payments', 'property')
                ->whereHas('landlords', function ($q) use ($landlord) {
                    $q->where('landlords.id', $landlord->id);
                })
                ->whereNotIn('invoices.status', ['draft'])
                ->orderByDesc('invoice_date')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Invoices retrieved successfully.',
                'data'    => $invoices,
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    // -------------------------------------------------------------------------
    // GET /v1.0/landlord-portal/rent-payables
    // -------------------------------------------------------------------------

    /**
     * Return all landlord rent payables for the authenticated landlord.
     */
    public function getMyRentPayables(Request $request)
    {
        try {
            $this->storeActivity($request, '');

            $landlord = $this->resolveAuthLandlord();
            if (!$landlord) {
                return response()->json([
                    'success' => false,
                    'message' => 'No landlord profile linked to this account.',
                    'data'    => [],
                ], Response::HTTP_NOT_FOUND);
            }

            // GET RENT PAYABLES FOR THIS LANDLORD
            $rentPayables = LandlordRentPayable::with('payable_rents.rent', 'rent_adjustments')
                ->where('landlord_id', $landlord->id)
                ->orderByDesc('create_date')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Rent payables retrieved successfully.',
                'data'    => $rentPayables,
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }

    // -------------------------------------------------------------------------
    // GET /v1.0/landlord-portal/report
    // -------------------------------------------------------------------------

    /**
     * Return a summary report (total invoiced, paid, due) for the authenticated landlord.
     */
    public function getLandlordReport(Request $request)
    {
        try {
            $this->storeActivity($request, '');

            $landlord = $this->resolveAuthLandlord();
            if (!$landlord) {
                return response()->json([
                    'success' => false,
                    'message' => 'No landlord profile linked to this account.',
                    'data'    => [],
                ], Response::HTTP_NOT_FOUND);
            }

            // TOTAL AMOUNT INVOICED
            $totalInvoiced = Invoice::whereHas('landlords', fn($q) => $q->where('landlords.id', $landlord->id))
                ->whereNotIn('invoices.status', ['draft'])
                ->sum('total_amount');

            // TOTAL AMOUNT PAID
            $totalPaid = Invoice::whereHas('landlords', fn($q) => $q->where('landlords.id', $landlord->id))
                ->whereNotIn('invoices.status', ['draft'])
                ->with('invoice_payments')
                ->get()
                ->sum(fn($inv) => $inv->invoice_payments->sum('amount'));

            // TOTAL RENT PAYABLES DUE
            $totalRentPayable = LandlordRentPayable::where('landlord_id', $landlord->id)
                ->sum('total_amount');

            return response()->json([
                'success' => true,
                'message' => 'Landlord report retrieved successfully.',
                'data'    => [
                    'landlord'          => $landlord->only(['id', 'first_Name', 'last_Name', 'email']),
                    'total_invoiced'    => (float) $totalInvoiced,
                    'total_paid'        => (float) $totalPaid,
                    'total_due'         => (float) ($totalInvoiced - $totalPaid),
                    'total_rent_payable'=> (float) $totalRentPayable,
                ],
            ], Response::HTTP_OK);

        } catch (Exception $e) {
            return $this->sendError($e, 500, $request);
        }
    }
}
