<?php

use App\Contracts\TaskCreationServiceInterface;
use App\Contracts\TaskServiceInterface;
use App\Contracts\TaskUpdateServiceInterface;
use App\Http\Controllers\TaskController;
use App\Http\Requests\CreateTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Services\TaskAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

beforeEach(function () {
    $this->taskService = \Mockery::mock(TaskServiceInterface::class);
    $this->taskCreationService = \Mockery::mock(TaskCreationServiceInterface::class);
    $this->taskUpdateService = \Mockery::mock(TaskUpdateServiceInterface::class);
    $this->taskAccessService = \Mockery::mock(TaskAccessService::class);

    $this->controller = new TaskController(
        $this->taskService,
        $this->taskCreationService,
        $this->taskUpdateService,
        $this->taskAccessService
    );

    // Create test user and login first
    $role = Role::factory()->create(['name' => 'manager']);
    $this->user = User::factory()->create(['role_id' => $role->id]);

    Auth::login($this->user);

    // Create tasks after authentication
    $this->task = Task::factory()->create([
        'created_by' => $this->user->id,
        'assignee_id' => $this->user->id,
    ]);
    $this->dependencyTask = Task::factory()->create([
        'created_by' => $this->user->id,
        'assignee_id' => $this->user->id,
    ]);
});

afterEach(function () {
    \Mockery::close();
});

it('returns paginated tasks on index', function () {
    // Arrange
    $filters = ['status' => 'pending'];
    $perPage = 15;

    $tasks = new LengthAwarePaginator(
        collect([$this->task]),
        1,
        $perPage,
        1
    );

    $request = new Request($filters + ['per_page' => $perPage]);

    $this->taskAccessService
        ->shouldReceive('getTasksForUser')
        ->once()
        ->with($this->user, $request, $perPage)
        ->andReturn($tasks);

    // Act
    $response = $this->controller->index($request);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(200);
});

it('uses default per page when not provided on index', function () {
    // Arrange
    $filters = [];
    $defaultPerPage = 15;

    $tasks = new LengthAwarePaginator(
        collect([]),
        0,
        $defaultPerPage,
        1
    );

    $request = new Request;

    $this->taskAccessService
        ->shouldReceive('getTasksForUser')
        ->once()
        ->with($this->user, $request, $defaultPerPage)
        ->andReturn($tasks);

    // Act
    $response = $this->controller->index($request);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(200);
});

it('creates new task on store', function () {
    // Arrange
    $taskData = [
        'title' => 'New Task',
        'description' => 'Task description',
        'due_date' => '2024-12-31',
        'assignee_id' => $this->user->id,
    ];

    $request = \Mockery::mock(CreateTaskRequest::class);
    $request->shouldReceive('validated')->once()->andReturn($taskData);

    $this->taskCreationService
        ->shouldReceive('createTask')
        ->once()
        ->with($taskData)
        ->andReturn($this->task);

    // Act
    $response = $this->controller->store($request);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(201);
});

it('returns task details on show', function () {
    // Arrange
    $taskId = $this->task->id;

    $this->taskService
        ->shouldReceive('getTaskOrFail')
        ->once()
        ->with($taskId)
        ->andReturn($this->task);

    // Act
    $response = $this->controller->show($taskId);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(200);
});

it('modifies existing task on update', function () {
    // Arrange
    $taskId = $this->task->id;
    $updateData = [
        'title' => 'Updated Task',
        'description' => 'Updated description',
    ];

    $request = \Mockery::mock(UpdateTaskRequest::class);
    $request->shouldReceive('validated')->once()->andReturn($updateData);

    $this->taskService
        ->shouldReceive('getTaskOrFail')
        ->once()
        ->with($taskId)
        ->andReturn($this->task);

    $this->taskUpdateService
        ->shouldReceive('updateTask')
        ->once()
        ->with($this->task, $updateData)
        ->andReturn($this->task);

    // Act
    $response = $this->controller->update($request, $taskId);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(200);
});

it('changes task status on updateStatus', function () {
    // Arrange
    $taskId = $this->task->id;
    $newStatus = 'completed';

    $this->taskService
        ->shouldReceive('getTaskOrFail')
        ->once()
        ->with($taskId)
        ->andReturn($this->task);

    $this->taskUpdateService
        ->shouldReceive('updateStatus')
        ->once()
        ->with($this->task, $newStatus)
        ->andReturn($this->task);

    $request = new Request(['status' => $newStatus]);

    // Act
    $response = $this->controller->updateStatus($request, $taskId);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(200);
});

it('creates task dependency on addDependency', function () {
    // Arrange
    $taskId = $this->task->id;
    $dependsOnId = $this->dependencyTask->id;

    $this->taskService
        ->shouldReceive('getTaskOrFail')
        ->once()
        ->with($taskId)
        ->andReturn($this->task);

    $this->taskCreationService
        ->shouldReceive('addDependency')
        ->once()
        ->with($this->task, $dependsOnId)
        ->andReturn(true);

    $request = new Request(['depends_on_id' => $dependsOnId]);

    // Act
    $response = $this->controller->addDependency($request, $taskId);

    // Assert
    expect($response)->toBeInstanceOf(JsonResponse::class);
    expect($response->getStatusCode())->toBe(200);

    $responseData = json_decode($response->getContent(), true);
    expect($responseData)->toHaveKey('message');
    expect($responseData['message'])->toBe('Dependency added successfully');
});
