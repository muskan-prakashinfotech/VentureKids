<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class LevelRequest extends FormRequest
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
        if ($this->getMethod() == 'post') {
            return [
                'grade'             => 'required',
                'description'       => 'required',
                'cert_description'  => 'required',
                'cert_quote'        => 'required',
                'image'             => 'required|image',
                'assessment_order'  => 'nullable|integer|min:1',
            ];
        } else {
            return [
                'grade'             => 'required',
                'description'       => 'required',
                'cert_description'  => 'required',
                'cert_quote'        => 'required',
                'image'             => 'sometimes|image',
                'assessment_order'  => 'nullable|integer|min:1',
            ];
        }
    }

    public function attributes()
    {
        return  [
            'grade' => 'name',
            'cert_description' => 'certificate description',
            'cert_quote' => 'certificate quote',
        ];
    }
}
