<?php

namespace App\Exceptions;

use Exception;

class TaskDuplicateDependencyException extends Exception
{
    public function __construct(?string $message = null, int $code = 0, ?Exception $previous = null)
    {
        $message = $message ?: 'Dependency already exists.';
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json(['error' => $this->getMessage()], 400);
    }
}
