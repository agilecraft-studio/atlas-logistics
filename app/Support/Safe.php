<?php

namespace App\Support;

class Safe
{
    /**
     * Unserialize stored legacy payloads without letting null/empty/corrupt
     * values escalate to a 500 (warnings are converted to exceptions by the
     * framework error handler).
     */
    public static function unserialize(mixed $value, mixed $default = null): mixed
    {
        if (!is_string($value) || $value === '') {
            return $default;
        }

        set_error_handler(static fn (): bool => true);

        try {
            $result = unserialize($value);
        } catch (\Throwable) {
            $result = false;
        } finally {
            restore_error_handler();
        }

        return ($result === false && $value !== 'b:0;') ? $default : $result;
    }
}
