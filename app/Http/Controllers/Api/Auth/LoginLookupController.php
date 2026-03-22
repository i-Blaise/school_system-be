<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginLookupRequest;
use App\Models\User;

class LoginLookupController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(LoginLookupRequest $request)
    {
        $validated = $request->validated();
        
        $users = User::with('school')->where('email', $validated['email'])->get();
        
        if ($users->isEmpty()) {
            return response()->json([
                'message' => 'No account found with that email address.',
                'schools' => []
            ], 404);
        }
        
        $schools = $users->map(function ($user) {
            return [
                'school_id' => $user->school->id,
                'school_name' => $user->school->name,
                'school_slug' => $user->school->slug,
            ];
        });
        
        return response()->json([
            'message' => 'Account found.',
            'schools' => $schools
        ]);
    }
}
