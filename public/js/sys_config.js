$("#saveuser").click(function() {
    $.post('sys_config/set', {
        user: formArrayToObject($("#user").serializeArray())
    }, function(data) {
        swal.fire(data.swal)
    }, 'json');
});