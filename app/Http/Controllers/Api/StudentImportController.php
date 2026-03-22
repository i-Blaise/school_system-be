<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportStudentsRequest;

class StudentImportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ImportStudentsRequest $request)
    {
        $request->validated();
        
        $file = $request->file('file');
        $path = $file->storeAs('students_imports', $file->getClientOriginalName());

        return response()->json([
            'message' => 'Student import file uploaded successfully',
            'file_path' => $path,
        ]);
    }
}
