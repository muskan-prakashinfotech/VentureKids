<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProjectTheme;
use App\Models\StudentGrade;

class ProjectThemeController extends Controller
{
    public function projectThemeCreate()
    {
        $grades = StudentGrade::all();
        return view('backend.projectThemes.add_projectTheme', compact('grades'));
    } 
    
    public function projectThemeStore(Request $request)
    {
        $request->validate([
            'project_theme_name' => 'required|string|max:255',
            'student_grade_id' => 'required|exists:student_grade,id',
            'status' => 'required|in:0,1',
        ]);

        // Save to database
        $theme = new ProjectTheme();
        $theme->student_grade_id = $request->student_grade_id;
        $theme->project_theme_name = $request->project_theme_name;
        $theme->status = $request->status;
        $theme->save();

        return redirect()->route('backend.projectThemelist.projectThemeList')->with('success', 'Project Theme created successfully!');

    } 
     
    
    public function getProjectTheme(Request $request)
    {
        
        $themes = ProjectTheme::with('grade') // eager load grade relation
        ->select('id', 'student_grade_id', 'project_theme_name','status');

        return datatables()->of($themes)
        ->addColumn('grade', function($row) {
            return $row->grade ? $row->grade->name : '-';
        })
        ->addColumn('status', function ($row) {
            return $row->status 
                ? '<span class="badge badge-success">Active</span>' 
                : '<span class="badge badge-danger">Inactive</span>';
        })
        ->addColumn('action', function ($row) {
            $actionbtn = '<div class="ActionBtns">';

            // Edit button
            $actionbtn .= '<a href="' . route('backend.projectThemeedit.projectThemeEdit', $row->id) . '" 
                  class="btn btn-block btn-info btn-sm">
                  <i class="fas fa-edit"></i>
               </a>';

            // Delete button
            $actionbtn .= '<a href="' . route('backend.projectThemedelete.projectThemeDelete', $row->id) . '" 
                  class="btn btn-block btn-danger btn-sm delete-theme"
                  id="deleteProjectTheme"
                  data-id="' . $row->id . '"
                  data-url="' . route('backend.projectThemedelete.projectThemeDelete', $row->id) . '">
                  <i class="fas fa-trash"></i>
               </a>';

            $actionbtn .= '</div>';

            return $actionbtn;
        })
        ->rawColumns(['status', 'action'])
        ->make(true);
        
    } 
    
    
    public function projectThemeList()
    {
       return view('backend.projectThemes.list_projectTheme');
    } 

    public function projectThemeEdit($id)
    {
        $grades = StudentGrade::all();
        $theme = ProjectTheme::findOrFail($id);

        return view('backend.projectThemes.edit_projectTheme', compact('grades', 'theme'));
    }

    public function projectThemeUpdate(Request $request ,$id)
    {
       $theme = ProjectTheme::findOrFail($id);

        $request->validate([
            'project_theme_name' => 'required|string|max:255',
            'student_grade_id' => 'required|exists:student_grade,id',
            'status' => 'required|in:0,1',
        ]);

        $theme->update([
            'project_theme_name' => $request->project_theme_name,
            'student_grade_id' => $request->student_grade_id,
            'status' =>$request->status,
        ]);
        //dd($request->all(), $theme);

        return redirect()->route('backend.projectThemelist.projectThemeList')->with('success', 'Project Theme updated successfully!');
    }

    public function projectThemeDelete($id)
    {
        $theme = ProjectTheme::findOrFail($id);
        $theme->delete();

        return response()->json(['success' => true]);
    }

}
