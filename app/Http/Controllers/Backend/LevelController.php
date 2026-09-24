<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\LevelRequest;
use App\Models\Grade;
use App\Http\Traits\CustomFileUpload;
use Illuminate\Http\Request;
class LevelController extends Controller
{
    use CustomFileUpload;
    public function index()
    {
        $levels = Grade::orderBy('display_order_id')->get();
        
        return view('backend.levels.index', compact('levels'));
    }

    public function create()
    {
        return view('backend.levels.create');
    }

    public function store(LevelRequest $request)
    {
        $data = $request->only('grade', 'description', 'cert_description', 'cert_quote', 'image', 'display_order', 'assessment_order', 'levelPublish');
        
        $grade = new Grade();
        $grade->grade = $data['grade'];
        $grade->description = $data['description'];
        $grade->cert_description = $data['cert_description'];
        $grade->cert_quote = $data['cert_quote'];
        
        $image = $request->file('image');
        if(!empty($image)) {
            $image_name = uniqid() . '.' . $image->getClientOriginalExtension();
            $path = public_path('/image/level');
            $image->move($path, $image_name);
            $grade->image = $image_name;
        }
        
        $level_icon = $request->file('level_icon');
        if(!empty($level_icon)) {
            $level_icon_name = uniqid() . '.' . $level_icon->getClientOriginalExtension();
            $path = public_path('/image/level/icon');
            $level_icon->move($path, $level_icon_name);
            $grade->level_icon = $level_icon_name;
        } else {
            return back()->withErrors(["level_icon" => "The upload icon field is required."])->withInput();
        }

        if(isset($request->is_primary)) {
            $grade->is_primary = 1;
        }

        $display_order_id = $data['display_order'];
        if(empty($display_order_id)) {
           $grade->display_order_id = $grade->getNextDisplayOrderId();
        } else {
           $grade->display_order_id = $display_order_id;
        }
        $grade->assessment_order = $data['assessment_order'] ?? null;

        $levelPublish = $request->input('levelPublish');
        $grade->is_publish = ($levelPublish == 1) ? 1 : 0;
        
        $grade->save();
        
        return redirect()->route('backend.level.index')->with('success', 'Record added successfully.');
    }

    public function edit(Grade $grade)
    {
        return view('backend.levels.edit', compact('grade'));
    }

    public function update(Grade $grade, LevelRequest $request)
    {
        $data = $request->only('grade', 'description', 'cert_description', 'cert_quote', 'display_order', 'assessment_order', 'levelPublish');
        $gradeData = Grade::select(['image','level_icon'])->where('id',$grade->id)->get();
        if ($request->hasFile('image')) {
            if($gradeData->count() && !empty($gradeData[0]->image)) {
                $this->deleteFile($grade->image,'image/level/');
            }
            $image = $request->file('image');
            $image_name = uniqid() . '.' . $image->getClientOriginalExtension();
            $path = public_path('/image/level');
            $image->move($path, $image_name);
            $data['image'] = $image_name;
        }
        $level_icon = $request->file('level_icon');
        if(!empty($level_icon)) {
            if($gradeData->count() && !empty($gradeData[0]->level_icon)) {
                $this->deleteFile($grade->level_icon,'image/level/icon/');
            }
            $level_icon_name = uniqid() . '.' . $level_icon->getClientOriginalExtension();
            $path = public_path('/image/level/icon');
            $level_icon->move($path, $level_icon_name);
            $data['level_icon'] = $level_icon_name;
        } else if(empty($gradeData[0]->level_icon)) {
            return back()->withErrors(["level_icon" => "The upload icon field is required."])->withInput();
        }
        if(isset($request->is_primary)) {
            $data['is_primary'] = 1;
        } else {
            $data['is_primary'] = 0; 
        }
        
        $display_order_id = $data['display_order'];
        if(empty($display_order_id)) {
           $data['display_order_id'] = $grade->getNextDisplayOrderId();
        } else {
           $data['display_order_id'] = $display_order_id;
        }
        $data['assessment_order'] = $data['assessment_order'] ?? null;

        $levelPublish = $request->input('levelPublish');
        $data['is_publish'] = ($levelPublish == 1) ? 1 : 0;
        
        $grade->update($data);

        return redirect()->route('backend.level.index')->with('success', 'Record updated successfully.');
    }

    public function imageDelete(Request $request){
        if(isset($request->gradeid)){
            $grade = Grade::where('id',$request->gradeid)->first();
            if(isset($request->imageType)) {
                if($request->imageType == 'image') {
                    $this->deleteFile(
                        $gradeimg = $grade->image,
                        'image/level/'
                    );
                    $grade->image = null;
                }
                if($request->imageType == 'icon') {
                    $this->deleteFile(
                        $gradeimg = $grade->level_icon,
                        'image/level/icon/'
                    );
                    $grade->level_icon = null;
                }
                $grade->save();
            }
            $output = array('status' => true, 'message' => 'File Deleted Succesfully!');
        }else{
            $output = array('status' => false, 'message' => "Grade Not Found!");
        }
        return json_encode($output);
    }

    public function destory(Grade $grade)
    {
        $grade->delete();

        return redirect()->route('backend.level.index')->with('success', 'Record deleted successfully.');
    }
}
