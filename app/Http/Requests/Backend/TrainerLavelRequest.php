<?php

namespace App\Http\Requests\Backend;

use Illuminate\Foundation\Http\FormRequest;

class TrainerLavelRequest extends FormRequest
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
                'image'             => 'required|image',
            ];
        } else {
            return [
                'grade'             => 'required',
                'image'             => 'sometimes|image',
            ];
        }
    }

    public function attributes()
    {
        return  [
            'grade' => 'name',
        ];
    }
}
