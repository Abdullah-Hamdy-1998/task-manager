<?php

namespace App\Services;

use App\Contracts\TaskCreationServiceInterface;
use App\Contracts\TaskCycleServiceInterface;
use App\Contracts\TaskRepositoryInterface;
use App\Contracts\TaskServiceInterface;
use App\Exceptions\TaskAlreadyCompletedException;
use App\Exceptions\TaskCycleDependencyException;
use App\Exceptions\TaskDuplicateDependencyException;
use App\Models\Task;

class TaskCreationService implements TaskCreationServiceInterface
{
    protected $taskRepository;

    protected $taskCycleService;

    protected $taskService;

    public function __construct(
        TaskRepositoryInterface $taskRepository,
        TaskCycleServiceInterface $taskCycleService,
        TaskServiceInterface $taskService
    ) {
        $this->taskRepository = $taskRepository;
        $this->taskCycleService = $taskCycleService;
        $this->taskService = $taskService;
    }

    public function createTask(array $data): Task
    {
        $task = $this->taskRepository->create($data);

        if (isset($data['depends_on_id'])) {
            $this->addDependency($task, $data['depends_on_id']);
        }

        return $task;
    }

    public function addDependency(Task $task, int $dependsOnId): void
    {
        if ($task->status === 'complete') {
            throw new TaskAlreadyCompletedException;
        }

        $dependsOnTask = $this->taskService->getTaskOrFail($dependsOnId);

        if ($this->taskCycleService->wouldCreateCycle($task, $dependsOnTask)) {
            throw new TaskCycleDependencyException;
        }

        if ($task->dependsOnTasks()->where('depends_on_task_id', $dependsOnId)->exists()) {
            throw new TaskDuplicateDependencyException;
        }

        $task->dependsOnTasks()->attach($dependsOnId);
    }
}
