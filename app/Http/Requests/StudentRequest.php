<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StudentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $requiresStudentGrade = !auth()->check() || !isPartnerUser();

        if ($this->getMethod() == 'post') {
            return [
                'name'          => 'required',
                'username' => [
                    'required',
                    'string',
                    'max:50',
                    'regex:/^[A-Za-z0-9._]+$/',  // allowed characters
                    'unique:users,username'
                ],
                'email'         => 'nullable|email|unique:users,email',
                'student_grade_id' => $requiresStudentGrade ? 'required' : 'nullable',
                'country'       => 'required',
                'date_of_birth' => 'nullable|date',
                'gender'        => 'required|in:Male,Female,Other',
                'school_batch' => 'required',
                // 'grade_id'      => 'required'
                // 'address'       => 'required',
                // 'parent_name'   => 'required',
                // 'parent_email'  => 'required',
                // 'mobile'        => 'required',
                // 'profile_image' => 'required|image',
                // 'password'      => 'required|confirmed',
            ];
        }else{
            // dd($_POST);
            return [
                'name'          => 'required',
                'username' => [
                    'required',
                    'string',
                    'max:50',
                    'regex:/^[A-Za-z0-9._]+$/',  // allowed characters
                    'unique:users,username,'.$this->user_id
                ],
                'email'         => 'nullable|email|unique:users,email,'.$this->user_id,
                'student_grade_id' => $requiresStudentGrade ? 'required' : 'nullable',
                'country'       => 'required',
                'date_of_birth' => 'nullable|date',
                'gender'        => 'required|in:Male,Female,Other',
                'school_batch' => 'required',
                // 'grade_id'      => 'required'
                // 'address'       => 'required',
                // 'parent_name'   => 'required',
                // 'parent_email'  => 'required',
                // 'mobile'        => 'required',
                // 'profile_image' => 'required|image',
                // 'password'      => 'required|confirmed',
            ];
        }
    }
}
