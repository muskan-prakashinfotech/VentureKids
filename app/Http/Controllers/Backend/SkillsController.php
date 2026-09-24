<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\StudentObservations;
use Illuminate\Http\Request;
use App\Models\StudentSkills;

class SkillsController extends Controller
{
    public function index()
    {
        $skill_list = StudentSkills::all();
        return view('backend.skills.index', compact('skill_list'));
    }

    public function create()
    {
        return view('backend.skills.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'skill_name' => 'required',
        ]);

        $skill = new StudentSkills();
        $skill->skill_name = $request->skill_name;
        
        $skill->save();

        return redirect()->route('backend.skill_list')->with('success', 'Skill added successfully.');
    }

    public function edit($skillId)
    {
        $skillData = StudentSkills::find($skillId);

        return view('backend.skills.edit', compact('skillData'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'skill_name' => 'required',
        ]);

        $skill_id = $request->skill_id;
        $skill = StudentSkills::find($skill_id);

        if($skill) {
            $skill->skill_name = $request->skill_name;
            $skill->save();
        }

        return redirect()->route('backend.skill_list')->with('success', 'Skill updated successfully.');
    }

    public function destory($skillId)
    {
        $skill = StudentSkills::find($skillId);

        if($skill) {
            $chk_skill_data = \DB::table('student_observations')->whereRaw('FIND_IN_SET(?, skill_id)', $skillId)->get();
            if($chk_skill_data->count()) {
                foreach($chk_skill_data as $rec) {
                    $sId = explode(',', $rec->skill_id);
                    while(($i = array_search($skillId, $sId)) !== false) {
                        unset($sId[$i]);
                    }
                    if(!empty($sId)) {
                        $skill_id = implode(',', $sId);
                    } else {
                        $skill_id = null;
                    }
                    StudentObservations::where('id', $rec->id)->update(['skill_id' => $skill_id]);
                }
            }
            $skill->delete(); 
        }

        return redirect()->route('backend.skill_list')->with('success', 'Skill deleted successfully.');
    }

}