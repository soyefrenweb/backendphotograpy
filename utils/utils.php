<?php
    function log_message(string $message, string $level = 'INFO'): void {
        $log_dir = __DIR__ . '/../logs/';
        $log_file = $log_dir . 'app.log';

        if (!is_dir($log_dir)) {
            mkdir($log_dir, 0777, true); 
        }

        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[{$timestamp}] [{$level}] {$message}\n";

        file_put_contents($log_file, $log_entry, FILE_APPEND);
    }
?>