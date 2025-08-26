<?php

namespace App\Exceptions;

use Exception;

class TaskIncompleteDependenciesException extends Exception
{
    public function __construct(?string $message = null, int $code = 0, ?Exception $previous = null)
    {
        $message = $message ?: 'Cannot mark task as completed until all dependencies are completed.';
        parent::__construct($message, $code, $previous);
    }

    public function render($request)
    {
        return response()->json([
            'error' => 'Incomplete Dependencies',
            'message' => $this->getMessage(),
        ], 422);
    }
}
