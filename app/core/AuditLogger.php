<?php
require_once dirname(__DIR__) . '/models/SystemLogModel.php';

class AuditLogger
{
    private const DATA_CHANGE_ACTIONS = [
        'CREATE',
        'ADD',
        'INSERT',
        'STORE',
        'UPDATE',
        'EDIT',
        'PATCH',
        'TRANSFER',
        'TRANSFERRED',
        'DELETE',
        'REMOVE',
        'DESTROY',
    ];

    private const SYSTEM_ACTIONS = [
        'LOGIN',
        'LOGOUT',
        'LOGIN_FAILED',
        'FAILED_LOGIN',
        'PASSWORD_RESET',
        'LOCK',
        'UNLOCK',
        'SYSTEM',
        'SECURITY',
        'CONFIG',
        'BACKUP',
        'RESTORE',
        'IMPORT',
        'EXPORT',
        'MIGRATION',
        'SEED',
    ];

    public static function log($user_id, $action_type, $module, $description)
    {
        if (!self::shouldLogAction($action_type, $module)) {
            return false;
        }

        $logModel = new SystemLogModel();
        return $logModel->log($user_id, $action_type, $module, $description);
    }

    public static function shouldLogAction($action_type, $module = '')
    {
        $action = strtoupper(trim((string) $action_type));
        if ($action === '') {
            return false;
        }

        if (in_array($action, self::DATA_CHANGE_ACTIONS, true) || in_array($action, self::SYSTEM_ACTIONS, true)) {
            return true;
        }

        return preg_match('/(^|_)(CREATE|CREATED|ADD|ADDED|INSERT|UPDATE|UPDATED|EDIT|DELETE|DELETED|REMOVE|REMOVED)(_|$)/', $action) === 1;
    }
}
?>
