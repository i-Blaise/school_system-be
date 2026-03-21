<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterSchoolRequest;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterSchoolController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(RegisterSchoolRequest $request)
    {
        $validated = $request->validated();

        $result = DB::transaction(function () use ($validated) {
            $school = School::create([
                'name' => $validated['school_name'],
                'slug' => $validated['school_slug'],
            ]);

            $admin = User::create([
                'school_id' => $school->id,
                'name' => $validated['admin_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'admin',
            ]);

            $token = $admin->createToken('admin-token')->plainTextToken;

            return [
                'school' => $school,
                'admin' => $admin,
                'token' => $token,
            ];
        });

        return response()->json($result, 201);
    }
}
