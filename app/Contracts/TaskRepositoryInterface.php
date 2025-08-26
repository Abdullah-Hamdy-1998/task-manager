<?php

namespace App\Contracts;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface TaskRepositoryInterface
{
    public function create(array $data): Model;
    public function find(int $id): ?Task;
    public function getFilteredTasks(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function getUserTasks(User $user, array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function update(Task $task, array $data): bool;
    public function updateStatus(Task $task, string $newStatus): bool;
}