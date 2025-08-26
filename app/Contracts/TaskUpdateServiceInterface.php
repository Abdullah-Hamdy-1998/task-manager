<?php

namespace App\Contracts;

use App\Models\Task;

interface TaskUpdateServiceInterface
{
    public function updateTask(Task $task, array $data): Task;

    public function updateStatus(Task $task, string $newStatus): Task;
}
