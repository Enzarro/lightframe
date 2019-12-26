var path = 'cont_plan_individual';
var dTable_c;
var dTable_s;
var selected;

$(document).ready(function(){
    $("#paso_2").hide();
    $("#paso_3").hide();  
    $("#file_add").hide();
    
});

$(document).on('client-change', function() {
    dTable_c.ajax.reload();

});


$.post(`${path}/list`,{ config: true, type: "accounts" },function(columnDefs) {
    //Load DataTables
    dTable_c = $(`#cuentas_generales`).on('xhr.dt', function ( e, settings, json, xhr ) {
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
                    type : "accounts",
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


$(document).off('click', '#check').on('click', '#check', function () {
	selected = dTable_c.row($(this).closest('tr'));
	if ($(this).is(':checked') == true) {
		status = 1;
        msg = "activar";
	} else {
		status = 0;
        msg = "cancelar";
	}
	swal.fire({
		title: "",
		text: "Seguro de " + msg + " de esta cuenta?",
        type: "info",
		showCancelButton: true
	}).then(function(result) {
        console.log(selected.data());
        $.post(`${path}/set`, {
            fn: "account-parent",
            status: status,
            account: selected.data().codigo_cuenta,
            parent: selected.data().parent,
            client: sessionStorage.getItem("client")
         }, function(data) {
            console.log(data);
            if(data == true){
                $(dTable_c.cell(selected, 3).node()).html('<div><button type="button" id="paso2" class="btn btn-success btn-sm" > Paso 2 <span class="fas fa-angle-double-right"></span></button></div>')
            } else {
                $(dTable_c.cell(selected, 3).node()).html('')
            }
        });
    }, function(dismiss) {
		if (dismiss === 'cancel') {
            // ($(this).is(':checked')) ? ($(this).prop('checked', false)) : ($(this).prop('checked', true));
			($("#check").is(':checked') == true) ? ($("#check").prop('checked', false)) : ($("#check").prop('checked', true));
		}
	});
});


//Volver al paso 1
$(".paso1").on("click",function(){
    $("#paso_2").hide("slow");
    $("#paso_1").show("slow");
    selected = null;
    dTable_c.ajax.reload(null, false);
});

//Volver al paso 2
$(".paso2").on("click",function(){
    $("#paso_3").hide("slow");
    $("#paso_2").show("slow");
    selected = null;
    dTable_s.ajax.reload(null, false);
    
});

$(document).on('click', '#paso2', function () {
    selected = dTable_c.row($(this).closest('tr'));
    var codigo = selected.data().codigo_cuenta;
    $("#paso_1").hide("slow");
    $("#paso_2").find(".title2").html("SELECCIÓN DE CUENTA HIJO PARA <b>"+codigo+"</b>");
    $.post(`${path}/list`,{ config: true, type: "subaccounts", codigo : codigo },function(columnDefs) {
        //Load DataTables
        if(!dTable_s){
            dTable_s = $(`#subcuentas`).on('xhr.dt', function ( e, settings, json, xhr ) {
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
                            type : "subaccounts",
                            codigo : codigo,
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
        }
    }, "json");
    
    $("#paso_2").show("slow");
});

$(document).off('click', '#check2').on('click', '#check2', function () {
	selected = dTable_s.row($(this).closest('tr'));
	if ($(this).is(':checked') == true) {
		status = 1;
        msg = "activar";
	} else {
		status = 0;
        msg = "cancelar";
	}
	swal.fire({
		title: "",
		text: "Seguro de " + msg + " de esta cuenta?",
        type: "info",
		showCancelButton: true
	}).then(function() {
        console.log(selected.data());
		$.post(`${path}/set`, {
            fn: "account-parent",
			status: status,
			account: selected.data().codigo_cuenta,
            parent: selected.data().parent,
            client: sessionStorage.getItem("client")
		}, function(data) {
            console.log(data);
            if(data == true){
                $(dTable_s.cell(selected, 3).node()).html('<div><button type="button" id="paso3" class="btn btn-success btn-sm" > Paso 3 <span class="fas fa-angle-double-right"></span></button></div>')
            } else {
                $(dTable_s.cell(selected, 3).node()).html('')
            }
		});
    }, function(dismiss) {
		if (dismiss === 'cancel') {
            // ($(this).is(':checked')) ? ($(this).prop('checked', false)) : ($(this).prop('checked', true));
			($("#check").is(':checked') == true) ? ($("#check").prop('checked', false)) : ($("#check").prop('checked', true));
		}
	});
});


$(document).on('click', '#paso3', function () {
    selected = dTable_s.row($(this).closest('tr'));
    $("#paso_2").hide("slow");
    $("#paso_3").find(".title3").html("CONFIGURACIÓN DE CUENTA <b>"+selected.data().codigo_cuenta+"</b>");
    $("#paso_3").show("slow");
    $("#paso_3").find(".form-body").html("<div class='row text-center'><div class='card-body col-md-12'><i class='fas fa-spinner font-large-5 info fa-spin'></i><h4 class='info mt-1'>Cargando ...</h4></div></div>");
    var parent = $("#paso_2 .title2").find("b").html();
    $.post(`${path}/get`, { codigo: selected.data().codigo_cuenta, parent: parent,client: sessionStorage.getItem("client") }, function(data){
        $("#paso_3").find(".form-body").html(data);
        $("#rubros_pcga").selectpicker({
			liveSearch: true
        });
        $("#clasificacion_pcga").selectpicker({
			liveSearch: true
        });
        $("#rubros_fip").selectpicker({
			liveSearch: true
        });
        $("#clasificacion_fip").selectpicker({
			liveSearch: true
        });
        $("#rubros_ifrs").selectpicker({
			liveSearch: true
        });
        $("#clasificacion_ifrs").selectpicker({
			liveSearch: true
        });
        $("#caracteristicas").selectpicker({
            liveSearch: true,
            actionsBox : true
        });
        $("#doble").selectpicker({
            liveSearch: true,
        });
        $("#neteo").selectpicker({
            liveSearch: true,
		});
    });    
    // $("#paso_3").show("slow");
});


$(document).on('click', '#save_form', function(){
    item = {};
	item["pcga"] = { activo : ($("#pcga").is(":checked")) ? 1: 0, rubro : ($("#rubros_pcga").val()) ? $("#rubros_pcga").val(): 0, clasificacion : ($("#clasificacion_pcga").val()) ? $("#clasificacion_pcga").val() :0 };
    item["fip"] = { activo : ($("#fip").is(":checked")) ? 1: 0, rubro : ($("#rubros_fip").val()) ? $("#rubros_fip").val() :0, clasificacion : ($("#clasificacion_fip").val()) ? $("#clasificacion_fip").val() :0};
    item["ifrs"] = { activo : ($("#ifrs").is(":checked")) ? 1: 0, rubro : ($("#rubros_ifrs").val()) ? $("#rubros_ifrs").val() : 0, clasificacion : ($("#clasificacion_ifrs").val()) ? $("#clasificacion_ifrs").val() : 0};
    item["caract"] =  $("#caracteristicas").val();
    item["dp"] = { doble : $("#doble").val(), neteo : $("#neteo").val()}
    item["codigo"] = $("#paso_3 .title3").find("b").html();
    item["parent"] = $("#paso_2 .title2").find("b").html();
    console.log(item);
    $.post(`${path}/set`,{ fn:"update-plan", datos:JSON.parse(JSON.stringify(item)), client: sessionStorage.getItem("client") }, function(data){
        console.log(data);
        swal.fire(JSON.parse(data));
    });
});


function activar_select(elem){
    if($(elem).is(":checked")){
        $(elem).closest("tr").find("select").each(function(i,v){
            $(this).prop("disabled", false).selectpicker('refresh').focus();
        });
    } else {
        $(elem).closest("tr").find("select").each(function(i,v){
            $(this).prop("disabled", true).selectpicker('refresh').focus();
        });
    }
}

function exportxls(fn){
    $.post(`${path}/export`,{fn: fn, client: sessionStorage.getItem("client")},function(data){
            $("#JSONtoXLS").html(data);
            $("#xlsform").submit();
        
    });
}






