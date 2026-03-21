<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\School;

class ResolveSchool
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->route('school_slug');

        if (! $slug) {
            abort(404, 'School slug not provided in route.');
        }

        $school = School::where('slug', $slug)->firstOrFail();

        app()->instance(School::class, $school);
        $request->attributes->add(['school' => $school]);

        return $next($request);
    }
}
