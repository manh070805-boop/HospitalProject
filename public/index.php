<?php
if (session_status() === PHP_SESSION_NONE) {
    $defaultSessionPath = session_save_path();
    $canUseDefaultSessionPath = $defaultSessionPath !== '' && is_dir($defaultSessionPath) && is_writable($defaultSessionPath);

    if (!$canUseDefaultSessionPath) {
        $projectSessionPath = dirname(__DIR__) . '/storage/sessions';
        if (!is_dir($projectSessionPath)) {
            mkdir($projectSessionPath, 0777, true);
        }

        if (is_dir($projectSessionPath) && is_writable($projectSessionPath)) {
            session_save_path($projectSessionPath);
        }
    }
}

session_start();
header('Content-Type: text/html; charset=UTF-8');

require_once __DIR__ . '/../routes/web.php';
?>
