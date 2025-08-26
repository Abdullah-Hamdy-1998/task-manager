<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface TaskFilterServiceInterface
{
    public function applyFilters(Builder $query, array $filters): Builder;
}