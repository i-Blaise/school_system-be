<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ImportTeachersRequest;

class TeacherImportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(ImportTeachersRequest $request)
    {
        $request->validated();
        
        $file = $request->file('file');
        $path = $file->storeAs('teachers_imports', $file->getClientOriginalName());

        return response()->json([
            'message' => 'Teacher import file uploaded successfully',
            'file_path' => $path,
        ]);
    }
}
