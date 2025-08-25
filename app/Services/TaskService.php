<?php

namespace App\Services;

use App\Repositories\TaskRepository;
use Illuminate\Database\Eloquent\Model;

class TaskService
{
    protected $taskRepository;

    public function __construct(TaskRepository $taskRepository)
    {
        $this->taskRepository = $taskRepository;
    }

    public function createTask(array $data): Model
    {
        return $this->taskRepository->create($data);
    }
}