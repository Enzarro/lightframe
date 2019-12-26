var path = 'cont_plan_general';

$.post(`${path}/list`,{ config: true },function(columnDefs) {
    //Load DataTables
    dTable = $(`#plan_general`).on('xhr.dt', function ( e, settings, json, xhr ) {
        if (json.swal) {
            swal.fire(json.swal);
        }
    }).DataTable({
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
        language: dtSpanish,
        initComplete: function(settings, json) {
        }
    });
}, "json");


$("#pg-new").on('click',function(){
    $.post(`${path}/form`, { }, function(data) {
        type = 0;
        $("#modal-plangeneral .modal-body").html(data);        
        $("#modal-plangeneral").find("form").validator();
        // InitFormFields("#modal-plangeneral .modal-body");
        $("#modal-plangeneral").modal("show");
    });
});

$("#pg-delete").on("click", function(){
    var dtRows = dTable.rows({
        selected: true
    });

    if(dtRows.count()>0){
        swal.fire({
            title: "Eliminar registros",
            text: '¿Está seguro que desea eliminar ' + dtRows.count() + ' registro(s)?',
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
                    dtSelectedIDs.push(value['codigo_cuenta']);
                })
                //Send Request
                $.post(`${path}/delete`, {
                    codigos : dtSelectedIDs,
                    client: sessionStorage.getItem("client")
                }, function(data) {
                    if (data.type == "success") {
                        dTable.ajax.reload(null, false);
                    }
                    swal.fire(data);
                }, "json");
            }
        }).catch(swal.noop);
    } else {
        swal.fire({
            type: "warning",
            title: "Eliminar registros",
            text: "No ha seleccionado cuenta"
        });
    }

});

$(document).on("click", ".main-edit", function(){
    var id = null;
    if ($(this).hasClass("main-edit")) {
        id = dTable.row($(this).closest('tr')).data()['codigo_cuenta'];
    }
    $.post(`${path}/form`, {
        codigo_cuenta : id
    }, function(data) {
        type = 1;
        $("#modal-plangeneral .modal-body").html(data);
        $("#modal-plangeneral").find("form").validator();
        // InitFormFields("#modal-plangeneral .modal-body");
        $("#modal-plangeneral").modal("show");
    });

});



$("#save").click(function(){
    var cont = $("#modal-plangeneral").find("form").validator('validate').has('.has-error').length;
    var type = $("#type").length>0 ? $("#type").val() : 0;
    var datos = [];
    $("#listdiv .row").each(function(i,v){
        item = {};
        item["codigo_cuenta"] = $(this).find(".cod_cuentas").val();
        item["descripcion"] = $(this).find(".detalle_cuentas").val();
        item["parent"] = $("#codigo_cuenta").val();
        item["type"] = $(this).find(".type").length>0 ? $(this).find(".type").val() : 0;
        datos.push(item);
    });

    if(cont == 0){
       $.post(`${path}/set`,
        {type: type, codigo_cuenta: $("#codigo_cuenta").val(), descripcion: $("#descripcion").val(), 
            subcuentas: JSON.parse(JSON.stringify(datos))},
        function(data){
            swal.fire(JSON.parse(data));
            $("#modal-plangeneral").modal("hide");
             dTable.ajax.reload();
        });
    } else {
         swal.fire({
             text: "Debe completar todos los campos obligatorios.",
             type: "warning"
         });
    }
});

   
function subcuentas_new(){
    $("#subcuentas").clone().appendTo("#subcuentas_clone");
    $("#subcuentas_clone .input-group-btn:last-child").show();
    $("#subcuentas_clone .row:last-child .col-md-7").find("input").val('');
    $("#subcuentas_clone .row:last-child .col-md-5").find("input").removeAttr("readonly").val('');
    $("#modal-plangeneral").find("form").validator('update');
}

function subcuentas_delete(elem){
    $(elem).closest(".row").remove();
    $("#modal-plangeneral").find("form").validator('update');
}


