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

                <script>
                    $(document).ready(function() {
                        var isUpdating = false;
                        var pendingChanges = {};

                        // Show toast notification
                        function showToast(message, type = 'success') {
                            var toast = $('<div class="toast toast-' + type + '">' + message + '</div>');
                            $('body').append(toast);
                            toast.fadeIn();
                            setTimeout(function() {
                                toast.fadeOut(function() {
                                    $(this).remove();
                                });
                            }, 3000);
                        }

                        // Update permission via AJAX
                        function updatePermission(menuId, userId, permissionType, isChecked, permissionId) {
                            $.ajax({
                                url: 'update_permission.php',
                                type: 'POST',
                                data: {
                                    menu_id: menuId,
                                    user_id: userId,
                                    permission_type: permissionType,
                                    is_checked: isChecked ? 1 : 0,
                                    permission_id: permissionId
                                },
                                dataType: 'json',
                                success: function(response) {
                                    if (response.success) {
                                        // Update permission ID if it was created
                                        if (response.permission_id && permissionId == 0) {
                                            var row = $('tr[data-menu-id="' + menuId + '"][data-user-id="' + userId + '"]');
                                            row.data('permission-id', response.permission_id);
                                            row.attr('data-permission-id', response.permission_id);

                                            // Update all checkboxes in this row with the new permission ID
                                            row.find('.permission-toggle').data('permission-id', response.permission_id);
                                            row.find('.permission-toggle').attr('data-permission-id', response.permission_id);
                                        }
                                        showToast('Permission updated successfully!');
                                    } else {
                                        showToast('Error: ' + response.message, 'error');
                                        // Revert checkbox state
                                        $('.permission-toggle[data-menu-id="' + menuId + '"][data-permission="' + permissionType + '"]')
                                            .prop('checked', !isChecked);
                                    }
                                },
                                error: function() {
                                    showToast('Network error. Please try again.', 'error');
                                    // Revert checkbox state
                                    $('.permission-toggle[data-menu-id="' + menuId + '"][data-permission="' + permissionType + '"]')
                                        .prop('checked', !isChecked);
                                }
                            });
                        }

                        // Handle individual permission checkbox change
                        $('.permission-toggle').on('change', function() {
                            if (isUpdating) return;

                            var menuId = $(this).data('menu-id');
                            var userId = $(this).data('user-id');
                            var permissionType = $(this).data('permission');
                            var isChecked = $(this).is(':checked');
                            var permissionId = $(this).data('permission-id') || 0;

                            // Update immediately
                            updatePermission(menuId, userId, permissionType, isChecked, permissionId);

                            // Update "All" checkbox for this row
                            var row = $(this).closest('tr');
                            var allChecked = row.find('.permission-view').is(':checked') &&
                                row.find('.permission-add').is(':checked') &&
                                row.find('.permission-edit').is(':checked') &&
                                row.find('.permission-delete').is(':checked');
                            row.find('.permission-all').prop('checked', allChecked);
                        });

                        // Handle "All" checkbox for a row
                        $('.permission-all').on('change', function() {
                            if (isUpdating) return;

                            var menuId = $(this).data('menu-id');
                            var userId = $(this).data('user-id');
                            var isChecked = $(this).is(':checked');
                            var row = $(this).closest('tr');
                            var permissionId = row.data('permission-id') || 0;

                            // Check/uncheck all permissions in this row
                            row.find('.permission-toggle').prop('checked', isChecked);

                            // Update all permissions
                            row.find('.permission-toggle').each(function() {
                                var permissionType = $(this).data('permission');
                                updatePermission(menuId, userId, permissionType, isChecked, permissionId);
                            });
                        });

                        // Select All View
                        $('#selectAllView').on('change', function() {
                            var isChecked = $(this).is(':checked');
                            $('.permission-view').prop('checked', isChecked).trigger('change');
                            showToast('All View permissions ' + (isChecked ? 'enabled' : 'disabled'), 'info');
                        });

                        // Select All Add
                        $('#selectAllAdd').on('change', function() {
                            var isChecked = $(this).is(':checked');
                            $('.permission-add').prop('checked', isChecked).trigger('change');
                            showToast('All Add permissions ' + (isChecked ? 'enabled' : 'disabled'), 'info');
                        });

                        // Select All Edit
                        $('#selectAllEdit').on('change', function() {
                            var isChecked = $(this).is(':checked');
                            $('.permission-edit').prop('checked', isChecked).trigger('change');
                            showToast('All Edit permissions ' + (isChecked ? 'enabled' : 'disabled'), 'info');
                        });

                        // Select All Delete
                        $('#selectAllDelete').on('change', function() {
                            var isChecked = $(this).is(':checked');
                            $('.permission-delete').prop('checked', isChecked).trigger('change');
                            showToast('All Delete permissions ' + (isChecked ? 'enabled' : 'disabled'), 'info');
                        });

                        // Select All (All permissions)
                        $('#selectAllAll').on('change', function() {
                            var isChecked = $(this).is(':checked');
                            $('.permission-all, .permission-toggle').prop('checked', isChecked).trigger('change');
                            showToast('All permissions ' + (isChecked ? 'enabled' : 'disabled'), 'info');
                        });

                        // Save All button
                        $('#saveAllBtn').on('click', function() {
                            showToast('Permissions are saved automatically when you check/uncheck boxes!', 'info');
                        });

                        // Reset All button
                        $('#resetAllBtn').on('click', function() {
                            if (confirm('Are you sure you want to reset all permissions for this user?')) {
                                // Uncheck all checkboxes
                                $('.permission-toggle, .permission-all').prop('checked', false);

                                // Update database
                                $('.permission-toggle').each(function() {
                                    var menuId = $(this).data('menu-id');
                                    var userId = $(this).data('user-id');
                                    var permissionType = $(this).data('permission');
                                    var permissionId = $(this).data('permission-id') || 0;

                                    updatePermission(menuId, userId, permissionType, false, permissionId);
                                });

                                showToast('All permissions reset to disabled', 'warning');
                            }
                        });
                    });
                </script>
            <?php
            }
            ?>
        </div>
    </div>
<?php
}
?>

<script>
$(document).ready(function() {

    function showToast(message, type = 'success') {
        alert(message); // Replace with your custom toast UI if needed
    }

    function updatePermission(menuId, userId, permissionType, isChecked, permissionId) {
        $.ajax({
            url: "update_permission.php",
            type: "POST",
            data: {
                menu_id: menuId,
                user_id: userId,
                permission_type: permissionType,
                is_checked: isChecked ? 1 : 0,
                permission_id: permissionId
            },
            dataType: "json",
            success: function(res) {
                if(res.success){
                    if(res.permission_id && permissionId == 0) {
                        let row = $('tr[data-menu-id="'+menuId+'"]');
                        row.attr('data-permission-id', res.permission_id);
                        row.find('.permission-toggle').attr('data-permission-id', res.permission_id);
                    }
                }
                else{
                    showToast(res.message, 'error');
                }
            },
            error: function(){
                showToast("Error updating permission!", 'error');
            }
        });
    }

    function updateAllCheck(menuId) {
        let row = $('tr[data-menu-id="'+menuId+'"]');
        let allChecked = row.find('.permission-view').is(':checked') &&
                         row.find('.permission-add').is(':checked') &&
                         row.find('.permission-edit').is(':checked') &&
                         row.find('.permission-delete').is(':checked');

        row.find('.permission-all').prop('checked', allChecked);
    }

    $(".permission-toggle").change(function(){
        let row = $(this).closest("tr");
        let menuId = row.data("menu-id");
        let userId = row.data("user-id");
        let permissionId = row.data("permission-id") || 0;
        let permissionType = $(this).data("permission");
        let isChecked = $(this).is(':checked');

        updatePermission(menuId, userId, permissionType, isChecked, permissionId);
        updateAllCheck(menuId);
    });

    $(".permission-all").change(function(){
        let row = $(this).closest("tr");
        let menuId = row.data("menu-id");
        let userId = row.data("user-id");
        let isChecked = $(this).is(':checked');
        let permissionId = row.data("permission-id") || 0;

        row.find(".permission-toggle").each(function(){
            $(this).prop("checked", isChecked);
            updatePermission(menuId, userId, $(this).data("permission"), isChecked, permissionId);
        });
    });

    $("#selectAllView,#selectAllAdd,#selectAllEdit,#selectAllDelete,#selectAllAll").change(function(){
        let action = $(this).attr("id");
        let isChecked = $(this).is(":checked");

        switch(action){
            case "selectAllView":
                $(".permission-view").prop("checked", isChecked).trigger("change");
                break;
            case "selectAllAdd":
                $(".permission-add").prop("checked", isChecked).trigger("change");
                break;
            case "selectAllEdit":
                $(".permission-edit").prop("checked", isChecked).trigger("change");
                break;
            case "selectAllDelete":
                $(".permission-delete").prop("checked", isChecked).trigger("change");
                break;
            case "selectAllAll":
                $(".permission-all,.permission-toggle").prop("checked", isChecked).trigger("change");
                break;
        }
    });

});

</script>