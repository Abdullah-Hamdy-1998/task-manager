<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the seeded users (manager and user)
        $manager = User::find(1); // Manager user
        $user = User::find(2);    // Regular user

        // Create tasks with different statuses and due dates for testing filters

        // Pending tasks assigned to user
        Task::factory()->create([
            'title' => 'Setup Development Environment',
            'description' => 'Install and configure all necessary development tools and dependencies.',
            'status' => 'pending',
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'created_by' => $manager->id,
            'assignee_id' => $user->id,
        ]);

        Task::factory()->create([
            'title' => 'Design Database Schema',
            'description' => 'Create comprehensive database schema for the application.',
            'status' => 'pending',
            'due_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
            'created_by' => $manager->id,
            'assignee_id' => $user->id,
        ]);

        // Completed tasks
        Task::factory()->create([
            'title' => 'Project Planning Meeting',
            'description' => 'Initial project planning and requirement gathering session.',
            'status' => 'completed',
            'due_date' => Carbon::now()->subDays(5)->format('Y-m-d'),
            'created_by' => $manager->id,
            'assignee_id' => $user->id,
        ]);

        Task::factory()->create([
            'title' => 'Code Review Guidelines',
            'description' => 'Establish code review process and guidelines for the team.',
            'status' => 'completed',
            'due_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
            'created_by' => $manager->id,
            'assignee_id' => $manager->id,
        ]);

        // Canceled task
        Task::factory()->create([
            'title' => 'Legacy System Migration',
            'description' => 'Migrate data from legacy system to new platform.',
            'status' => 'canceled',
            'due_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
            'created_by' => $manager->id,
            'assignee_id' => $user->id,
        ]);

        // Tasks with different due date ranges
        Task::factory()->create([
            'title' => 'API Documentation',
            'description' => 'Create comprehensive API documentation with examples.',
            'status' => 'pending',
            'due_date' => Carbon::now()->addDays(21)->format('Y-m-d'),
            'created_by' => $manager->id,
            'assignee_id' => $user->id,
        ]);

        Task::factory()->create([
            'title' => 'Security Audit',
            'description' => 'Perform comprehensive security audit of the application.',
            'status' => 'pending',
            'due_date' => Carbon::now()->addDays(45)->format('Y-m-d'),
            'created_by' => $manager->id,
            'assignee_id' => $manager->id,
        ]);

        // Overdue task
        Task::factory()->create([
            'title' => 'Bug Fix - Login Issue',
            'description' => 'Fix critical login authentication bug reported by users.',
            'status' => 'pending',
            'due_date' => Carbon::now()->subDays(3)->format('Y-m-d'),
            'created_by' => $manager->id,
            'assignee_id' => $user->id,
        ]);

        // Task assigned to manager
        Task::factory()->create([
            'title' => 'Performance Optimization',
            'description' => 'Optimize application performance and database queries.',
            'status' => 'pending',
            'due_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
            'created_by' => $manager->id,
            'assignee_id' => $manager->id,
        ]);

        // Additional tasks for pagination testing
        Task::factory()->create([
            'title' => 'Unit Test Coverage',
            'description' => 'Increase unit test coverage to 90% or higher.',
            'status' => 'pending',
            'due_date' => Carbon::now()->addDays(28)->format('Y-m-d'),
            'created_by' => $manager->id,
            'assignee_id' => $user->id,
        ]);

        // Create additional random tasks for more comprehensive testing
        Task::factory(15)->create([
            'created_by' => $manager->id,
            'assignee_id' => function () use ($manager, $user) {
                return fake()->randomElement([$manager->id, $user->id]);
            },
        ]);
    }
}
