<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\TrainerLavelRequest;
use App\Models\Trainerlavel;
use App\Http\Traits\CustomFileUpload;
use Illuminate\Http\Request;

class TrainerLavelController extends Controller
{
    use CustomFileUpload;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $levels = Trainerlavel::orderBy('display_order_id')->get();
        return view('backend.levels.trainer.index', compact('levels'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.levels.trainer.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TrainerLavelRequest $request)
    {
        $data = $request->only('grade', 'image', 'display_order');
        
        $trainerLevel = new Trainerlavel();
        $trainerLevel->grade = $data['grade'];

        $image = $request->file('image');
        if(!empty($image)) {
            $image_name = uniqid() . '.' . $image->getClientOriginalExtension();
            $path = public_path('/image/level/trainer');
            $image->move($path, $image_name);
            $trainerLevel->image = $image_name;
        }

        $display_order_id = $data['display_order'];
        if(empty($display_order_id)) {
           $trainerLevel->display_order_id = $trainerLevel->getNextDisplayOrderId();
        } else {
           $trainerLevel->display_order_id = $display_order_id;
        }
        
        $trainerLevel->save();

        return redirect()->route('backend.trainerlevel.index')->with('success', 'Record added successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $grade = Trainerlavel::where('id',$id)->first();
        return view('backend.levels.trainer.edit', compact('grade'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(TrainerLavelRequest $request, $id)
    {
        $grade = Trainerlavel::where('id',$id)->first();
        $data = $request->only('grade', 'display_order');

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $image_name = uniqid() . '.' . $image->getClientOriginalExtension();
            $path = public_path('/image/level/trainer');
            $image->move($path, $image_name);
            $data['image'] = $image_name;

            $this->deleteFile(
                $gradeimg = $grade->image,
                'image/level/trainer/'
            );
        }

        $display_order_id = $data['display_order'];
        if(empty($display_order_id)) {
           $data['display_order_id'] = $grade->getNextDisplayOrderId();
        } else {
           $data['display_order_id'] = $display_order_id;
        }
        
        $grade->update($data);

        return redirect()->route('backend.trainerlevel.index')->with('success', 'Record updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $level = Trainerlavel::where('id',$id)->first();
        $this->deleteFile(
            $gradeimg = $level->image,
            'image/level/trainer/'
        );
        $level->delete();

        return redirect()->route('backend.trainerlevel.index')->with('success', 'Record deleted successfully.');
    }

    public function imageDelete(Request $request){
        if(isset($request->gradeid)){
            $grade = Trainerlavel::where('id',$request->gradeid)->first();
            $this->deleteFile(
                $gradeimg = $grade->image,
                'image/level/trainer/'
            );
            $grade->image = null;
            $grade->save();
            $output = array('status' => true, 'message' => 'Image delete succesfully');
        }else{
            $output = array('status' => false, 'message' => "Grade id not found");
        }
        return json_encode($output);
    }
}
