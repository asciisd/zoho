<?php

namespace Asciisd\Zoho\Exceptions;

use Exception;

class ZohoException extends Exception
{
    /**
     * Create a new Zoho exception instance.
     */
    public function __construct(string $message = '', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Report the exception.
     */
    public function report(): void
    {
        //
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render()
    {
        return response()->json([
            'error' => true,
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
        ], 500);
    }
}
