<?php

namespace App\Providers;

use App\Contracts\TaskCreationServiceInterface;
use App\Contracts\TaskCycleServiceInterface;
use App\Contracts\TaskFilterServiceInterface;
use App\Contracts\TaskRepositoryInterface;
use App\Contracts\TaskServiceInterface;
use App\Contracts\TaskUpdateServiceInterface;
use App\Repositories\TaskRepository;
use App\Services\TaskAccessService;
use App\Services\TaskCreationService;
use App\Services\TaskCycleService;
use App\Services\TaskFilterService;
use App\Services\TaskService;
use App\Services\TaskUpdateService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(TaskServiceInterface::class, TaskService::class);
        $this->app->bind(TaskCreationServiceInterface::class, TaskCreationService::class);
        $this->app->bind(TaskUpdateServiceInterface::class, TaskUpdateService::class);
        $this->app->bind(TaskFilterServiceInterface::class, TaskFilterService::class);
        $this->app->bind(TaskCycleServiceInterface::class, TaskCycleService::class);
        $this->app->singleton(TaskAccessService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \App\Models\Task::observe(\App\Observers\TaskObserver::class);
    }
}
