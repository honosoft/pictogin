<?php

namespace pictogin;

// TODO: probably use something like monolog.
class SimpleLogger {
    public static function log($message) {
        if (getenv('DOCKER')) {
            fwrite(fopen('php://stdout', 'w'),  "$message\r\n");
        }
    }

    public static function error($message) {
        if (getenv('DOCKER')) {
            error_log($message);
        }
    }
}