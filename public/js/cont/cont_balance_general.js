var path = 'c_balance_general';

var loading_layer = "<div id='loading_div' class='alert alert-success'><i class='fa fa-gear fa-spin'></i> Cargando...</div>";
var loading_exportar_layer = "<div id='loading_div' class='alert alert-success'><i class='fa fa-gear fa-spin'></i> Exportando...</div>";
var btn_save_layer = "<i class='fa fa-gear fa-spin'></i> Guardando...";
var loading_layer_full = "<div id='_load_main_frame'>" + loading_layer + "</div>";	

$(document).ready(function() {		
	//Datetime Pickers
	var d = new Date();
	var month = d.getMonth();
	var day = d.getDate();
	var year = d.getFullYear();		
	$('#calendar_from').datetimepicker({
		locale: 'es',
		format: 'YYYY-MM-DD',
		defaultDate: year + '-01-01'
	});
	$('#calendar_to').datetimepicker({
		locale: 'es',
		format: 'YYYY-MM-D',
		defaultDate: new Date(new Date().getFullYear(),11,31),
		useCurrent: false
	});		
	$("#calendar_from").on("dp.change", function (e) {
		$('#calendar_to').data("DateTimePicker").minDate(e.date);
		clear_area();
	});		
	$("#calendar_to").on("dp.change", function (e) {
		$('#calendar_from').data("DateTimePicker").maxDate(e.date);
		clear_area();
	});
	$('#tipo').on('change', function() { clear_area(); });
	init();		
});

function init(){
	$.post(`${path}/load_init_cbo`, {
        client : sessionStorage.getItem("client")
        // function ( d ) {
        //     return $.extend( {}, d, {
        //         client: sessionStorage.getItem("client")
        //     });
        // }
	}, function(data) {
		$("#cboBalance").html(data);
	});
}

$("#filtro_btn").click(function() {
	($('#decimalesFrm').prop('checked')) ? (decimalFrm = 1):(decimalFrm = 0);	
	if($("#tipo").val() == ""){
		swal.fire({	title: "TIPO DE BALANCE",
				text: "ES UN CAMPO OBLIGATORIO",
				type: "warning",
				animation: true,
				html: false
		});  
		return;
	}
	$("#loading_div").remove();
		$("#area_filtro_cuerpo").hide();
		$("#area_filtro_cuerpo").before(loading_layer);
		$("#xls_area").html("");
	$.post(`${path}/buscar`, {
        client : sessionStorage.getItem("client"),
		type: $("#tipo").val(),
		from: $("#calendar_from").val(),
		to: $("#calendar_to").val(),
		decimal: decimalFrm
	}, function (data){
		$("#loading_div").remove();
		$("#area_filtro_cuerpo").show("slow");
		$("#xls_area").html("").html(data);
		$('.bg_detalle').on('click', function() { 
			$("#main-modal").modal("show");
			open_big_book($(this).attr("ind_cuenta"));
		});
	});
});

function open_big_book(unique_account){
	$("#main-modal").find(".modal-body").html(loading_layer);
	($('#decimalesFrm').prop('checked')) ? (decimalFrm = 1):(decimalFrm = 0);	
	$.post(`${path}/big_book_modal`, {
		client : sessionStorage.getItem("client"),
		from: $("#calendar_from").val(),
		to: $("#calendar_to").val(),
		account: unique_account,
		decimal: decimalFrm
	}, function(data) {
		$('#main-modal').animate({ scrollTop: 0 }, 'fast');
		$("#main-modal").find(".modal-body").html(data);
		$('.com_detalle').on('click', function() { 
			open_comprobant($(this).attr("ind_folio"), $(this).attr("ind_account"));
		});
	});
}

function open_comprobant(id_comprobant, unique_account){
	$("#main-modal").find(".modal-body").html(loading_layer);
	($('#decimalesFrm').prop('checked')) ? (decimalFrm = 1):(decimalFrm = 0);	
	$.post(`${path}/comprobant_detail`, {
		id_comprobant: id_comprobant,
		client : sessionStorage.getItem("client"),
		decimal: decimalFrm
	}, function (data) {
		$('#main-modal').animate({ scrollTop: 0 }, 'fast');
		$("#main-modal").find(".modal-body").html(data);
		$('#backLm').on('click', function() { open_big_book(unique_account); });
	});
}

function clear_area(){	
	$("#loading_div").remove();
	$("#area_filtro_cuerpo").hide();
	$("#xls_area").html("");	
}

$("#export_xls").click(function() {
	$("#xlsform").submit();
});

$("#export_pdfb").click(function() {
	$("#pdfbform").submit();
});

$("#export_pdfl").click(function() {
	$("#pdflform").submit();
});

// /* OPEN PDF */
// 	$(document).off('click', '.modalPdf').on('click', '.modalPdf', function(){
// 		docType = $(this).attr("docType");
// 		switch(docType){
// 			case "xls":
// 				pdfHeader = '<table class="table">' + $("#tabla_header").html() + '</table>';
// 				pdfBody = '<table class="table">' + $("#tabla_pdf").html() + '</table>';
// 			break;
// 			case "pdf":
// 				if($(this).attr("pdfOpt") == "L") $("#thCol1").hide();
// 				pdfHeader = $("#tabla_header").html();
// 				pdfBody = $("#tabla_pdf").html();
// 			break;
// 		}
// 		pdfHeader = pdfHeader;
// 		pdfBody = pdfBody;
// 		$("#pdfShow").remove();
// 		$("#pdfForm").attr("action", 'http://34.236.202.115/');
// 		// $("#pdfFile").val(cryp(apiPath,"en"));
// 		$("#pdfFile2").val('localhost');
// 		$("#pdfHeader").val(cryp(pdfHeader,"en"));
// 		$("#pdfBody").val(cryp(pdfBody,"en"));
// 		// $("#pdfKey").val(keyPdfPrint);
// 		$("#typeDoc").val(cryp(docType,"en"));
// 		$("#pdfForm").attr({"target" : "_blank"});				
// 		$("#pdfForm").submit();
// 		$("#thCol1").show();
// 	});