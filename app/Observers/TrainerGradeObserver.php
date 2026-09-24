<?php

namespace App\Observers;

use App\Models\Trainerlavel;

class TrainerGradeObserver
{
    /**
     * Handle the Grade "creating" event.
     *
     * @param  \App\Models\Trainerlavel  $grade
     * @return void
     */
    public function creating(Trainerlavel $trainerlavel)
    {
        /*  GENERATE AND STORE UNIQUE CODE */
        $trainerlavel->unique_code = 'L'.substr(sha1(time()), 0, 5);
    }

    /**
     * Handle the Trainerlavel "created" event.
     *
     * @param  \App\Models\Trainerlavel  $trainerlavel
     * @return void
     */
    public function created(Trainerlavel $trainerlavel)
    {
        //
    }

    /**
     * Handle the Trainerlavel "updated" event.
     *
     * @param  \App\Models\Trainerlavel  $trainerlavel
     * @return void
     */
    public function updated(Trainerlavel $trainerlavel)
    {
        //
    }

    /**
     * Handle the Trainerlavel "deleted" event.
     *
     * @param  \App\Models\Trainerlavel  $trainerlavel
     * @return void
     */
    public function deleted(Trainerlavel $trainerlavel)
    {
        //
    }

    /**
     * Handle the Trainerlavel "restored" event.
     *
     * @param  \App\Models\Trainerlavel  $trainerlavel
     * @return void
     */
    public function restored(Trainerlavel $trainerlavel)
    {
        //
    }

    /**
     * Handle the Trainerlavel "force deleted" event.
     *
     * @param  \App\Models\Trainerlavel  $trainerlavel
     * @return void
     */
    public function forceDeleted(Trainerlavel $trainerlavel)
    {
        //
    }
}
