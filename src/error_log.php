<?php
function logError($error, $type = 'ERROR') {
    $logFile = __DIR__ . '/logs/error.log';
    $logDir = dirname($logFile);
    
    if (!file_exists($logDir)) {
        mkdir($logDir, 0755, true);
    }
    
    $timestamp = date('Y-m-d H:i:s');
    $message = "[$timestamp] [$type] $error\n";
    file_put_contents($logFile, $message, FILE_APPEND);
}
