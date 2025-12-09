<?php
if ($userid > 0) {
    // Get user details
    $user_sql = "SELECT username FROM admin WHERE id = " . intval($userid);
    $user_result = $conn->query($user_sql);

    if (!$user_result || $user_result->num_rows == 0) {
        echo "<div class='alert alert-danger'>
                <i class='fa fa-exclamation-triangle'></i> User not found!
              </div>";
        return;
    }

    $user_data = $user_result->fetch_assoc();
    $username = htmlspecialchars($user_data['username']);
?>

    <div class="box box-success">
        <div class="box-body">
            <?php
            // Function to get all unique menus from permissions table with menu details
            function getAllMenusFromPermissions($conn, $user_id)
            {
                $sql = "SELECT DISTINCT p.admin_menu_id as id, am.name, am.icon
                        FROM permissions p 
                        LEFT JOIN admin_menus am ON p.admin_menu_id = am.id
                        WHERE p.admin_user_id = " . intval($user_id);
                $result = $conn->query($sql);

                $menus = [];
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $menus[] = $row;
                    }
                }
                return $menus;
            }

            // Function to get user permissions with all details
            function getUserPermissions($conn, $user_id)
            {
                $sql = "SELECT p.* 
                        FROM permissions p 
                        WHERE p.admin_user_id = " . intval($user_id);
                $result = $conn->query($sql);

                $permissions = [];
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $menu_id = isset($row['admin_menu_id']) ? $row['admin_menu_id'] : 0;
                        $permissions[$menu_id] = $row;
                    }
                }
                return $permissions;
            }

            // Function to get menu name by ID from admin_menus table
            function getMenuNameById($conn, $menu_id)
            {
                $sql = "SELECT am.name, am.icon
                        FROM admin_menus am
                        WHERE am.id = " . intval($menu_id);
                $result = $conn->query($sql);

                if ($result && $result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    return $row;
                }
                return ['name' => 'Menu ' . $menu_id, 'icon' => 'fa-folder'];
            }

            // Function to display menu rows
            function displayMenuRow($menu, $userPermissions, $userid, $conn)
            {
                $menu_id = $menu['id'];
                $menu_name = htmlspecialchars($menu['name']);
                $menu_icon = !empty($menu['icon']) ? $menu['icon'] : 'fa-folder';

                // Get permission for this menu
                $permission = isset($userPermissions[$menu_id]) ? $userPermissions[$menu_id] : null;
                $permission_id = $permission ? $permission['id'] : 0;

                // Determine initial permission values
                $view_checked = ($permission && $permission['permission_view'] == 1) ? 'checked' : '';
                $add_checked = ($permission && $permission['permission_add'] == 1) ? 'checked' : '';
                $edit_checked = ($permission && $permission['permission_edit'] == 1) ? 'checked' : '';
                $delete_checked = ($permission && $permission['permission_delete'] == 1) ? 'checked' : '';
                $all_checked = ($view_checked && $add_checked && $edit_checked && $delete_checked) ? 'checked' : '';
            ?>

                <tr data-menu-id="<?php echo $menu_id; ?>"
                    data-user-id="<?php echo $userid; ?>"
                    data-permission-id="<?php echo $permission_id; ?>">
                    <td>
                        <div class="menu-item">
                            <i class="fa <?php echo $menu_icon; ?>"></i>
                            <span class="menu-name"><?php echo $menu_name; ?></span>
                            <small class="text-muted">(ID: <?php echo $menu_id; ?>)</small>
                        </div>
                    </td>

                    <!-- View Permission -->
                    <td class="text-center">
                        <input type="checkbox"
                            class="permission-toggle permission-view"
                            data-permission="view"
                            data-menu-id="<?php echo $menu_id; ?>"
                            data-user-id="<?php echo $userid; ?>"
                            data-permission-id="<?php echo $permission_id; ?>"
                            <?php echo $view_checked; ?>>
                    </td>

                    <!-- Add Permission -->
                    <td class="text-center">
                        <input type="checkbox"
                            class="permission-toggle permission-add"
                            data-permission="add"
                            data-menu-id="<?php echo $menu_id; ?>"
                            data-user-id="<?php echo $userid; ?>"
                            data-permission-id="<?php echo $permission_id; ?>"
                            <?php echo $add_checked; ?>>
                    </td>

                    <!-- Edit Permission -->
                    <td class="text-center">
                        <input type="checkbox"
                            class="permission-toggle permission-edit"
                            data-permission="edit"
                            data-menu-id="<?php echo $menu_id; ?>"
                            data-user-id="<?php echo $userid; ?>"
                            data-permission-id="<?php echo $permission_id; ?>"
                            <?php echo $edit_checked; ?>>
                    </td>

                    <!-- Delete Permission -->
                    <td class="text-center">
                        <input type="checkbox"
                            class="permission-toggle permission-delete"
                            data-permission="delete"
                            data-menu-id="<?php echo $menu_id; ?>"
                            data-user-id="<?php echo $userid; ?>"
                            data-permission-id="<?php echo $permission_id; ?>"
                            <?php echo $delete_checked; ?>>
                    </td>

                    <!-- All Permissions -->
                    <td class="text-center">
                        <input type="checkbox"
                            class="permission-all"
                            data-menu-id="<?php echo $menu_id; ?>"
                            data-user-id="<?php echo $userid; ?>"
                            <?php echo $all_checked; ?>>
                    </td>
                </tr>

            <?php
            }

            // Get data - fetch all menus from permissions table joined with admin_menus
            $allMenus = getAllMenusFromPermissions($conn, $userid);
            $userPermissions = getUserPermissions($conn, $userid);

            if (empty($allMenus)) {
                echo '<div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> No menus found for this user.
                      </div>';
            } else {
            ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="permissionsTable">
                        <thead class="thead-dark">
                            <tr>
                                <th width="30%">Menu Name</th>
                                <th class="text-center" width="14%">View</th>
                                <th class="text-center" width="14%"> Add</th>
                                <th class="text-center" width="14%"> Edit</th>

                                <th class="text-center" width="14%"> Delete</th>
                                <th class="text-center" width="14%">All</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($allMenus as $menu) {
                                displayMenuRow($menu, $userPermissions, $userid, $conn);
                            }
                            ?>
                        </tbody>
                    </table>
                </div>



                <style>
                    .indent {
                        display: inline-block;
                        width: 20px;
                    }

                    .menu-item {
                        padding: 5px 0;
                    }

                    .menu-name {
                        font-weight: 600;
                        color: #333;
                    }

                    .permission-toggle {
                        width: 20px;
                        height: 20px;
                        cursor: pointer;
                    }

                    .permission-all {
                        width: 20px;
                        height: 20px;
                        cursor: pointer;
                    }

                    #permissionsTable thead th {
                        vertical-align: middle;
                        text-align: center;
                    }

                    #permissionsTable thead th input[type="checkbox"] {
                        margin-top: 5px;
                    }
                </style>

            <?php
            }
            ?>
        </div>
    </div>
<?php
}
?>
