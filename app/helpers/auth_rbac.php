<?php
require_once __DIR__ . '/../helpers/app_helper.php';

/**
 * Cek apakah user memiliki permission tertentu
 * @param string $permission_key
 * @return void
 * Jika tidak ada izin, akan redirect ke /403.php
 */
function requirePermission(string $permission_key): void
{
    $logFile = $GLOBALS['logFile'] ?? __DIR__ . '/../logs/auth.log';

    try {
        logWithTimestamp("=== RBAC CHECK START: {$permission_key} ===", $logFile);

        // pastikan user login
        if (!isset($_SESSION['user_id']) || !$_SESSION['is_logged_in']) {
            header("Location: /login.php");
            exit;
        }

        $permissions   = $_SESSION['permissions'] ?? [];
        $permissionMap = $_SESSION['permission_map'] ?? []; // ['permission_key' => id]

        // permission key valid?
        if (!isset($permissionMap[$permission_key])) {
            insertLogHelper($GLOBALS['conn'] ?? null, [
                'user_id'     => $_SESSION['user_id'] ?? 0,
                'action'      => 'FAILED ACCESS',
                'table_name'  => 'permissions',
                'record_id'   => 0,
                'description' => "Permission key '{$permission_key}' tidak ditemukan",
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            ]);
            header("Location: /403.php");
            exit;
        }

        $permId = $permissionMap[$permission_key];

        // cek apakah user memiliki permission tersebut
        if (!in_array($permId, $permissions)) {
            insertLogHelper($GLOBALS['conn'] ?? null, [
                'user_id'     => $_SESSION['user_id'] ?? 0,
                'action'      => 'FAILED ACCESS',
                'table_name'  => 'permissions',
                'record_id'   => $permId,
                'description' => "User ID {$_SESSION['user_id']} mencoba mengakses '{$permission_key}' tanpa izin",
                'ip_address'  => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
            ]);
            header("Location: /403.php");
            exit;
        }

        logWithTimestamp("=== RBAC CHECK SUCCESS: {$permission_key} ===", $logFile);
    } catch (Throwable $e) {
        logWithTimestamp("❌ RBAC ERROR: " . $e->getMessage(), $logFile);
        header("Location: /403.php");
        exit;
    } finally {
        logWithTimestamp("=== RBAC CHECK END: {$permission_key} ===", $logFile);
    }
}
