<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Otp;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    //
    public function register(Request $request) {
        $request->validate([
            'mobile' => 'required|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'profile_image' => 'required'
        ]);


        $user = User::create([
            'mobile' => $request->mobile,
            'email' => $request->email,
            'profile_image' => $request->profile_image,
            'password' => password_hash($request->password, PASSWORD_BCRYPT),
        ]);

        $now = now();

        $emailOtp = rand(100000, 999999);
        $mobileOtp = rand(100000, 999999);

        Otp::create([
            'email' => $user->email,
            'otp' => $emailOtp,
            'expires_at' => $now->copy()->addMinutes(5)
        ]);

        Otp::create([
            'mobile' => $user->mobile,
            'otp' => $mobileOtp,
            'expires_at' => $now->copy()->addMinutes(5)
        ]);

        return response()->json([
            'message' => 'User registered successfully',
            'email_otp' => $emailOtp,
            'mobile_otp' => $mobileOtp
        ], 201);
    }

    public function generateOTP(Request $request)
    {
        if (!$request->email && !$request->mobile) {
            return response()->json([
                'error' => 'Email or mobile is required'
            ], 400);
        }

        $otp = rand(100000, 999999);
        $expiresAt = now()->addMinutes(5);

        if ($request->email) {
            $exists = User::where('email', $request->email)->exists();
            if (!$exists) {
                return response()->json(['error' => 'Email not registered'], 404);
            }

            Otp::updateOrCreate(
                ['email' => $request->email],
                ['otp' => $otp, 'expires_at' => $expiresAt, 'is_used' => false]
            );
        }

        if ($request->mobile) {
            $exists = User::where('mobile', $request->mobile)->exists();
            if (!$exists) {
                return response()->json(['error' => 'Mobile not registered'], 404);
            }

            Otp::updateOrCreate(
                ['mobile' => $request->mobile],
                ['otp' => $otp, 'expires_at' => $expiresAt, 'is_used' => false]
            );
        }

        return response()->json([
            'message' => 'OTP generated',
            'otp' => $otp
        ]);
    }

    public function verifyOTP(Request $request)
    {
        if (!$request->otp) {
            return response()->json(['error' => 'OTP is required'], 400);
        }

        $query = Otp::where('otp', $request->otp)
            ->where('is_used', false)
            ->where('expires_at', '>=', now());

        if ($request->email) {
            $query->where('email', $request->email);
            $user = User::where('email', $request->email)->first();
        } elseif ($request->mobile) {
            $query->where('mobile', $request->mobile);
            $user = User::where('mobile', $request->mobile)->first();
        } else {
            return response()->json(['error' => 'Email or mobile required'], 400);
        }

        $otpRecord = $query->first();

        if (!$otpRecord) {
            return response()->json([
                'error' => 'Invalid or expired OTP'
            ], 400);
        }

        if ($otpRecord->attempts >= 3) {
            return response()->json([
                'error' => 'Maximum OTP attempts exceeded'
            ], 429);
        }

        // increment attempts
        $otpRecord->increment('attempts');

        $otpRecord->update(['is_used' => true]);

        $user->increment('login_count');

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'Login successful',
            'token' => $token
        ]);
    }

    public function changePassword(Request $request)
    {
        $user = auth()->user();

        if ($request->new_password === $request->old_password) {
            return response()->json([
                'error' => 'Old password not allowed'
            ], 400);
        }

        if (!password_verify($request->old_password, $user->password)) {
            return response()->json([
                'error' => 'Invalid old password'
            ], 400);
        }

        $user->password = password_hash($request->new_password, PASSWORD_BCRYPT);
        $user->save();

        return response()->json([
            'message' => 'Password changed successfully'
        ]);
    }

    public function verifyPassword(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'result' => false,
                'message' => 'User not found'
            ]);
        }

        return response()->json([
            'result' => password_verify($request->password, $user->password)
        ]);
    }

}
