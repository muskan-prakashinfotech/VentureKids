<?php

namespace App\Http\Requests;

use App\Rules\CsvValidator;
use Illuminate\Foundation\Http\FormRequest;

class SchoolAdminRequest extends FormRequest
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
        return [
            'school_name' => 'required',
            'city' => 'required',
            'address' => 'required',
            'year_establish' => 'required',
            'incharge_name' => 'required',
            'incharge_email' => 'required',
            'contact_number' => 'required',
            'fee_per_student' => 'required',
            'upload_csv'   => ['mimes:csv,txt,xls,xlsx', new CsvValidator([
                'Full Name' => 'required',
                'Child Email' => ['required', 'unique:users,email'],
                'Level' => ['required','string'],
                'Gender' => ['required', 'in:Male,Female,Other'],
                'Date of Birth' => ['required', 'date'],
                'Parent Full Name' => 'required',
                'Parent Email' => 'required|email',
                'Address' => 'string',
            ])],
            // 'upload_students_levels_csv' => ['mimes:csv,txt,xls,xlsx', new CsvValidator([
            //     'Full Name' => 'required',
            //     'Email' => ['required', 'exists:users,email'],
            //     'Level' => ['required', 'exists:grades,grade'],
            // ])],
        ];
    }

    public function attributes()
    {
        return  [
            'upload_csv'                    => 'upload excel',
            // 'upload_students_levels_csv'    => "upload excel for student's level",
        ];
    }
}
