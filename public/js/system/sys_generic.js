var dTable, selected = '';
var table = 'generic';

//Grid
$.post(`${path}/list`, {
    config: true
}, function(columnDefs) {
    //Load DataTables
    dTable = $(`#${table}`).DataTable({
        autoWidth: false,
        serverSide: true,
        processing: true,
        ajax: {
            url: `${path}/list`,
            type: 'POST',
            data: function ( d ) {
                return $.extend( {}, d, {
                    client: sessionStorage.getItem("client")
            });
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

$(document).on('client-change', function() {
    dTable.ajax.reload();
});

//Form
$(document).on("click", "#main-new,.main-edit", function() {
    selected = undefined;
    if ($(this).hasClass("main-edit")) {
        selected = dTable.row($(this).closest('tr')).data()[primary];
    }
    swal.fire({
		html: '<div class="pt-1"><i class="fa fa-spinner fa-pulse fa-3x fa-fw "></i></div>',	
		title: "Cargando...",
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false
	});
    $.post(`${path}/form`, {
        id: selected,
        client: sessionStorage.getItem("client")
    }, function(data) {
        $("#modal-default .modal-body").html(data);
        InitFormFields("#modal-default .modal-body");
        $("#modal-default").modal("show");
        swal.close();
    });
});

//Save
$("#save").click(function() {
    if ($("#form-generic").validator('validate').has('.has-error').length === 0) {
        var data = formToFormData("#form-generic");
        // data.id = selected;
        // data.client = sessionStorage.getItem("client");
        data.append('client', sessionStorage.getItem("client"));
        
        if(!data.get('id')){
            if(selected){
                data.append('id', selected);
            } else {
                data.append('id', '');
            }
        } else {
            if(selected){
                data.set('id', selected);
            } else {
                data.set('id', '');
            }
        }        
                
        // cambio btn save a estado guardando...
        var btn_status = $('#save').html();
        $('#save').html(btn_save_layer).attr("disabled", true);
        
        $.post({
            url: `${path}/set`,
            data: data,
            dataType: 'JSON',
            processData: false,
            contentType: false,  
            success: (data) => {
                if (data.type == 'success') {
                    $("#modal-default").modal("hide");
                    $('#save').attr("disabled", false).html(btn_status); // reponer btn save
                    dTable.ajax.reload();
                }
                swal.fire(data);
            }
        });
    
        // $.post(`${path}/set`, data, function(data) {
        //     if (data.type == 'success') {
        //         $("#modal-default").modal("hide");
        //         $('#save').attr("disabled", false).html(btn_status); // reponer btn save
        //         dTable.ajax.reload();
        //     }
        //     swal.fire(data);
        // }, 'json');
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
                dtSelectedIDs.push(value[primary]);
            })
            //Send Request
            $.post(`${path}/delete`, {
                list: dtSelectedIDs,
                client: sessionStorage.getItem("client")
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
    $.post(`${path}/export`,{client: sessionStorage.getItem("client"), type:"export"},function(data){
        $("#JSONtoXLS").html(data);
        $("#jsonxls").submit();     
    });
});

$("#main-import").on("change.bs.fileinput", function() {
    var objFile = $(this)[0].files[0];
    if(objFile!==undefined){
        swal.fire({
            html: loading_layer_txt,
            showConfirmButton: false
        });
        if ($.inArray(objFile.name.split('.').pop(), ['xlsx','xls']) == -1) {
            $(this)[0].value = '';
            swal.fire({type: 'warning', title: 'Formato no permitido', text: 'Sólo se admiten los siguientes formatos: Excel'});
            return;
        }else{
            $.post(`${path}/export`,{client: sessionStorage.getItem("client"), type:"import"},function(data){
                $("#XLStoJSON").html(data);     
                var formData = new FormData();
                formData.append('XLStoJSON',$("#XLStoJSON").val());
                formData.append('main-import',$("#main-import")[0].files[0]);
                formData.append('client',sessionStorage.getItem("client"));
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
            });
        }
    }
});