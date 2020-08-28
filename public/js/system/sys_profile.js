var path = 'sys_profile';
//Admin
$(document).ready(function () {
    $("#username").keyup(function() {
        validarUsuario();
    });
    $("#telefono").keyup(function() {
        validarTelefono();
    });
});

$("#saveuser").click(function() {
    if (validateForm("#form-user")) {
        swal.fire({
            html: loading_layer_txt,
            showConfirmButton: false
        });
        $.post(`${path}/setuser`, {
            user: formArrayToObject($("#form-user").serializeArray())
        }, function(data) {
            swal.fire(data)
        }, 'json');
    }
});

//DB
$("#savelogin").click(function() {
    if (validateForm("#form-login")) {
        if($("#rep_password").val().trim().replace(/['"]+/g, '') !== $("#password").val().trim().replace(/['"]+/g, '')){
            swal.fire({
                title : "Formulario Incompleto",
                type: 'error',
                text: 'Las contraseñas no coinciden'
            });
        }else{
            swal.fire({
                html: loading_layer_txt,
                showConfirmButton: false
            });
            $.post(`${path}/setlogin`, {
                user: formArrayToObject($("#form-login").serializeArray())
            }, function(data) {
                swal.fire(data)
            }, 'json');
        }
    }
});

var validarTelefono = () => {
    var regex = /[^+\d]/g;
    $("#telefono").val($("#telefono").val().replace(regex, ""));  
}

var validarUsuario = () => {
    var regex = /[^a-z0-9._@]/;
    $("#username").val($("#username").val().replace(regex, ""));   
}