<?php

namespace App\Http\Requests\Backend;

use App\Models\School;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreOrUpdateExternalSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true; // Set to false if you want to add authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $attendeeType = $this->input('attendee_type', 'schools');
        $sessionType  = $this->input('session_type', '1');

        $zoomLinkRules = ($sessionType == '1' && $attendeeType !== 'trainer')
            ? ['required', 'string', 'url', 'regex:/^https:\/\/(www\.)?zoom\.us\/(j|s)\/[0-9]+/']
            : ['nullable', 'string'];

        $rules = [
            'title'          => 'required|string|max:255',
            'date_time'      => 'required|date',
            'session_type'   => 'required|in:0,1',
            'attendee_type'  => 'required|in:schools,trainer',
            'zoom_link'      => $zoomLinkRules,
            'agenda'         => 'nullable|string',
            'send_zoom_link' => 'nullable',
            'speaker'        => 'required|string|max:100',
            'all_schools'    => 'nullable',
            'schools'        => 'required_without:all_schools|array',
            'schools.*'      => isPartnerUser()
                ? [Rule::exists('schools', 'id')->where('country_id', partnerCountryId())]
                : ['exists:schools,id'],
            'all_batches'    => 'nullable',
            'batches'        => 'nullable|array',
            'batches.*'      => 'exists:school_batch,id',
            'notify_recipients'   => 'nullable|array',
            'notify_recipients.*' => 'in:trainer,school,student',
            'recurrence_type'     => 'nullable|in:none,daily,weekly,monthly,custom',
            'recurrence_interval' => 'nullable|integer|min:1',
            'recurrence_days'     => 'nullable|array',
            'recurrence_days.*'   => 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'recurrence_end_date' => 'nullable|date|after_or_equal:date_time',
            'recurrence_count'    => 'nullable|integer|min:1|max:500',
            'recurrence_custom_dates'   => 'required_if:recurrence_type,custom|nullable|array',
            'recurrence_custom_dates.*' => 'nullable|date',
        ];

        // levels only required for schools attendee type
        if ($attendeeType === 'schools') {
            $rules['all_levels'] = 'nullable';
            $rules['levels']     = 'required_without:all_levels|array';
            $rules['levels.*']   = 'exists:grades,id';
        } else {
            $rules['levels'] = 'nullable|array';
            // trainer_ids optional selection
            $rules['trainer_ids']   = 'nullable|array';
            $rules['trainer_ids.*'] = 'integer|exists:trainers,id';
        }

        return $rules;
    }
}
