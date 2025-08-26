<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskShowResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'due_date' => $this->due_date ? $this->due_date->format('Y-m-d') : null,
            'created_by' => $this->created_by,
            'assignee_id' => $this->assignee_id,
            'assignee' => $this->whenLoaded('assignee', function () {
                return [
                    'id' => $this->assignee->id,
                    'name' => $this->assignee->name,
                ];
            }),
            'dependencies' => TaskResource::collection($this->whenLoaded('dependencies')),
        ];
    }
}