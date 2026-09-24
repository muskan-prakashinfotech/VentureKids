<?php

namespace App\Http\Controllers\Backend;

use App\Authorizable;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;
use App\Models\AdminOthers;
use App\Models\User;

class AdminsettingController extends Controller
{
    /////////////////////start
    public function resources(){

       $resource = AdminOthers::where('setting_name','teacher')->first()->toArray();
       //print_r($resource);die();
       return view('backend.others.resources')->with('resource',$resource);
   }

   public function getResourceValue(Request $request){

        $resource = $request->resource;
        $data['teacher']='';
        $data['student']='';
        $data['school']='';
        if($resource ==1){
            $data['teacher'] = AdminOthers::where('setting_name','teacher')->first()->toArray();
        }else if($resource == 2){
            $data['student'] = AdminOthers::where('setting_name','student')->first()->toArray();
        }else if($resource == 3){
            $data['school'] = AdminOthers::where('setting_name','school')->first()->toArray();
        }
        
        echo json_encode($data);

    }

    public function saveResourceValue(Request $request){

        $other_resource = $request->other_resource;
        $textareaValue = $request->textareaValue;
        
          if($other_resource == 1){
            $guide = AdminOthers::where('setting_name','teacher')->first();
            $guide->setting_value = $textareaValue;
            $guide->save();

            $update_terms=User::where('group','3')->update(array('termandcondition' => 0));

            echo 1;
        }else if($other_resource == 2){
            $guide = AdminOthers::where('setting_name','student')->first();
            $guide->setting_value = $textareaValue;
            $guide->save();

            $update_terms=User::where('group','4')->update(array('termandcondition' => 0));

            echo 2;
        }else if($other_resource == 3){
            $guide = AdminOthers::where('setting_name','school')->first();
            $guide->setting_value = $textareaValue;
            $guide->save();

            $update_terms=User::where('group','2')->update(array('termandcondition' => 0));

            echo 3;
        }
        
    }



    public function trainerGuide(){
        $guide = AdminOthers::where('setting_name','trainer_guide')->first()->toArray();
        return view('backend.others.trainerguide')->with('guide',$guide);
    }

    public function updateTrainerguide(Request $request){

        $validated = $request->validate([
            'description' => 'required',
        ]);

        $guide = AdminOthers::where('setting_name','trainer_guide')->first();
        $guide->setting_name = "trainer_guide";
        $guide->setting_value = $request->description;
        $guide->save();

        return redirect()->route('backend.trainerguide.trainerGuide')->with('success', 'Data Updated successfully.');
    
    }
    


    public function updateResources(Request $request){
        $validated = $request->validate([
            'resource' => 'required',
        ]);

        $guide = AdminOthers::where('setting_name','resources')->first();
        $guide->setting_name = "resources";
        $guide->setting_value = $request->resource;
        $guide->save();

        return redirect()->route('backend.resources.resources')->with('success', 'Data Updated successfully.');
    }
    public function trainerTerms(){
        $trainerterm = AdminOthers::where('setting_name','trainer_terms_and_privacy_policy')->first()->toArray();
        echo json_encode($trainerterm);
        // return view('backend.others.trainer_terms')->with('trainerterm',$trainerterm);
    }

    public function updateTrainerTerms(Request $request){
        
        $validated = $request->validate([
            'trainer_description' => 'required',
        ]);

        $guide = AdminOthers::where('setting_name','trainer_terms_and_privacy_policy')->first();
        $guide->setting_name = "trainer_terms_and_privacy_policy";
        $guide->setting_value = $request->trainer_description;
        $guide->save();

        return redirect()->route('backend.trainerterms.trainerTerms')->with('success', 'Data Updated successfully.');
    }

    public function schoolTerms(){
       $schoolterm = AdminOthers::where('setting_name','school_terms_and_privacy_policy')->first()->toArray();
       echo json_encode($schoolterm);
        // return view('backend.others.school_terms')->with('trainerterm',$schoolterm);
    }

    public function updateSchoolTerms(Request $request){
        
        $validated = $request->validate([
            'school_description' => 'required',
        ]);
        //::where('setting_name','school_terms_and_privacy_policy')->first();
        $guide = AdminOthers::where('setting_name','school_terms_and_privacy_policy')->first();
        $guide->setting_name = "school_terms_and_privacy_policy";
        $guide->setting_value = $request->school_description;
        $guide->save();

        return redirect()->route('backend.schoolterms.schoolTerms')->with('success', 'Data Updated successfully.');
    }

    public function studentTerms(){
        $studentterm = AdminOthers::where('setting_name','student_terms_and_privacy_policy')->first()->toArray();
        echo json_encode($studentterm);
        //  return view('backend.others.student_terms')->with('studentterm',$studentterm);
     }
 
     public function updateStudentTerms(Request $request){
         
         $validated = $request->validate([
             'student_description' => 'required',
         ]);
         
         $guide = AdminOthers::where('setting_name','student_terms_and_privacy_policy')->first();
         $guide->setting_name = "student_terms_and_privacy_policy";
         $guide->setting_value = $request->student_description;
         $guide->save();
 
         return redirect()->route('backend.studentterms.studentTerms')->with('success', 'Data Updated successfully.');
     }

    
}