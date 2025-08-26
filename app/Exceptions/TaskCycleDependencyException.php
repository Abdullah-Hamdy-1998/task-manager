<?php

namespace App\Exceptions;

use Exception;

class TaskCycleDependencyException extends Exception
{
    public function __construct(?string $message = null, int $code = 0, ?Exception $previous = null)
    {
        $message = $message ?: 'Cycle detected in task dependencies. A task cannot depend on itself or create a circular dependency.';
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json([
            'error' => 'Cycle Dependency Detected',
            'message' => $this->getMessage(),
        ], 422);
    }
}
