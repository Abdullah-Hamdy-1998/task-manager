<?php

namespace App\Exceptions;

use Exception;

class TaskNotFoundException extends Exception
{
    public function __construct(int $taskId, ?string $message = null, int $code = 0, ?Exception $previous = null)
    {
        $message = $message ?: "Task with ID {$taskId} not found.";
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json([
            'error' => 'Task Not Found',
            'message' => $this->getMessage(),
        ], 404);
    }
}
