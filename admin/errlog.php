<?php
/**
 * Show recent PHP error log entries — DELETE after use.
 */
define('ADMIN_PAGE', true);
require_once '../includes/config.php';
require_once '../includes/functions.php';
requireAdmin();

header('Content-Type: text/plain; charset=utf-8');

$logFile = '/home/u149904157/.logs/error_log_herciocampos_com';

if (!file_exists($logFile)) {
    echo "Log file not found at: $logFile\n";
    echo "\nPHP error_log setting: " . ini_get('error_log') . "\n";
    exit;
}

// Show last 80 lines
$lines = file($logFile);
$last  = array_slice($lines, -80);
echo implode('', $last);
