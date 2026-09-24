<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class StudentScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        try {
            /**
             * BEFORE LOGGED IN GLOBAL SCOPE WILL NOT BE APPLIED DUE TO NO SESSION VALUE
             * AND WHILE LOADING LARAVEL APPLICATION (on boot) GLOBAL SCOPE SHOULD NOT BE APPLIED
             * THAT'S WHY ADDED DEFAULT VALUE AND AS SOON AS `school_id` GOT FROM SESSION
             * SCOPE WILL BE APPLIED
             */
            $tenantId = session()->get('tenant_id', 0);
            $studentSchoolId = session()->get('student_school_id', 0);

            // IF TENANT VERIFIED THEN TENANT WISED STUDENT
            if (!empty($tenantId)) {
                $builder->where('school_id', function ($query) use ($tenantId) {
                    $query->select('id')
                          ->from('schools')
                          ->where('tenant_id', $tenantId);
                });
            }
            // OTHERWISE CHECK FOR STUDENT SCHOOL SESSION AND RETURN SCHOOL WISED STUDENT 
            else if (!empty($studentSchoolId)) {
                $builder->where('school_id', $studentSchoolId);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
