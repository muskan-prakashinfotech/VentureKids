<?php

namespace App\Http\Requests\Backend\Content;

use Illuminate\Foundation\Http\FormRequest;

class Contentrequest extends FormRequest
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
                'agegroup_id' => 'required',
                'stream_id' => 'required',
                'title' => 'required',
                'video'=> 'required|mimes:mp4',
            ];
        } else {
            return [
                'agegroup_id' => 'required',
                'stream_id' => 'required',
                'title' => 'required',
                'video'=> 'sometimes|mimes:mp4',
            ];
        }
    }
}
