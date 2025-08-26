<?php

namespace App\Contracts;

use App\Models\Task;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskServiceInterface
{
    public function getAllTasks(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function getTask(int $id): ?Task;

    public function getTaskOrFail(int $id): Task;
}
