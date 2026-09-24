<?php

namespace App\Rules;

use App\Traits\CsvFIleupload;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Validator;

class CsvValidator implements Rule
{
    use CsvFIleupload;
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $rules;
    public $csvdata;
    public $errors = [];

    public function __construct($rules)
    {
        $this->rules = $rules;
        $this->errors = [];
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        if (!file_exists($value) || !is_readable($value)) {
            return false;
        }
        $csvData = $this->getCsvAsArray($value);

        $errors = [];
        foreach ($csvData as $rowIndex => $csvValues) {
            $validator = Validator::make($csvValues, $this->rules);
            if (!empty($this->headingRow)) {
                $validator->setAttributeNames($this->headingRow);
            }
            if ($validator->fails()) {
                $errors[$rowIndex] = $validator->messages()->toArray();
            }
        }
        $this->errors = $errors;

        return count($this->errors) == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        $message = [];
        foreach ($this->errors as $key => $error) {
            foreach ($error as $field => $value) {
                foreach ($value as $val) {
                    $message[] = str_replace('.', '', $val) . ' at Row ' . ($key + 2);
                }
            }
        }
        
        if(!count($message)) {
            $message[] = 'This file have some incorrect values';
        }
        
        return $message;
    }
}
