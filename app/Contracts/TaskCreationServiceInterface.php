<?php

namespace App\Contracts;

use App\Models\Task;

interface TaskCreationServiceInterface
{
    public function createTask(array $data): Task;

    public function addDependency(Task $task, int $dependsOnId): void;
}
