<?php

namespace App\Observers;

use App\Models\Live;

class LiveObserver
{
    /**
     * Handle the Live "created" event.
     */
    public function created(Live $live): void
    {
        //
    }

    /**
     * Handle the Live "updated" event.
     */
    public function updated(Live $live): void
    {
        //
    }

    /**
     * Handle the Live "deleted" event.
     */
    public function deleted(Live $live): void
    {
        //
    }

    /**
     * Handle the Live "restored" event.
     */
    public function restored(Live $live): void
    {
        //
    }

    /**
     * Handle the Live "force deleted" event.
     */
    public function forceDeleted(Live $live): void
    {
        //
    }
}
