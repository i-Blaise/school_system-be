<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSchoolProfileRequest;
use App\Models\SchoolProfile;
use Illuminate\Support\Facades\DB;

class SchoolProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(UpdateSchoolProfileRequest $request)
    {
        $validated = $request->validated();
        $school = $request->user()->school;

        DB::transaction(function () use ($validated, $school) {
            SchoolProfile::updateOrCreate(
                ['school_id' => $school->id],
                [
                    'logo_url' => $validated['logo_url'] ?? null,
                    'description' => $validated['description'] ?? null,
                ]
            );

            // Replace awards
            $school->awards()->delete();
            if (!empty($validated['awards'])) {
                $awards = collect($validated['awards'])->map(fn($award) => ['name' => $award]);
                $school->awards()->createMany($awards);
            }

            // Replace locations
            $school->locations()->delete();
            if (!empty($validated['locations'])) {
                $school->locations()->createMany($validated['locations']);
            }
        });

        return response()->json([
            'school_profile' => $school->profile,
            'awards' => $school->awards,
            'locations' => $school->locations,
        ]);
    }
}
