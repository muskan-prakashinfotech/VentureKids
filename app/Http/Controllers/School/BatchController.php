<?php

namespace App\Http\Controllers\school;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolBatch;
use App\Models\Students;
use App\Models\TrainerAllocationNew;
use Illuminate\Support\Facades\Session;

class BatchController extends Controller
{
    public function index()
    {
        $school_id = Session::get('school_id');
        $batchData = SchoolBatch::where('school_id', $school_id)->get();
        return view('school.batch.index', compact('batchData'));
    }

    public function create()
    {
        return view('school.batch.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'batch_name' => 'required',
        ]);
        
        $batch = new SchoolBatch();
        $batch->school_id = Session::get('school_id');
        $batch->batch_name = $request->batch_name;
        $batch->save();

        return redirect()->route('school.batch-list')->with('success', 'Batch Added Successfully!');
    }

    public function edit($batchId)
    {
        $batchData = SchoolBatch::where('school_id', Session::get('school_id'))->where('id', $batchId)->get();
        return view('school.batch.edit', compact('batchData'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'batch_name' => 'required',
        ]);
        
        $batch = SchoolBatch::where('school_id', Session::get('school_id'))->find($request->batch_id);
        if($batch) {
            $batch->batch_name = $request->batch_name;
            $batch->save();
            return redirect()->route('school.batch-list')->with('success', 'Batch Updated Successfully!');
        }
        return redirect()->route('school.batch-list')->with('error', 'Error Occurred!');
    }

    public function destory($batch_id)
    {
        $batch = SchoolBatch::where('school_id', Session::get('school_id'))->find($batch_id);

        if($batch) {
            Students::where('school_batch_id',$batch_id)->update(['school_batch_id' => null]);
            TrainerAllocationNew::where('school_batch_id',$batch_id)->where('school_id', Session::get('school_id'))->delete();
            $batch->delete(); 
        }

        return redirect()->route('school.batch-list')->with('success', 'Batch Deleted Successfully!');
    }

}