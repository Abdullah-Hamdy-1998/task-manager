<?php

namespace App\Observers;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskObserver
{
    /**
     * Handle the task "creating" event.
     */
    public function creating(Task $task): void
    {
        $task->created_by = Auth::id();
    }
}