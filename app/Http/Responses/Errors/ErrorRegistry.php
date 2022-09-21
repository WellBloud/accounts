<?php

namespace App\Http\Responses\Errors;

class ErrorRegistry
{
    public const GENERAL_ERROR = 1;
    public const INVALID_QUERY = 2;
    public const UNAUTHENTICATED = 3;
    public const SERVER_ERROR = 4;
    public const NOT_FOUND = 5;
    public const MODEL_NOT_FOUND = 6;
    public const NOT_SUPPORTED = 7;
    public const REQUEST_FAILED = 8;

    private const MESSAGES = [
        self::GENERAL_ERROR => 'General error',
        self::NOT_FOUND => 'Referenced object not found',
        self::MODEL_NOT_FOUND => 'Model not found',
        self::INVALID_QUERY => 'Unable to process request',
        self::UNAUTHENTICATED => 'Could not authenticate you',
        self::SERVER_ERROR => 'Server error',
        self::NOT_SUPPORTED => 'That action is not supported',
        self::REQUEST_FAILED => 'The request failed to process',
    ];

    public static function getMessage(int $code): string
    {
        return self::MESSAGES[$code] ?? self::MESSAGES[self::GENERAL_ERROR];
    }
}
