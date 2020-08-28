var dTable, selected;
var path = 'sys_grid';
var primary = 'grid_id';
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
        language: dtSpanish,
        initComplete: function(settings, json) {
            $('[data-toggle="popover"]').popover();
            //Format search box
            /*$(`#${table}_filter`).find("input").wrap("<div class='input-group'></div>");
            $(`#${table}_filter`).find(".input-group").prepend("<span class='input-group-addon'><span class='input-group-text'><span class='fa fa-search text-center' aria-hidden='true'></span></span></span>");
            $(`#${table}_filter`).find("input").css("margin", "0");*/
        }
    });
}, "json");

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
    swal.fire({
        html: loading_layer_txt,
        showConfirmButton: false
    });
    $.post(`${path}/consolidate`, {
        id: selected
    }, function(data) {
        swal.close();
        swal.fire(data);
    }, 'json');
});

//Save
$("#save").click(function() {
    if (validateForm("#form-grid")) {
        var data = formToObject("#form-grid");
        data.id = selected;
        $.post(`${path}/set`, data, function(data) {
            if (data.type == 'success') {
                $("#modal-default").modal("hide");
                dTable.ajax.reload();
            }
            swal.fire(data);
        }, 'json');
    }
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
                dtSelectedIDs.push(value['grid_id']);
            })
            console.log(dtSelectedIDs);
            // return;
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

$("#main-export").click(function() {
    var win = window.open(`${path}/export`, '_blank');
    // win.focus();
});

socket.on('sys_grid:import:bar', function(data) {
    //Clients bar
    $("#grids-bar").find("span").html(`${data.bar_grids.name} (${data.bar_grids.current}/${data.bar_grids.max})`);
    $("#grids-bar").find(".progress-bar").css('width', `${100/data.bar_grids.max*data.bar_grids.current}%`)
    $("#grids-bar").find(".progress-bar").attr('aria-valuenow', data.bar_grids.current);
    $("#grids-bar").find(".progress-bar").attr('aria-valuemax', data.bar_grids.max);
});

$("#main-import").on("change.bs.fileinput", function() {
    var objFile = $(this)[0].files[0];
    if(objFile!==undefined){
        swal.fire({
            html: loading_layer_txt,
            showConfirmButton: false
        });
        if ($.inArray(objFile.name.split('.').pop(), ['json']) == -1) {
            $(this)[0].value = '';
            swal.fire({type: 'warning', title: 'Formato no permitido', text: 'Sólo se admiten los siguientes formatos: JSON'});
            return;
        } else {
            var formData = new FormData();
            formData.append('main-import',$("#main-import")[0].files[0]);
            formData.append('client',sessionStorage.getItem("client"));

            //Barras de carga en swal
            swal.fire({
                showConfirmButton: false,
                allowOutsideClick: false,
                allowEscapeKey: false,
                allowEnterKey: false,
                html: `
<div class="card-body pt-0" id="grids-bar">
<p>Grilla <span class="float-right text-bold-600"></span></p>
<div class="progress">
    <div class="progress-bar bg-gradient-x-danger" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="0" style="-webkit-transition: unset; transition: unset;"></div>
</div>
</div>`
            });

            $.ajax({
                type: 'POST',
                url: `${path}/import`,
                data: formData,
                dataType: 'JSON',
                processData: false,
                contentType: false,  
                success: (data) => {
                    if(data.stat == "resume"){
                        $(".modal-body").html(data);
                        $(".modal").modal("show");
                    }else{
                        if (data.type == "success") {
                            dTable.ajax.reload(null, false);
                            swal.fire(data);
                        }else{
                            $("#main-import").val('');
                            swal.fire({type: 'error', title: 'Ocurrio un problema', html: data, width:800});
                        }
                    }
                }
            });
        }
    }
});