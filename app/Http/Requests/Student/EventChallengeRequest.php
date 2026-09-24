<?php

namespace App\Http\Requests\Student;

use Illuminate\Foundation\Http\FormRequest;

class EventChallengeRequest extends FormRequest
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
            'event_id' => 'required',
            'description' => 'required',
            'attachment' => 'required|min:1|array',
            'attachment.*' => 'required|file',
        ];
    }
}
