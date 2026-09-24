<?php

namespace App\Observers;

use App\Models\Grade;

class GradeObserver
{
    /**
     * Handle the Grade "creating" event.
     *
     * @param  \App\Models\Grade  $grade
     * @return void
     */
    public function creating(Grade $grade)
    {
         /*  GENERATE AND STORE UNIQUE CODE */
        $grade->unique_code = 'L'.substr(sha1(time()), 0, 5);
    }

    /**
     * Handle the Grade "created" event.
     *
     * @param  \App\Models\Grade  $grade
     * @return void
     */
    public function created(Grade $grade)
    {
        //  
    }

    /**
     * Handle the Grade "updated" event.
     *
     * @param  \App\Models\Grade  $grade
     * @return void
     */
    public function updated(Grade $grade)
    {
        //
    }

    /**
     * Handle the Grade "deleted" event.
     *
     * @param  \App\Models\Grade  $grade
     * @return void
     */
    public function deleted(Grade $grade)
    {
        //
    }

    /**
     * Handle the Grade "restored" event.
     *
     * @param  \App\Models\Grade  $grade
     * @return void
     */
    public function restored(Grade $grade)
    {
        //
    }

    /**
     * Handle the Grade "force deleted" event.
     *
     * @param  \App\Models\Grade  $grade
     * @return void
     */
    public function forceDeleted(Grade $grade)
    {
        //
    }
}
