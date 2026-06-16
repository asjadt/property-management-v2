<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        $allowedEmail = env('DEV_ACCESS_EMAIL');

        if ($request->email !== $allowedEmail) {
            return response()->json(['message' => 'Unauthorized email address.'], 403);
        }

        // Generate 6 digit OTP
        $otp = sprintf("%06d", mt_rand(100000, 999999));

        // Store in cache for 5 minutes
        Cache::put('dev_otp_' . $request->email, $otp, now()->addMinutes(5));

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

        $cachedOtp = Cache::get('dev_otp_' . $request->email);

        if (!$cachedOtp || $cachedOtp !== $request->otp) {
            return response()->json(['message' => 'Invalid or expired OTP.'], 400);
        }

        // OTP is valid
        Cache::forget('dev_otp_' . $request->email);
        session(['dev_access_granted' => true]);

        return response()->json(['message' => 'Login successful', 'redirect' => url('/')]);
    }

    public function logout()
    {
        session()->forget('dev_access_granted');
        return redirect()->route('dev.login');
    }
}
