<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\School;
use DB;
use File;
use Illuminate\Support\Facades\Validator;

class SchoolController extends Controller
{
    public function add_school()
    {
        return view('admin.school.add_school');
    }

    public function school_store(Request $req)
    {

        $validatedData = Validator::make($req->all(), [
            'school_name' => 'required',
            'address' => 'required',
            'year_establish' => 'required',
            'incharge_name' => 'required',
            'email' => 'required|unique:schools',
            'contact_number' => 'required',
            'school_logo' => 'file|mimes:jpeg,png,jpg|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $data=array();
		$data['school_name']=$req->school_name;
        $data['school_address']=$req->address;
        $data['year_establish']=$req->year_establish;
        $data['incharge_name']=$req->incharge_name;
        $data['email']=$req->email;
        $data['contact_number']=$req->contact_number;
        $data['partner_name']=$req->partner_name;
        $data['course_start_date']=$req->course_start_date;
        $data['create_entrepreneurship']=$req->radio1;
        $data['weekly_class_time']=$req->weekly_class_time;
        $data['package']=$req->package;

        $school_logo=$req->school_logo;
        $excel_file=$req->upload_excel;
   
        if($school_logo)
        {
            $image_name=Str::random(10);//unique nmae generate every time
            $ext=strtolower($school_logo->getClientOriginalExtension());
            $image_full_name='school_'.$image_name.'.'.$ext;

            $upload_path='image/school/';
            
            $success=$school_logo->move($upload_path,$image_full_name);

            $data['school_logo']=$upload_path.$image_full_name;
        }

        if($excel_file)
        {
            $excel_name=Str::random(10);//unique nmae generate every time
            $ext=strtolower($excel_file->getClientOriginalExtension());
            $image_full_name='excel_'.$excel_name.'.'.$ext;

            $upload_path='image/school/excel/';
            
            $success=$excel_file->move($upload_path,$image_full_name);

            $data['upload_excel']=$upload_path.$image_full_name;
        }

        $student_grade['grade3_student']=$req->grade3_student;
        $student_grade['grade4_student']=$req->grade4_student;
        $student_grade['grade5_student']=$req->grade5_student;
        $student_grade['grade6_student']=$req->grade6_student;
        $student_grade['grade7_student']=$req->grade7_student;
        $student_grade['grade8_student']=$req->grade8_student;
        $student_grade['grade9_student']=$req->grade9_student;
        $student_grade['grade10_student']=$req->grade10_student;
        $student_grade['grade11_student']=$req->grade11_student;
        $student_grade['grade12_student']=$req->grade12_student;
        $new=json_encode($student_grade);

        $school_logo=$req->school_logo;
        $excel_file=$req->upload_excel;


        $data['school_grade']=$new;
        $success=DB::table('schools')->insert($data);
        
        // if($last_id)
        // {
           
        //     echo '<pre>';
        //     print_r($new);
        //     die();
        //     $success=DB::table('student_grade')->insert($student_grade);
        // }
       

       if($success)
       {
            $notification=array(
                'message'=>'School successfully Inserted!',
                'success'=>'success',
            );

            return redirect()->route('school-list')->with($notification);
       }

        
        
    }

    public function school_list()
    {
        $school_list=School::all();
        // echo '<pre>';
        // print_r($school_list);
        // die();
        return view('admin.school.school_list',compact('school_list'));
    }

    public function school_edit($id)
    {
       $school=School::find($id);
    //    echo '<pre>';
    //    print_r(json_decode($school['school_grade']));
    //    die(); 
       return view('admin.school.edit_school',compact('school'));
    }

    public function school_update(Request $req)
    {
        $schhol_id=$req->school_id;
        $email=$req->school_email;

        $validatedData = Validator::make($req->all(), [
            'school_name' => 'required',
            'address' => 'required',
            'year_establish' => 'required',
            'incharge_name' => 'required',
            'email' => 'required',
            'contact_number' => 'required',
            'school_logo' => 'file|mimes:jpeg,png,jpg|max:5120' // 5MB
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $data=array();
		$data['school_name']=$req->school_name;
        $data['school_address']=$req->address;
        $data['year_establish']=$req->year_establish;
        $data['incharge_name']=$req->incharge_name;
        $data['email']=$req->email;
        $data['contact_number']=$req->contact_number;
        $data['partner_name']=$req->partner_name;
        $data['course_start_date']=$req->course_start_date;
        $data['create_entrepreneurship']=$req->radio1;
        $data['weekly_class_time']=$req->weekly_class_time;
        $data['package']=$req->package;

        $school_logo=$req->school_logo;
        $excel_file=$req->upload_excel;

        $old_school_logo=$req->old_school_logo;
        $old_excel_file=$req->old_excel;

        if($school_logo){

            if($old_school_logo != ''){
                if(File::exists($old_school_logo)){
                    unlink($old_school_logo);
                }
            }


            $image_name=Str::random(10);//unique nmae generate every time
            $ext=strtolower($school_logo->getClientOriginalExtension());
            $image_full_name='school_'.$image_name.'.'.$ext;

            $upload_path='image/school/';
            
            $success=$school_logo->move($upload_path,$image_full_name);

            $data['school_logo']=$upload_path.$image_full_name;

        }

        if($excel_file)
        {
            if($old_excel_file != ''){
                if(File::exists($old_excel_file)){
                    unlink($old_excel_file);
                }
            }
            $excel_name=Str::random(10);//unique nmae generate every time
            $ext=strtolower($excel_file->getClientOriginalExtension());
            $image_full_name='excel_'.$excel_name.'.'.$ext;

            $upload_path='image/school/excel/';
            
            $success=$excel_file->move($upload_path,$image_full_name);

            $data['upload_excel']=$upload_path.$image_full_name;
        }

        
        $student_grade['grade3_student']=$req->grade3_student;
        $student_grade['grade4_student']=$req->grade4_student;
        $student_grade['grade5_student']=$req->grade5_student;
        $student_grade['grade6_student']=$req->grade6_student;
        $student_grade['grade7_student']=$req->grade7_student;
        $student_grade['grade8_student']=$req->grade8_student;
        $student_grade['grade9_student']=$req->grade9_student;
        $student_grade['grade10_student']=$req->grade10_student;
        $student_grade['grade11_student']=$req->grade11_student;
        $student_grade['grade12_student']=$req->grade12_student;
        $new=json_encode($student_grade);

        $data['school_grade']=$new;

        // echo '<pre>';
        // print_r($data);
        // die();
        //$school_update=School::first($schhol_id);
        $success=School::where("id",$schhol_id)->update($data);


        if($success)
        {
             $notification=array(
                 'message'=>'School successfully Updated!',
                 'success'=>'success',
             );
 
             return redirect()->route('school-list')->with($notification);
        }

    }

    public function school_delete($id)
    {
        $data=School::find($id);
   
        if($data->school_logo)
        {
            $destinationPath = \Storage::disk('tenant_uploads')->path($data->school_logo);
            if ($data->tenant_id && File::exists($destinationPath)) {
                File::delete($destinationPath);
            } else {
                unlink($data->school_logo);
            }
        }
        if($data->school_cover_image)
        {
            $destinationPath = \Storage::disk('tenant_uploads')->path($data->school_cover_image);
            if ($data->tenant_id && File::exists($destinationPath)) {
                File::delete($destinationPath);
            } else {
                unlink($data->school_cover_image);
            }
        }
        if($data->upload_excel)
        {
            unlink($data->upload_excel);
        }

		$success=School::where('id',$id)->delete();

        if($success)
        {
            $notification=array(
                'message'=>'School Successfully deleted!',
                'success'=>'success',
            );
            return redirect()->back()->with($notification);
        }
		
    }
}
