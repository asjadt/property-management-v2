<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Mail;
use App\Mail\DevOtpMail;

class DevAccessController extends Controller
{
    public function showLoginForm()
    {
        return view('dev-login');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // GET ALLOWED EMAILS FROM ENV (COMMA-SEPARATED)
        $allowedEmails = array_map('trim', explode(',', env('DEV_ACCESS_EMAIL', '')));

        if (!in_array($request->email, $allowedEmails)) {
            return response()->json(['message' => 'Unauthorized email address.'], 403);
        }

        // Generate 6 digit OTP
        $otp = sprintf("%06d", mt_rand(100000, 999999));

        // Store OTP in session with 5 minute expiry
        Session::put('dev_otp_' . $request->email, [
            'otp'        => $otp,
            'expires_at' => now()->addMinutes(5)->timestamp,
        ]);

        // Send Email
        Mail::to($request->email)->send(new DevOtpMail($otp));

        return response()->json(['message' => 'OTP sent successfully.']);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6'
        ]);

        // GET OTP FROM SESSION
        $otpData = Session::get('dev_otp_' . $request->email);

        if (!$otpData || now()->timestamp > $otpData['expires_at'] || $otpData['otp'] !== $request->otp) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 400);
        }

        // OTP IS VALID — CLEAR IT
        Session::forget('dev_otp_' . $request->email);
        session(['dev_access_granted' => true]);

        return response()->json(['message' => 'Login successful', 'redirect' => url('/')]);
    }

    public function logout()
    {
        session()->forget('dev_access_granted');
        return redirect()->route('dev.login');
    }
}
