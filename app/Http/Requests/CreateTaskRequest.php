<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTaskRequest extends formRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Task::class);
    }

    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignee_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'status' => 'required|in:pending,completed,canceled',
        ];
    }
}