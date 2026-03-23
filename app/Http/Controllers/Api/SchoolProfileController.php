<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateSchoolProfileRequest;
use App\Models\SchoolProfile;
use Illuminate\Support\Facades\DB;

class SchoolProfileController extends Controller
{
    public function show(\Illuminate\Http\Request $request)
    {
        $school = $request->user()->school;

        return response()->json([
            'school_profile' => $school->profile,
            'awards' => $school->awards,
            'locations' => $school->locations,
        ]);
    }

    public function update(UpdateSchoolProfileRequest $request)
    {
        $validated = $request->validated();
        $school = $request->user()->school;

        DB::transaction(function () use ($request, $validated, $school) {
            // 1. Update base School Name if provided
            if (isset($validated['school_name'])) {
                $school->update(['name' => $validated['school_name']]);
            }

            // 2. Handle Logo Upload
            $logoUrl = $school->profile->logo_url ?? null;
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('school_logos', 'public');
                $logoUrl = asset('storage/' . $path);
            }

            // 3. Update School Profile
            SchoolProfile::updateOrCreate(
                ['school_id' => $school->id],
                [
                    'logo_url' => $logoUrl,
                    'description' => $validated['description'] ?? null,
                ]
            );

            // 4. Replace awards
            $school->awards()->delete();
            if (!empty($validated['awards'])) {
                $awards = collect($validated['awards'])->map(fn($award) => ['name' => $award]);
                $school->awards()->createMany($awards);
            }

            // 5. Replace locations
            if (isset($validated['school_location'])) {
                $school->locations()->delete();
                $school->locations()->create([
                    'address' => $validated['school_location'],
                    'is_primary' => true
                ]);
            }
        });

        return response()->json([
            'school_profile' => $school->profile,
            'awards' => $school->awards,
            'school' => $school->refresh()
        ]);
    }
}
