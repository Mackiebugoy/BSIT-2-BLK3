<?php
class Logger {
    public static function log($message) {
        $logFile = __DIR__ . '/server.log';
        $timestamp = date('Y-m-d H:i:s');
        file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
    }
}

// Example usage:
Logger::log('PHP Server initialized.');
?>
