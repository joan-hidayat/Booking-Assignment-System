<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . "/path/to/RBACModel.php";

$rbacModel = new RBACModel($GLOBALS['conn']);
$userRole  = $_SESSION['user']['role_id'] ?? null;

if ($userRole) {
    $_SESSION['permissions'] = $rbacModel->getPermissionsByRole($userRole);
    $_SESSION['permission_map'] = $rbacModel->getAllPermissions();
    echo json_encode(["status" => "updated"]);
} else {
    echo json_encode(["status" => "no_role"]);
}
