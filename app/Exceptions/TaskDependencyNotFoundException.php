<?php

namespace App\Exceptions;

use Exception;

class TaskDependencyNotFoundException extends Exception
{
    public function __construct(int $dependencyId, ?string $message = null, int $code = 0, ?Exception $previous = null)
    {
        $message = $message ?: "Task dependency with ID {$dependencyId} not found.";
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json([
            'error' => 'Task Dependency Not Found',
            'message' => $this->getMessage()
        ], 404);
    }
}