<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        try {
            /**
             * BEFORE LOGGED IN GLOBAL SCOPE WILL NOT BE APPLIED DUE TO NO SESSION VALUE
             * AND WHILE LOADING LARAVEL APPLICATION (on boot) GLOBAL SCOPE SHOULD NOT BE APPLIED
             * THAT'S WHY ADDED DEFAULT VALUE AND AS SOON AS `tenant_id` GOT FROM SESSION
             * SCOPE WILL BE APPLIED
             */
            $tenantId = session()->get('tenant_id', 0);

            if (!empty($tenantId)) {
                $builder->where('tenant_id', $tenantId);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
