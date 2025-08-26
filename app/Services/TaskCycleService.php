<?php

namespace App\Services;

use App\Models\Task;
use Illuminate\Support\Facades\DB;
use App\Contracts\TaskCycleServiceInterface;

class TaskCycleService implements TaskCycleServiceInterface
{
    public function wouldCreateCycle(Task $task, Task $dependsOnTask): bool
    {
        if ($task->id === $dependsOnTask->id) {
            return true;
        }

        $query = <<<'SQL'
        WITH RECURSIVE dependency_chain (task_id) AS (
            -- Start from the task we want to depend on
            SELECT ? AS task_id

            UNION ALL

            -- Follow its dependencies upwards
            SELECT td.depends_on_task_id
            FROM task_dependencies td
            INNER JOIN dependency_chain dc ON td.task_id = dc.task_id
        )
        -- If we ever reach the original task, it's a cycle
        SELECT 1 
        FROM dependency_chain 
        WHERE task_id = ? 
        LIMIT 1
        SQL;

        $result = DB::select($query, [$dependsOnTask->id, $task->id]);

        return !empty($result);
    }
}
