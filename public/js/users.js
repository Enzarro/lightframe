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
        dTable = $(`#${table_id}`).on('xhr.dt', function ( e, settings, json, xhr ) {
            if (json.swal) {
                swal.fire(json.swal);
            }
        }).DataTable({
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
                /*$(`#${table_id}_filter`).find("input").wrap("<div class='input-group'></div>");
                $(`#${table_id}_filter`).find(".input-group").prepend("<span class='input-group-addon'><span class='input-group-text'><span class='fa fa-search text-center' aria-hidden='true'></span></span></span>");
                $(`#${table_id}_filter`).find("input").css("margin", "0");*/
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
            actionsBox : true,
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

    //init de checkbox lista de recursos
    $('.recursosList').change(function() {
        if ($(this).prop("checked")) {
            $('#select_permiso_' + $(this).prop("id")).selectpicker('val', '').prop("disabled", false).selectpicker('refresh');
        } else {
            $('#select_permiso_' + $(this).prop("id")).selectpicker('val', '').prop("disabled", true).selectpicker('refresh');
        }
    });

    // buscador en lista de clientes
    $(".search").keyup(function () {
    
        var searchTerm = $(".search").val();
        var listItem = $('.results tbody').children('tr');
        var searchSplit = searchTerm.replace(/ /g, "'):containsi('");
        $.extend($.expr[':'], {'containsi': function(elem, i, match, array){
                return (elem.textContent || elem.innerText || '').toLowerCase().indexOf((match[3] || "").toLowerCase()) >= 0;
            }
        });
        $(".results tbody tr").not(":containsi('" + searchSplit + "')").each(function(e){
            $(this).attr('visible','false');
        });
        $(".results tbody tr:containsi('" + searchSplit + "')").each(function(e){
            $(this).attr('visible','true');
        });
        var jobCount = $('.results tbody tr[visible="true"]').length;
        $('.counter').text(jobCount + ' registro (s)');
        if(jobCount == '0'){ $('.no-result').show(); } else { $('.no-result').hide(); }
        
    });
}

//Save
$("#save").click(function() {
    
    if ($("#form-grid").validator('validate').has('.has-error').length === 0) {
        var data = formToObject("#form-grid");
        var data_form = formToObject("#data-form");
        var data_client = formToObject("#data-client");
        
        data.id = selected;
        for (let key of Object.keys(data_form)) {
            //Si existe el combo
            if($(`#select_permiso_${key}`).length) {
                if($(`#${key}`).prop('checked')) {
                    data_form[key] = $(`#select_permiso_${key}`).selectpicker('val');
                }
            }
        }
        //cambio btn save a estado guardando...
        var btn_status = $('#save').html();
        $('#save').html(btn_save_layer).attr("disabled", true);
        $.post(`${path}/set`, {
            usuario: data,
            permisos: data_form,
            clientes: data_client
        }, function(data) {
            $('#save').attr("disabled", false).html(btn_status); // reponer btn save
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

