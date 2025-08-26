<?php

namespace App\Exceptions;

use Exception;

class TaskAlreadyCompletedException extends Exception
{
    public function __construct(?string $message = null, int $code = 0, ?Exception $previous = null)
    {
        $message = $message ?: 'Cannot add dependency to a completed task.';
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json(['error' => $this->getMessage()], 400);
    }
}
