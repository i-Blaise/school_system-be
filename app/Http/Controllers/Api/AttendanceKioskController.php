<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class AttendanceKioskController extends Controller
{
    /**
     * Generate a short-lived QR token for the attendance kiosk.
     */
    public function generateToken(Request $request)
    {
        $schoolId = $request->user()->school_id;
        
        $token = (string) Str::uuid();
        
        // Store in cache for strictly 30 seconds, binding it to the specific school
        Cache::put("qr_attendance_{$token}", $schoolId, now()->addSeconds(30));

        // Frontend builds the visual QR utilizing this raw token
        return response()->json([
            'success' => true,
            'data' => [
                'token' => $token,
                'expires_in_seconds' => 30,
            ]
        ]);
    }
}
