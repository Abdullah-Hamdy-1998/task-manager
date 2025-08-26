<?php

namespace App\Services;

use App\Contracts\TaskFilterServiceInterface;
use Illuminate\Database\Eloquent\Builder;

class TaskFilterService implements TaskFilterServiceInterface
{
    public function applyFilters(Builder $query, array $filters): Builder
    {
        if (isset($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        if (isset($filters['due_from']) || isset($filters['due_to'])) {
            $query->byDueDateRange(
                $filters['due_from'] ?? null,
                $filters['due_to'] ?? null
            );
        }

        if (isset($filters['assignee_id'])) {
            $query->byAssignee($filters['assignee_id']);
        }

        return $query;
    }
}
