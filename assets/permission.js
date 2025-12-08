// assets/permission.js
$(document).ready(function(){
  // helper to show/hide loader (implement as needed)
  function showLoader() { /*...*/ }
  function hideLoader() { /*...*/ }

  function updatePermission(val, type, cls) {
    // val is string like "permId|userId|menuId|checked||permId2|user2|menu2|checked"
    $.ajax({
      url: 'change_permission.php',
      method: 'POST',
      data: { val: val, type: type, cls: cls },
      dataType: 'json',
      beforeSend: function(){ showLoader(); }
    }).done(function(resp){
      if (resp.type === 'success') {
        // optionally show toast
        console.log(resp.msg);
      } else {
        console.error(resp.msg);
      }
    }).fail(function(jqXHR){
      console.error('AJAX error', jqXHR);
    }).always(function(){ hideLoader(); });
  }

  $("input[type=checkbox]").on("change", function(){
     var name = $(this).attr('name');
     var checked = ($(this).is(":checked")) ? 1 : 0;
     var val = '';

     if(name == "viewall" || name == "addall" || name == "editall" || name == "deleteall" ) {
       // For class-based bulk toggles: find checkboxes that share class + data
       var cls = $(this).attr('class').split(' ')[0];
       $('.' + cls + '.' + $(this).attr('data-')).each(function(){
          $(this).prop('checked', checked);
          val += $(this).val() + '|' + checked + '||';
       });
       updatePermission(val.slice(0, -2), name, $(this).attr('class'));
     }
     else if(name == "allall") {
       $('.' + $(this).attr('class')).prop('checked', checked);
       $('.' + $(this).attr('class') + '-tr').each(function(){
          val += $(this).attr('data-') + '|' + checked + '||';
       });
       updatePermission(val.slice(0, -2), name, $(this).attr('class'));
     }
     else if(name == "all") {
       var obj = $(this).parents('tr');
       $(obj).find('td').each(function(){
         $(this).find('input:checkbox').prop('checked', checked);
       });
       val = $(obj).attr('data-') + '|' + checked;
       var cls = $(this).attr('class');
       updatePermission(val, name, cls.substring(0, cls.indexOf(" ",0)));
     }
     else {
       var cls = $(this).attr('class');
       updatePermission($(this).val() + '|' + checked, name, cls.substring(0, cls.indexOf(" ",0)));
     }
     return false;
  });
});
