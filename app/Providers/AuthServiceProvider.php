<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        \App\Models\Task::class => \App\Policies\TaskPolicy::class,
        // Additional policies can be added here
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}