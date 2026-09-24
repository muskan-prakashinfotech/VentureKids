<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\StudentMindset;
use App\Models\StudentMindsetData;

class MindsetController extends Controller
{
    public function index()
    {
        $mindset_list = StudentMindset::all();
        return view('backend.mindset.index', compact('mindset_list'));
    }

    public function create()
    {
        return view('backend.mindset.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'mindset_name' => 'required',
        ]);

        $mindset = new StudentMindset();
        $mindset->mindset_name = $request->mindset_name;
        
        $mindset->save();

        return redirect()->route('backend.mindset_list')->with('success', 'Mindset added successfully.');
    }

    public function edit($mindsetId)
    {
        $mindsetData = StudentMindset::find($mindsetId);

        return view('backend.mindset.edit', compact('mindsetData'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'mindset_name' => 'required',
        ]);

        $mindset_id = $request->mindset_id;
        $mindset = StudentMindset::find($mindset_id);

        if($mindset) {
            $mindset->mindset_name = $request->mindset_name;
            $mindset->save();
        }

        return redirect()->route('backend.mindset_list')->with('success', 'Mindset updated successfully.');
    }

    public function destory($mindsetId)
    {
        $mindset = StudentMindset::find($mindsetId);

        if($mindset) {
            StudentMindsetData::where('mindset_id', $mindsetId)->delete();
            $mindset->delete(); 
        }

        return redirect()->route('backend.mindset_list')->with('success', 'Mindset deleted successfully.');
    }

}