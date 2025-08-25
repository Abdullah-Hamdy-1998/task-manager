<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateTaskRequest;
use App\Services\TaskService;
use App\Http\Resources\TaskResource;
use Illuminate\Http\JsonResponse;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function store(CreateTaskRequest $request): JsonResponse
    {
        $task = $this->taskService->createTask($request->validated());
        return response()->json(new TaskResource($task), 201);
    }
}