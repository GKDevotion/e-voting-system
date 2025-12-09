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