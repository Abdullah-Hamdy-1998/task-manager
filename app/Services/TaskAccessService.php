<?php

namespace App\Services;

use App\Contracts\TaskServiceInterface;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TaskAccessService
{
    protected TaskServiceInterface $taskService;

    public function __construct(TaskServiceInterface $taskService)
    {
        $this->taskService = $taskService;
    }

    public function getTasksForUser(User $user, Request $request, int $perPage = 15): LengthAwarePaginator
    {
        if ($this->isManager($user)) {
            return $this->getTasksForManager($request, $perPage);
        }

        return $this->getTasksForRegularUser($user, $request, $perPage);
    }

    private function isManager(User $user): bool
    {
        return $user->role->name === 'manager';
    }

    private function getTasksForManager(Request $request, int $perPage): LengthAwarePaginator
    {
        $filters = $request->only(['status', 'due_from', 'due_to', 'assignee_id']);
        return $this->taskService->getAllTasks($filters, $perPage);
    }

    private function getTasksForRegularUser(User $user, Request $request, int $perPage): LengthAwarePaginator
    {
        $filters = $request->only(['status', 'due_from', 'due_to']);
        $filters['assignee_id'] = $user->id; // Only show tasks assigned to this user
        return $this->taskService->getAllTasks($filters, $perPage);
    }

    public function getAllowedFilters(User $user): array
    {
        $baseFilters = ['status', 'due_from', 'due_to'];
        
        if ($this->isManager($user)) {
            $baseFilters[] = 'assignee_id';
        }
        
        return $baseFilters;
    }
}