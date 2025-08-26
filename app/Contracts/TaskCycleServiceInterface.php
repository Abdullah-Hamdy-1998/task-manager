<?php

namespace App\Contracts;

use App\Models\Task;

interface TaskCycleServiceInterface
{
    public function wouldCreateCycle(Task $task, Task $dependsOnTask): bool;
}