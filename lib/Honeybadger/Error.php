<?php

namespace Honeybadger;

/**
 * Based on [Kohana's error
 * handler](https://github.com/kohana/core/blob/3.3/master/classes/Kohana/Core.php#L984:L995).
 *
 * @package  Honeybadger
 */
class Error
{
    /**
     * @var
     */
    private static $previous_handler;

    /**
     *
     */
    public static function register_handler()
    {
        self::$previous_handler = set_error_handler(
            [
                __CLASS__, 'handle',
            ]
        );
    }

    /**
     * @param      $code
     * @param      $error
     * @param null $file
     * @param null $line
     * @param null $context
     *
     * @return bool|mixed
     */
    public static function handle($code, $error, $file = null, $line = null, array $context = null)
    {
        if (error_reporting() & $code) {
            // This error is not suppressed by current error reporting settings.
            // Convert the error into an ErrorException.
            $exception = new \ErrorException($error, $code, 0, $file, $line);

            // Send the error to Honeybadger.
            Honeybadger::notifyOrIgnore($exception);
        }

        if (is_callable(self::$previous_handler)) {
            // Pass the triggered error on to the previous error handler.
            return call_user_func(
                self::$previous_handler,
                $code,
                $error,
                $file,
                $line,
                $context
            );
        }

        // Execute the PHP error handler.
        return false;
    }
} // End Error
