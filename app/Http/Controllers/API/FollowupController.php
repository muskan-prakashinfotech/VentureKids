<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Resources\StudentResource;
use App\Mail\CreatedStudentMail;
use App\Models\User;
use App\Models\Students;
use App\Models\EmailInfo;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Events\Backend\UserCreated;
use App\Models\Permission;
use App\Models\Role;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator;

class FollowupController extends Controller
{
    use ApiResponse;
    protected $module_name;

    public function __construct()
    {
        $this->module_name = 'users';
    }
    
    public function followUp(Request $request)
    {
        $rules = [
            'parent_email'  => 'required|email',
            'grade_id'      => 'required',
        ];
        
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $this->sendResponse(
                implode(',', $validator->messages()->all()),
                422
            );
        }
        
        $getStudent = User::where('email', $request->parent_email)->get();

        if($getStudent->count() == 0) {
            return $this->sendResponse('Student Email does not match with our record', 200);
        }

        $studentDetail = $getStudent->toArray();
        
        $student = new Students();
        
        $student->user_id = $studentDetail[0]['id'];
        // $student->school_id = $school->id;
        $student->name = $studentDetail[0]['name'];
        $student->parent_name = $studentDetail[0]['name'];
        $student->parent_email = $request->parent_email;
        // $student->address = $request->address;
        // $student->image = $image_name;
        $student->grade_id = $request->grade_id;
        $student->save();

        // Mail::to($request->email)->send(new CreatedStudentMail($request->email, $request->password));
        // Mail::to($request->parent_email)->send(new CreatedStudentMail($request->email, $request->password));

        $student_email = new EmailInfo;
        $student_email->name = $studentDetail[0]['name'];
        $student_email->mail_address = $request->parent_email;
        $student_email->mail_description = '';
        $student_email->group = 4;
        $student_email->save();

        
        return $this->sendResponse('Student successfully created!', 200);
    }
}
