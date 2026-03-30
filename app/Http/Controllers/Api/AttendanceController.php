<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Attendance;
use App\Models\User;
use App\Notifications\ManualAttendanceNotification;

class AttendanceController extends Controller
{
    /**
     * Handle Teacher QR Scan Clock-In/Clock-Out.
     */
    public function clock(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'action' => 'required|in:in,out',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $teacher = $request->user();

        // Validate the cache token
        $schoolIdFromToken = Cache::get("qr_attendance_{$request->token}");

        if (!$schoolIdFromToken) {
            return response()->json(['success' => false, 'message' => 'QR Code is expired or invalid. Please scan again.'], 400);
        }

        if ($schoolIdFromToken !== $teacher->school_id) {
            return response()->json(['success' => false, 'message' => 'This QR Code belongs to a different school.'], 403);
        }

        $today = now()->format('Y-m-d');
        
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $teacher->id, 'date' => $today, 'school_id' => $teacher->school_id]
        );

        $locationData = ($request->latitude && $request->longitude) 
            ? ['lat' => $request->latitude, 'lng' => $request->longitude] 
            : null;

        if ($request->action === 'in') {
            if ($attendance->clock_in) {
                return response()->json(['success' => false, 'message' => 'You have already clocked in today.'], 400);
            }
            $attendance->update([
                'clock_in' => now(),
                'clock_in_location' => $locationData,
                'clock_in_method' => 'qr_scan',
            ]);
            $msg = 'Successfully clocked in!';
        } else {
            if (!$attendance->clock_in) {
                return response()->json(['success' => false, 'message' => 'You must clock in before clocking out.'], 400);
            }
            if ($attendance->clock_out) {
                return response()->json(['success' => false, 'message' => 'You have already clocked out today.'], 400);
            }
            $attendance->update([
                'clock_out' => now(),
                'clock_out_location' => $locationData,
                'clock_out_method' => 'qr_scan',
            ]);
            $msg = 'Successfully clocked out!';
        }

        return response()->json(['success' => true, 'message' => $msg, 'data' => $attendance]);
    }

    /**
     * Handle Admin manual clock-in/out for teachers.
     */
    public function adminClock(Request $request)
    {
        $request->validate([
            'user_id' => 'required|uuid|exists:users,id',
            'action' => 'required|in:in,out',
            'note' => 'required|string|max:500',
        ]);

        $admin = $request->user();
        $targetTeacher = User::findOrFail($request->user_id);

        if ($targetTeacher->school_id !== $admin->school_id) {
            return response()->json(['success' => false, 'message' => 'Cannot modify attendance for a teacher in a different school.'], 403);
        }

        $today = now()->format('Y-m-d');
        
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $targetTeacher->id, 'date' => $today, 'school_id' => $admin->school_id]
        );

        if ($request->action === 'in') {
            if ($attendance->clock_in) {
                return response()->json(['success' => false, 'message' => 'This teacher has already been clocked in today.'], 400);
            }
            $attendance->update([
                'clock_in' => now(),
                'clock_in_method' => 'admin_manual',
                'clocked_in_by' => $admin->id,
                'admin_note' => trim($attendance->admin_note . "\nClock In Note: " . $request->note),
            ]);
            $actionLabel = 'clocked in';
        } else {
            if (!$attendance->clock_in) {
                return response()->json(['success' => false, 'message' => 'The teacher must be clocked in before they can be clocked out.'], 400);
            }
            if ($attendance->clock_out) {
                return response()->json(['success' => false, 'message' => 'This teacher has already been clocked out today.'], 400);
            }
            $attendance->update([
                'clock_out' => now(),
                'clock_out_method' => 'admin_manual',
                'clocked_out_by' => $admin->id,
                'admin_note' => trim($attendance->admin_note . "\nClock Out Note: " . $request->note),
            ]);
            $actionLabel = 'clocked out';
        }

        // Notify Teacher
        $targetTeacher->notify(new ManualAttendanceNotification($actionLabel, $admin->name, $request->note));

        return response()->json(['success' => true, 'message' => "Successfully {$actionLabel} for {$targetTeacher->name}.", 'data' => $attendance]);
    }
}
