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

$("#initdb").click(function() {
    swal.fire({
        html: loading_layer_txt,
        showConfirmButton: false
    });
    $.post('sys_config/initdb', {
        db: formArrayToObject($("#form-db").serializeArray())
    }, function(data) {
        swal.fire(data.swal)
    }, 'json');
});

/**
 * PELIIIGRO CARLITOOOOS
 * ATAAAANGANA!!!!!

                             \         .  ./
                           \      .:";'.:.."   /
                               (M^^.^~~:.'").
                         -   (/  .    . . \ \)  -
  O                         ((| :. ~ ^  :. .|))
 |\\                     -   (\- |  \ /  |  /)  -
 |  T                         -\  \     /  /-
/ \[_]..........................\  \   /  /

 */
const word = 'restablecer'
$("#wipedb").click(function() {

    swal.fire({
		title: "Vaciar base de datos",
		html: `¿Está realmente seguro?<br><span>Escriba la palabra mágica: </span><input type="text">`,
		type: "info",
		showCloseButton: true,
        showCancelButton: true,
        showConfirmButton: true,
		confirmButtonText: 'Proceder',
		cancelButtonText: 'Cancelar'
	}).then(function(res) {
        if (res.value) {
            swal.fire({
                html: loading_layer_txt,
                showConfirmButton: false
            });
            $.post('sys_config/wipedb', {
                db: formArrayToObject($("#form-db").serializeArray())
            }, function(data) {
                swal.fire({
                    type: 'success',
                    title: 'Excelsior',
                    html: data
                })
            });
        }
    }).catch(swal.noop);

    let jqswalcbtn = $("body > div.swal2-container.swal2-center.swal2-fade.swal2-shown > div > div.swal2-actions > button.swal2-confirm.swal2-styled");  

    $("#swal2-content > input[type=text]").keyup(function() {
        if ($(this).val() === word) {
            jqswalcbtn.show().focus();
        } else {
            jqswalcbtn.hide();
        }
    }).keyup()
    
    
    
});


$(document).on('click', '.wipe-toggle', function() {
    $(this).closest('table').find('tbody').toggle()
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