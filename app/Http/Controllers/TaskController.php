<?php

namespace App\Http\Controllers;

use App\Contracts\TaskCreationServiceInterface;
use App\Contracts\TaskServiceInterface;
use App\Contracts\TaskUpdateServiceInterface;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Http\Resources\TaskShowResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    protected $taskService;

    protected $taskCreationService;

    protected $taskUpdateService;

    protected $taskDependencyService;

    public function __construct(
        TaskServiceInterface $taskService,
        TaskCreationServiceInterface $taskCreationService,
        TaskUpdateServiceInterface $taskUpdateService
    ) {
        $this->taskService = $taskService;
        $this->taskCreationService = $taskCreationService;
        $this->taskUpdateService = $taskUpdateService;
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Task::class);
        $filters = $request->only(['status', 'due_from', 'due_to', 'assignee_id']);
        $perPage = (int) $request->input('per_page', 15);
        $tasks = $this->taskService->getAllTasks($filters, $perPage);

        return response()->json(TaskResource::collection($tasks));
    }

    public function myTasks(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'due_from', 'due_to']);
        $perPage = (int) $request->input('per_page', 15);
        $tasks = $this->taskService->getMyTasks(Auth::user(), $filters, $perPage);

        return response()->json(TaskResource::collection($tasks));
    }

    public function store(CreateTaskRequest $request): JsonResponse
    {
        $this->authorize('create', Task::class);
        $task = $this->taskCreationService->createTask($request->validated());

        return response()->json(new TaskResource($task), 201);
    }

    public function show($id): JsonResponse
    {
        $task = $this->taskService->getTaskOrFail($id);
        $this->authorize('view', $task);

        return response()->json(new TaskShowResource($task));
    }

    public function update(UpdateTaskRequest $request, $id): JsonResponse
    {
        $task = $this->taskService->getTaskOrFail($id);
        $this->authorize('update', $task);
        $updatedTask = $this->taskUpdateService->updateTask($task, $request->validated());

        return response()->json(new TaskResource($updatedTask), 200);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validated = $request->validate(['status' => 'required|string|in:pending,completed,canceled']);
        $task = $this->taskService->getTaskOrFail($id);
        $this->authorize('updateStatus', $task);
        $updatedTask = $this->taskUpdateService->updateStatus($task, $validated['status']);

        return response()->json(new TaskResource($updatedTask), 200);
    }

    public function addDependency(Request $request, $id): JsonResponse
    {
        $validated = $request->validate(['depends_on_id' => 'required|integer|exists:tasks,id']);
        $task = $this->taskService->getTaskOrFail($id);
        $this->authorize('addDependency', $task);
        $this->taskCreationService->addDependency($task, $validated['depends_on_id']);

        return response()->json(['message' => 'Dependency added successfully'], 200);
    }
}
