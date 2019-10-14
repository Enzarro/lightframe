var dTable, selected, type;
var path = 'users';
var primary = 'usuario_id';
var table = 'usuarios';
// var table_resources = 'resources';

$(document).ready(function() {
    load_table(table, 'list');
    // load_table(table_resources, 'resources'); 
});

function load_table(table_id, method) {
    $.post(`${path}/${method}`, {
        config: true
    }, function(columnDefs) {
        //Load DataTables
        dTable = $(`#${table_id}`).DataTable({
            autoWidth: false,
            serverSide: true,
            ajax: {
                url: `${path}/${method}`,
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
                //Format search box
                $(`#${table_id}_filter`).find("input").wrap("<div class='input-group'></div>");
                $(`#${table_id}_filter`).find(".input-group").prepend("<span class='input-group-addon'><span class='input-group-text'><span class='fa fa-search text-center' aria-hidden='true'></span></span></span>");
                $(`#${table_id}_filter`).find("input").css("margin", "0");
            }
        });
    }, "json");
}

//Form
$(document).on("click", "#main-new,.main-edit", function() {
    selected = undefined;
    if ($(this).hasClass("main-edit")) {
        selected = dTable.row($(this).closest('tr')).data()[primary];
        type = 0;
    } else {
        type = 1;
    }
    $.post(`${path}/form`, {
        id: selected
    }, function(data) {
        $(".modal-body").html(data);
        $(".selectpicker").selectpicker({
            liveSearch: true,
            width: '300px'
        })
        if(type == 1) {
            $(".selectpicker").each(function() {
                $(this).prop("disabled", true).selectpicker('val', '').selectpicker('refresh');
            })
        } else {
            type = 0;
        }
        initForm();    
        $("#modal-default").modal("show");
    });
});

function initForm() {
    InitFormFields(".modal-body");
    $('input[type="checkbox"].minimal, input[type="radio"].minimal').iCheck({
        checkboxClass: 'icheckbox_minimal-blue',
        radioClass   : 'iradio_minimal-blue'
    });
    $('input[type="checkbox"]').on('ifChanged', function(){
        if ($(this).prop("checked")) {
            $('#select_permiso_' + $(this).prop("id")).selectpicker('val', '').prop("disabled", false).selectpicker('refresh');
        } else {
            $('#select_permiso_' + $(this).prop("id")).selectpicker('val', '').prop("disabled", true).selectpicker('refresh');
        }
    });
}

//Save
$("#save").click(function() {

    if ($("#form-grid").validator('validate').has('.has-error').length === 0) {
        var data = formToObject("#form-grid");
        var data_form = formToObject("#data-form");
        data.id = selected;
        for (let key of Object.keys(data_form)) {
            if($(`#select_permiso_${key}`).length) {
                if($(`#${key}`).prop('checked')) {
                    data_form[key] = $(`#select_permiso_${key}`).selectpicker('val');
                }
            }
        }
        console.log(data_form)
        $.post(`${path}/set`, {
            usuario: data,
            permisos: data_form
        }, function(data) {
            if (data.type == 'success') {
                $("#modal-default").modal("hide");
                dTable.ajax.reload();
            }
            swal.fire(data);
        }, 'json');
    } else {
        swal.fire({
			text: "Debe completar todos los campos obligatorios.",
			type: "warning"
		});
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
                dtSelectedIDs.push(value['usuario_id']);
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

