<?php
include 'config.php'; // database connection file

header('Content-Type: application/json');

$menu_id = intval($_POST['menu_id'] ?? 0);
$user_id = intval($_POST['user_id'] ?? 0);
$permission_type = $_POST['permission_type'] ?? '';
$is_checked = intval($_POST['is_checked'] ?? 0);
$permission_id = intval($_POST['permission_id'] ?? 0);

if (!$menu_id || !$user_id || !$permission_type) {
    echo json_encode(["success" => false, "message" => "Invalid request"]);
    exit;
}

// Map permission type to column name
$columnMap = [
    'view' => 'permission_view',
    'add'  => 'permission_add',
    'edit' => 'permission_edit',
    'delete' => 'permission_delete'
];

if (!isset($columnMap[$permission_type])) {
    echo json_encode(["success" => false, "message" => "Invalid permission type"]);
    exit;
}

$column = $columnMap[$permission_type];

try {
    if ($permission_id > 0) {
        // UPDATE existing record
        $stmt = $conn->prepare("UPDATE permissions SET $column = ? WHERE id = ?");
        $stmt->bind_param("ii", $is_checked, $permission_id);
        $stmt->execute();
    } else {
        // INSERT new permission row
        $stmt = $conn->prepare("INSERT INTO permissions (admin_menu_id, admin_user_id, $column, created_at) 
                               VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("iii", $menu_id, $user_id, $is_checked);
        $stmt->execute();
        $permission_id = $conn->insert_id;
    }

    echo json_encode([
        "success" => true,
        "permission_id" => $permission_id,
        "message" => "Permission updated"
    ]);
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
exit;
?>
