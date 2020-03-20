//Admin
$("#saveuser").click(function() {
    $.post('sys_config/setuser', {
        user: formArrayToObject($("#form-user").serializeArray())
    }, function(data) {
        swal.fire(data.swal)
    }, 'json');
});

//DB
$("#savedb").click(function() {
    $.post('sys_config/setdb', {
        db: formArrayToObject($("#form-db").serializeArray())
    }, function(data) {
        swal.fire(data.swal)
    }, 'json');
});

$("#testdb").click(function() {
    $.post('sys_config/testdb', {
        db: formArrayToObject($("#form-db").serializeArray())
    }, function(data) {
        swal.fire(data.swal)
    }, 'json');
});

//Login service
$("#savelogin").click(function() {
    $.post('sys_config/setdb', {
        db: formArrayToObject($("#form-login").serializeArray())
    }, function(data) {
        swal.fire(data.swal)
    }, 'json');
});

$("#testlogin").click(function() {
    $.post('sys_config/testlogin', {
        db: formArrayToObject($("#form-login").serializeArray())
    }, function(data) {
        swal.fire(data.swal)
    }, 'json');
});