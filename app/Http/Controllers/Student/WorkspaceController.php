<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AIToolSubcategory;
use Illuminate\Support\Facades\Session;

class WorkspaceController extends Controller
{
    public function index_new()
    {
        $studentId=Session::get('student_id');
        $subcategories = AIToolSubcategory::select('id', 'ai_tool_id', 'name', 'image', 'description', 'status', 'display_order')
            ->with(['tool:id,url'])
            ->orderBy('display_order', 'asc')
            ->get()
            ->map(function ($subcategory) {
            $subcategory->hasAccess = $subcategory->status == 1;
            return $subcategory;
        });

        return view('student.workspace.my-workspace', compact('subcategories','studentId'));
     }

}
