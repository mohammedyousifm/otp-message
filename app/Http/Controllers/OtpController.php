<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\OtpMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class OtpController extends Controller
{
    public function showOtpForm()
    {
        return view('otp.form');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);


        // Generate a random 6-digit OTP
        $otp = rand(100000, 999999);


        // Save OTP in session (you could save it to the database instead for production)
        Session::put('otp', $otp);
        Session::put('otp_email', $request->email);


        // Send OTP to email
        Mail::to($request->email)->send(new OtpMail($otp));

        return back()->with('success', 'OTP has been sent to your email.');
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric'
        ]);

        // Check if OTP matches the one stored in the session
        if (Session::get('otp') == $request->otp && Session::get('otp_email') == $request->email) {
            Session::forget(['otp', 'otp_email']); // Clear OTP session data

            // Regenerate the session to prevent fixation
            $request->session()->regenerate();

            return redirect('/dashboard')->with('success', 'OTP verified successfully!');
        } else {
            return back()->withErrors(['otp' => 'Invalid OTP, please try again.']);
        }
    }
}
