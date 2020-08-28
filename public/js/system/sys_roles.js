var dTable, selected, type;
var path = 'sys_roles';
var primary = 'id';
// var table_resources = 'resources';

$.post(`${path}/list`, {
    config: true
}, function(columnDefs) {
    //Load DataTables
    dTable = $(`#roles`).on('xhr.dt', function ( e, settings, json, xhr ) {
        if (json.swal) {
            swal.fire(json.swal);
        }
    }).DataTable({
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
            //Format search box
            /*$(`#${table_id}_filter`).find("input").wrap("<div class='input-group'></div>");
            $(`#${table_id}_filter`).find(".input-group").prepend("<span class='input-group-addon'><span class='input-group-text'><span class='fa fa-search text-center' aria-hidden='true'></span></span></span>");
            $(`#${table_id}_filter`).find("input").css("margin", "0");*/
        }
    });
}, "json");

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

    $('.apply-all').click(function() {
        let val = $(this).val();
        if ($(`.recursosList[data-parent="${val}"]:checked`).length) {
            $(`.recursosList[data-parent="${val}"]`).prop("checked", false).change()
        } else {
            $(`.recursosList[data-parent="${val}"]`).click()
        }
    });

    //init de checkbox lista de recursos
    $('.recursosList').change(function() {
        if ($(this).prop("checked")) {
            $('#select_permiso_' + $(this).val()).prop("disabled", false).selectpicker('refresh');
            
        } else {
            $('#select_permiso_' + $(this).val()).selectpicker('val', '').prop("disabled", true).selectpicker('refresh');
        }
    }).change();

    $('.recursosList').click(function() {
        if ($(this).prop("checked")) {
            $('#select_permiso_' + $(this).val()).selectpicker('selectAll');
        }
    });

    //init de checkbox lista de recursos
    $('.clientesList').change(function() {
        if ($(this).prop("checked")) {
            $('#select_perfiles_' + $(this).val()).prop("disabled", false).selectpicker('refresh');
        } else {
            $('#select_perfiles_' + $(this).val()).selectpicker('val', '').prop("disabled", true).selectpicker('refresh');
        }
    }).change();

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
    if (validateForm("#form-roles") && validateForm("#form-plus")) {
        var data_rol = formToObject("#form-roles");
        var data_permisos = formToObject("#form-permisos");

        data_rol.id = selected;

        // RECURSOS / PERMISOS
        for (let key of Object.keys(data_permisos)) {
            //Si existe el combo
            if($(`#select_permiso_${key}`).length) {
                if($(`#res_${key}`).prop('checked') && $(`#select_permiso_${key}`).selectpicker('val').length) {
                    data_permisos[key] = $(`#select_permiso_${key}`).selectpicker('val');
                }
            }
        }
        //cambio btn save a estado guardando...
        var btn_status = $('#save').html();
        // $('#save').html(btn_save_layer).attr("disabled", true);
        $.post(`${path}/set`, {
            rol: data_rol,
            permisos: data_permisos,
        }, function(data) {
            $('#save').attr("disabled", false).html(btn_status); // reponer btn save
            if (data.type == 'success') {
                $("#modal-default").modal("hide");
                dTable.ajax.reload();
            }
            swal.fire(data);
        }, 'json');

    }
});

$("#main-export").click(function() {
    var win = window.open(`${path}/export`, '_blank');
    // win.focus();
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
<div class="card-body pt-0" id="trees-bar">
<p>Recursos <span class="float-right text-bold-600"></span></p>
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
                dtSelectedIDs.push(value['id']);
            })
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

