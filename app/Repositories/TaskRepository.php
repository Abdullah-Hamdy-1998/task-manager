<?php

namespace App\Repositories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Model;

class TaskRepository
{
    public function create(array $data): Model
    {
        return Task::create($data);
    }
}