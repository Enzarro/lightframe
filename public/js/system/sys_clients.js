var dTable, selected;
var path = 'sys_clients';
var primary = 'client_id';
var table = 'mantenedores';

//Grid
$.post(`${path}/list`, {
    config: true
}, function(columnDefs) {
    //Load DataTables
    dTable = $(`#${table}`).DataTable({
        autoWidth: false,
        serverSide: true,
        ajax: {
            url: `${path}/list`,
            type: 'POST',
            data: {
                
            }
        },
        columnDefs: columnDefs,
        select: {
            style: 'multi',
            selector: 'td:last-child'
        },
        order: [
            [ 0, "asc" ]
        ],
        language: dtSpanish
    });
}, "json");

socket.on('sys_clients:update-bar', function(data) {
    //Clients bar
    $("#clients-bar").find("span").html(`${data.bar_clients.name} (${data.bar_clients.current}/${data.bar_clients.max})`);
    $("#clients-bar").find(".progress-bar").css('width', `${100/data.bar_clients.max*data.bar_clients.current}%`)
    $("#clients-bar").find(".progress-bar").attr('aria-valuenow', data.bar_clients.current);
    $("#clients-bar").find(".progress-bar").attr('aria-valuemax', data.bar_clients.max);
    //Clients bar
    $("#grids-bar").find("span").html(`${data.bar_grids.name} (${data.bar_grids.current}/${data.bar_grids.max})`);
    $("#grids-bar").find(".progress-bar").css('width', `${100/data.bar_grids.max*data.bar_grids.current}%`)
    $("#grids-bar").find(".progress-bar").attr('aria-valuenow', data.bar_grids.current);
    $("#grids-bar").find(".progress-bar").attr('aria-valuemax', data.bar_grids.max);
});

//Form
$(document).on("click", "#main-new,.main-edit", function() {
    selected = undefined;
    if ($(this).hasClass("main-edit")) {
        selected = dTable.row($(this).closest('tr')).data()[primary];
    }
    $.post(`${path}/form`, {
        id: selected
    }, function(data) {
        $("#modal-default .modal-body").html(data);
        InitFormFields("#modal-default .modal-body");
        $("#modal-default").modal("show");
    });
});

//Consolidate
$(document).on('click', '.main-consolidate', function() {
    selected = dTable.row($(this).closest('tr')).data()[primary];
    $.post(`${path}/consolidate`, {
        id: selected
    }, function(data) {
        swal.fire(data);
        dTable.ajax.reload(null, false);
    }, 'json');
});

//Consolidate all
$('#main-consolidate').click(function() {
    swal.fire({
        html: loading_layer_txt,
        showConfirmButton: false
    });
    //Confirmación con resumen
    $.post(`${path}/consolidate_all`, {
        resume: true
    }, function(html) {
        
        swal.fire({
            title: '¿Está seguro?',
            text: "No podrá revertir esta acción",
            type: 'warning',
            html: html,
            showCancelButton: true,
            confirmButtonText: 'Si, consolidar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                //Barras de carga en swal
                swal.fire({
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    html: `
<div class="card-body pt-0" id="clients-bar">
    <p>Esquema <span class="float-right text-bold-600"></span></p>
    <div class="progress">
        <div class="progress-bar bg-gradient-x-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="0" style="-webkit-transition: unset; transition: unset;"></div>
    </div>
</div>

<div class="card-body pt-0" id="grids-bar">
    <p>Tabla <span class="float-right text-bold-600"></span></p>
    <div class="progress">
        <div class="progress-bar bg-gradient-x-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="0" style="-webkit-transition: unset; transition: unset;"></div>
    </div>
</div>`
                });

                //Consolidación
                $.post(`${path}/consolidate_all`, {},
                function(data) {
                    swal.fire(data);
                    dTable.ajax.reload(null, false);
                }, 'json');
            }
        })


    });

    
});


//SP
$(document).on('click', '.main-sp', function() {
    swal.fire({
        html: loading_layer_txt,
        showConfirmButton: false
    });
    selected = dTable.row($(this).closest('tr')).data()[primary];
    $.post(`${path}/sp`, {
        id: selected
    }, function(data) {
        swal.fire(data);
        dTable.ajax.reload(null, false);
    }, 'json');
});

//SP dbo
$(document).on('click', '#main-spdbo', function() {
    swal.fire({
        html: loading_layer_txt,
        showConfirmButton: false
    });
    // selected = dTable.row($(this).closest('tr')).data()[primary];
    $.post(`${path}/spdbo`, {
        // id: selected
    }, function(data) {
        swal.fire(data);
        // dTable.ajax.reload(null, false);
    }, 'json');
});

//SP cli
$(document).on('click', '#main-spcli', function() {
    swal.fire({
        html: loading_layer_txt,
        showConfirmButton: false
    });
    // selected = dTable.row($(this).closest('tr')).data()[primary];
    $.post(`${path}/spcli`, {
        // id: selected
    }, function(data) {
        swal.fire(data);
        // dTable.ajax.reload(null, false);
    }, 'json');
});

//Cleansing
$('#main-cleansing').click(function() {
    swal.fire({
        html: loading_layer_txt,
        showConfirmButton: false
    });
    //Confirmación con resumen
    $.post(`${path}/cleansing`, {
        resume: true
    }, function(html) {
        
        swal.fire({
            title: '¿Está seguro?',
            type: 'warning',
            html: html,
            width: 600,
            showCancelButton: true,
            confirmButtonText: 'Si, eliminar tablas',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.value) {
                //Barras de carga en swal
                swal.fire({
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    html: `
<div class="card-body pt-0" id="clients-bar">
    <p>Esquema <span class="float-right text-bold-600"></span></p>
    <div class="progress">
        <div class="progress-bar bg-gradient-x-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="0" style="-webkit-transition: unset; transition: unset;"></div>
    </div>
</div>

<div class="card-body pt-0" id="grids-bar">
    <p>Tabla <span class="float-right text-bold-600"></span></p>
    <div class="progress">
        <div class="progress-bar bg-gradient-x-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="0" style="-webkit-transition: unset; transition: unset;"></div>
    </div>
</div>`
                });

                //Consolidación
                $.post(`${path}/cleansing`, {},
                function(data) {
                    swal.fire(data);
                    dTable.ajax.reload(null, false);
                }, 'json');
            }
        })


    });

    
});

$(document).on('click', '.cleansing-toggle', function() {
    $(this).closest('table').find('tbody').toggle()
});

//Save
$("#save").click(function() {
    var data = formToObject("#form-grid");
    data.id = selected;
    //cambio btn save a estado guardando...
    var btn_status = $('#save').html();
    $('#save').html(btn_save_layer).attr("disabled", true);
    $.post(`${path}/set`, data, function(data) {
        if (data.type == 'success') {
            $("#modal-default").modal("hide");
            $('#save').attr("disabled", false).html(btn_status); // reponer btn save
            dTable.ajax.reload();
        }
        swal.fire(data);
    }, 'json');
});

//Delete
$("#main-delete").click(function() {
	var dtRows = dTable.rows({
		selected: true
	});
	var dtTotalRows = dtRows.count();
	if (dtTotalRows == 0) {
		swal.fire({
            type: "warning",
            title: "Eliminar registros",
            text: "No ha seleccionado ningún registro"
        });
		return;
	}
	swal.fire({
		title: "Eliminar registros",
		text: '¿Está seguro que desea eliminar ' + dtTotalRows + ' registro(s)?',
		type: "info",
		showCloseButton: true,
		showCancelButton: true,
		confirmButtonText: 'Eliminar registro(s)',
		cancelButtonText: 'Volver'
	}).then(function(res) {
        if (res.value) {
            //Build Array
            var dtSelectedRows = dtRows.data().toArray();
            var dtSelectedIDs = [];
            $.each(dtSelectedRows, function(index, value) {
                dtSelectedIDs.push(value[primary]);
            })
            console.log(dtSelectedIDs);
            //Send Request
            $.post(`${path}/delete`, {
                list: dtSelectedIDs
            }, function(data) {
                if (data.type == "success") {
                    dTable.ajax.reload(null, false);
                }
                swal.fire(data);
            }, "json");
        }
	}).catch(swal.noop);
});