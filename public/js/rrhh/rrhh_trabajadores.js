var dTable;
var dTableCargas;

var path = 'rrhh_trabajadores';
var primary = 'id';
var table = 'mantenedores';

var selected;
var datenacimiento;
var dv = function(T) {
	var M=0,S=1;
	for(;T;T=Math.floor(T/10))
	S=(S+T%10*(9-M++%6))%11;
	return S?S-1:'K';
};

$(document).ready(function() {
    //GRID: Toggle View Disabled Trabajador
    $('#element-worker-viewdisabled').click(function(){
        if($(this).val() == 'true') {
            $(this).val('false');
            $(this).html('<span aria-hidden="true" class="glyphicon glyphicon-eye-open"></span>');
        } else {
            $(this).val('true');
            $(this).html('<span aria-hidden="true" class="glyphicon glyphicon-eye-close"></span>');
        }
        dTable.draw();
    });
	//Datetime Pickers
	$('#fechatributaria').datetimepicker({
		locale: 'es',
		format: 'YYYY-MM',
		viewMode: 'months',
		useCurrent: false,
		defaultDate: new Date(),
		maxDate: new Date()
	});
	$("#fechatributaria").on("dp.change", function(e) {
		dTable.ajax.reload();
	});
	//GRID: Load DataTables Configurations
	$.post(`${path}/list`,
	{
		config: true,
		client: sessionStorage.getItem("client")
	},
	function(columnDefs) {
		//Load DataTables
		dTable = $('#dataTables-ccg').DataTable({
			responsive: true,
			"serverSide": true,
			"ajax": {
				"url": `${path}/list`,
				"type": 'POST',
				"data": function ( d ) {
					LoadMonthIM();
					return $.extend( {}, d, {
						date: $('#fechatributaria').val(),
						viewdisabled: $('#element-worker-viewdisabled').val(),
						client: sessionStorage.getItem("client")
					});
				}
			},
			"columnDefs": columnDefs,
			select: {
				style: 'multi',
				selector: 'td:last-child'
			},
			"order": [
				[ 0, "desc" ]
			],
			"language": dtSpanish
		});
	}, "json");
	
	//PREVIRED: Initialize
	// $('#previredpicker').datetimepicker({
	// 	inline: true,
	// 	locale: 'es',
	// 	format: 'YYYY-MM',
	// 	viewMode: 'months'
	// });
	// $('#previredpicker').data("DateTimePicker").clear();
	// $("#previredpicker").on("dp.change", function(e) {
	// 	submit_post_via_hidden_form(
	// 		pathSite+"api/ajax/files.r_previred.php",
	// 		{
	// 			fn: "previred",
	// 			month: $('#previredpicker').data("DateTimePicker").date().format('MM'),
	// 			year: $('#previredpicker').data("DateTimePicker").date().format('YYYY')
	// 		}
	// 	);
	// });
	InitFields();
});

$(document).on('client-change', function() {
    dTable.ajax.reload();
});

function LoadMonthIM() {
	$.post(`${path}/`, {
		fn:	"inmen",
		month: $('#fechatributaria').val()
	}, function(data) {
		$("#monthdata").hide().slideDown('fast').html(`<b>UF:</b> $${data.R47}, <b>IPC:</b> ${data.R50}%, <b>Tope gratificación:</b> ${data.O59} UF`);
	}, "json")
}

function InitFields() {
	
	
}


//GRID MODAL: New Trabajador Form
$('#main-new').click(function() {
	if (!(sessionStorage.getItem("client") == undefined || sessionStorage.getItem("client") == '')) {
		$.post(`${path}/form`, {
			client: sessionStorage.getItem("client"),
			date: $('#fechatributaria').val()
		}, function(data, satus) {
			//Después de intentar realizar login, llamar a primera página a cargar (Enrutamiento)
			$( "#modal-default .modal-title" ).html( 'Nuevo Trabajador' );
			$("#modal-default .modal-body").html(data);
			// $( "#ez-Modal-Body" ).html( data );
	
			$( "#modal-default .modal-footer" ).html(	`<button id="modal-worker-save" type="button" class="btn btn-primary m1save" style="margin-right: 15px;"><span class="fa fa-floppy-o" aria-hidden="true"></span> Guardar</button>
										<button type="button" class="btn btn-link closemodal">Cerrar</button>`);
			// $( ".modal-footer" ).show();
			// $( "#ez-Modal" ).modal( 'show' );
			$("#modal-default").modal("show");
			
			$("#nacionalidad").val("46"); //Setear chile como el país por defecto
			$("#formapago").trigger("change");
			$("#contrato").trigger("change");
			
			InitWorkerFields();
		});
	} else {
		swal.fire({
			type: 'warning',
			text: 'Debe seleccionar un proyecto para poder crear trabajadores en él'
		})
	}
});
//MODAL: Form Trabajador Save
$(document).off('click', '#modal-worker-save').on('click', '#modal-worker-save', function() {
    var validBonos = !validateTable(tBonos);
    var validCargas = !validateTable(tCargas);
	if (validBonos != true || validCargas != true) {
		swal.fire({
			type: 'warning',
			title: 'Tablas de subregistros',
			text: 'Hay subregistros cuyos cambios no han sido confirmados.'	
		});
		return;
	}
	if ($("#trabajador_new").validator('validate').has('.has-error').length === 0) {
		$("#centroscostos").find("#data").val(JSON.stringify(tCCs.DataTable().data().toArray()));
        $("#bonos").find("#data").val(JSON.stringify(tBonos.DataTable().data().toArray()));
		$("#cargas").find("#data").val(JSON.stringify(tCargas.DataTable().data().toArray()));
		$.post(`${path}/set`,
		{
			client: sessionStorage.getItem("client"),
			form: JSON.stringify($("#trabajador_new").autoNumeric('getArray'))
		},
		function(data, satus){
			//Get POST Data
			var result;
			result = JSON.parse(data);
			//Add ROW to DataTable
			if (result["stat"] == "success") {
				dTable.draw( false );
			}
			//Display Modal Info
			swal.fire({
				title: result["desc"],
				type: result["stat"]
			});
            $("#modal-default").modal('hide');
		});
	}
});

//GRID MODAL: Edit Trabajador Form
$(document).off('click', '#element-worker-edit').on('click', '#element-worker-edit', function(){
	selected = dTable.row($(this).closest('tr'));
	console.log(selected.data());
	$.post(`${path}/form`,
	{
		client: sessionStorage.getItem("client"),
		identifier: selected.data()[primary],
		date: $('#fechatributaria').val()
	},
	function(data, satus){
		$( "#ez-Modal-Title" ).html( "<strong>Trabajador:</strong> "+selected.data()['nombre'] );
		$( "#ez-Modal-Body" ).html( data );
		$( ".modal-footer" ).html(	'<button id="modal-worker-update" type="button" class="btn btn-primary m1save" style="margin-right: 15px;"><span class="fa fa-floppy-o" aria-hidden="true"></span> Guardar</button>'+
									'<button type="button" class="btn btn-link closemodal">Cerrar</button>');
		$( ".modal-footer" ).show();
		$("#rut-s").attr("readonly",true);
		$("#formapago").trigger("change");
		$("#contrato").trigger("change");
		//Pais de nacionalidad = Chile
		if($("#nacionalidad").val() != '46'){
			$("#ciudadorigen").parent().parent().parent().css("display", "block");
		}
		
		//Modal
		$( "#ez-Modal" ).modal( 'show' );
		//Init fields
		InitWorkerFields();
		
	});
});

//Load log list
function LoadLogList(json) {
	var data = JSON.parse(json);
	$("#changelog").html("");
	$(data).each(function() {
		var cloned = ("<tr><td>"+this.fecha_log.substring(0, 7)+"</td><td>"+this.sueldo_base+"</td><td>"+this.centro_costo+"</td><td>"+this.cargo+"</td><td>"+this.afp+"</td><td>"+this.isapre+'</td><td><button id="'+this.fecha_log+'" class="btn btn-danger removelog" title="Eliminar registro" type="button"><span aria-hidden="true" class="glyphicon glyphicon-trash"></span></button></td></tr>');
		$("#changelog").append(cloned);
	});
}
//Remove log
$(document).off('click', '.removelog').on('click', '.removelog', function() {
	$.post(`${path}/`,
	{
		fn: "delete-datalog",
		id: selected.data()[0],
		month: $(this).attr("id")
	},
	function(data, satus){
		if (data.stat == "success") {
			LoadLogList(data.loglist);
		}
		swal.fire({
			title: data.desc,
			type: data.stat
		});
	}, "json");
});
//MODAL: Edit Trabajador Save
$(document).off('click', '#modal-worker-update').on('click', '#modal-worker-update', function() {
	if (validateTable(tCargas)) {
		swal.fire({
			type: 'warning',
			title: 'Cargas familiares',
			text: 'Hay cargas cuyos cambios no han sido confirmados.'	
		});
		return;
	}
	if ($("#trabajador_new").validator('validate').has('.has-error').length === 0) {
		$("#centroscostos").find("#data").val(JSON.stringify(tCCs.DataTable().data().toArray()));
        $("#cargas").find("#data").val(JSON.stringify(tCargas.DataTable().data().toArray()));
        $("#bonos").find("#data").val(JSON.stringify(tBonos.DataTable().data().toArray()));
		$.post(`${path}/`,
		{
			fn: "formtrabajador-update",
			identifier: selected.data()[0],
			form: JSON.stringify($("#trabajador_new").autoNumeric('getArray'))
		},
		function(data, satus) {
			//Refresh DataTable
			if (data.swal.type == "success") {
				dTable.draw(false);
				//LoadLogList(result["loglist"]);
			}
			//Display Modal Info
			swal.fire(data.swal);
            $("#ez-Modal").modal('hide');
			//$( "#ez-Modal-Body" ).html( "<div class='alert alert-"+result["stat"]+"' role='alert'>"+result["desc"]+"</div>" );
			//$( ".modal-footer" ).html('<button type="button" class="btn btn-link closemodal">Cerrar</button>');
		}, 'json');
	}
});

//Delete
$(document).off('click', '#ccg-delete').on('click', '#ccg-delete', function(){
	var dtRows = dTable.rows( { selected: true } );
	var dtTotalRows = dtRows.count();
	if (dtTotalRows == 0) {
		swal.fire("No ha seleccionado ningún registro.");
		return;
	}
	swal.fire({
		title: "Eliminar registros",
		text: '¿Está seguro que desea eliminar '+dtTotalRows+' registro(s)?',
		type: "info",
		showCloseButton: true,
		showCancelButton: true,
		confirmButtonText: 'Eliminar registro(s)',
		cancelButtonText: 'Volver'
	}).then(function () {
		//Build Array
		var dtSelectedRows = dtRows.data().toArray();
		var dtSelectedIDs = [];
		$.each(dtSelectedRows, function( index, value ) {
			dtSelectedIDs.push(value[0]);
		})
		//Send Request
		$.post(`${path}/`,
		{
			fn: 'delete',
			list: JSON.stringify(dtSelectedIDs)
		},
		function(data) {
			if (data.stat == "success") {
				dtRows.ajax.reload();
			}
			swal.fire({
				text: data.desc,
				type: data.stat
			});
		}, "json");
	}).catch(swal.noop);
});


//GRID: Trabajador Events Modal
$(document).off('click', '#element-worker-events').on('click', '#element-worker-events', function() {
	selected = dTable.row($(this).closest('tr'));
	
	//Send Request
	$.post(`${path}/`,
	{
		fn: 'events-form',
		identifier: selected.data()[0]
	},
	function(data) {
		var DPConfig = function() {
			$(this).find(".event-date").datetimepicker({
				locale: 'es',
				format: 'YYYY-MM-DD',
				viewMode: 'days',
				defaultDate: new Date()
			});
			$(this).find("#event-remove").click(function() {
				$(this).closest("tr").remove();
			});
		};
		//Carga de modal
		$( "#ez-Modal-Title" ).html( "<strong>Gestión de fechas:</strong> "+selected.data()[2] );
		$( "#ez-Modal-Body" ).html( data );
		$( ".modal-footer" ).html(	'<button id="event-save" type="button" class="btn btn-primary" style="margin-right: 15px;"><span class="fa fa-floppy-o" aria-hidden="true"></span> Guardar cambios</button>'+
									'<button type="button" class="btn btn-link closemodal">Cerrar</button>');
		$( ".modal-footer" ).show();
		$( "#ez-Modal" ).modal( 'show' );
		//Inicialización de campos
		$("#events-listdiv tbody tr").each(DPConfig);
		//Añadir
		$("#event-add").click(function() {
			$("#evento-template tr").clone().appendTo("#events-listdiv tbody").each(DPConfig);
		});
		//Guardar
		$("#event-save").click(function() {
			var arrEvents = [];
			
			//Build bonos array
			var arrEvents = new Object();
			var count = 0;
			$("#events-listdiv tbody tr").each(function() {
				var row = new Object();
				row["event-type"] = $(this).find(".event-type").val();
				row["event-date"] = $(this).find(".event-date").val();
				arrEvents[count] = row;
				count++;
			});
			$.post(`${path}/`,
			{
				fn: 'events-save',
				identifier: selected.data()[0],
				events: JSON.stringify(arrEvents)
			},
			function(data) {
				swal.fire({
					text: data.text,
					type: data.type
				});
			}, 'json');
		});
	});
	
});

function ToggleTrabajador() {
	var fecha;
	if (selected.data()[3] == '1') {
		fecha = $( "#ez-Modal-Body" ).find('#disable-date').val();
	} else {
		var d = new Date();

		var month = d.getMonth()+1;
		var day = d.getDate();

		fecha = d.getFullYear() + '-' +
			(month<10 ? '0' : '') + month + '-' +
			(day<10 ? '0' : '') + day;
	}
	$.post(`${path}/`,
	{
		fn: "formtrabajador-toggle",
		identifier: selected.data()[0],
		fecha: fecha
	},
	function(data, satus){
		var result;
		result = JSON.parse(data);
		if(result["stat"] == "danger") {
			swal.fire({text: result["desc"], type: result["stat"]});
			return;
		}
		if (result["val"] == "0" || result["val"] == "1" || result["stat"] == "success") {
			$( "#ez-Modal" ).modal( 'hide' );
			swal.fire({text: result["desc"], type: result["stat"]});
			dTable.draw(false);
		}
	});
}

//FORM Trabajador: Cargo CRUD
//Cargo CRUD :: In
$(document).off('click', '#cargo-edit').on('click', '#cargo-edit', function(){
	$("#cargo-select-name").trigger("change");
	$("#trabajador_new").fadeOut("fast" , function(){
		$("#cargo_form").fadeIn("fast");
		$( ".modal-footer" ).hide();
	});
	
});
//Cargo CRUD :: Out
$(document).off('click', '#cargo-back').on('click', '#cargo-back', function(){
	$("#cargo_form").fadeOut("fast", function(){
		$("#trabajador_new").fadeIn("fast");
		$( ".modal-footer" ).show();
	});
});
//Cargo CRUD :: Change
$(document).off('change', '#cargo-select-name').on('change', '#cargo-select-name', function(){
	if (!$(this).val()) return;
	$.post(`${path}/`,
	{
		fn: "data-cargo",
		identifier: $(this).val()
	},
	function(data, satus){
		//Get POST Data
		var result;
		result = JSON.parse(data);
		$("#cargo-name").val(result["nombre"]);
		$("#cargo-description").html(result["descripcion"]);
	});
});
//Cargo CRUD :: ReloadCombos
function ReloadCargoCombos() {
	$.post(`${path}/`,
	{
		fn: "combodata-cargo"
	},
	function(data, satus){
		$( "#cargo" ).html(data);
		$( "#cargo-select-name" ).html(data).change();
	});
}
//Cargo CRUD :: Update
$(document).off('click', '#cargo-action-update').on('click', '#cargo-action-update', function(){
	$.post(`${path}/`,
	{
		fn: "update-cargo",
		identifier: $('#cargo-select-name').val(),
		name: $('#cargo-name').val(),
		description: $('#cargo-description').html()
	},
	function(data, satus){
		//Get POST Data
		var result;
		result = JSON.parse(data);
		//Add ROW to DataTable
		if (result["stat"] == "success") {
			ReloadCargoCombos();
		}
		//Display Modal Info
		swal.fire(result["title"], result["desc"], result["stat"]);
	});
});
//Cargo CRUD :: Add
$(document).off('click', '#cargo-action-add').on('click', '#cargo-action-add', function(){
	$.post(`${path}/`,
	{
		fn: "add-cargo",
		identifier: $('#cargo-select-name').val(),
		name: $('#cargo-name').val(),
		description: $('#cargo-description').html()
	},
	function(data, satus){
		//Get POST Data
		var result;
		result = JSON.parse(data);
		//Add ROW to DataTable
		if (result["stat"] == "success") {
			ReloadCargoCombos();
		}
		//Display Modal Info
		swal.fire(result["title"], result["desc"], result["stat"]);
	});
});
//Cargo CRUD :: Delete
$(document).off('click', '#cargo-action-delete').on('click', '#cargo-action-delete', function(){
	$.post(`${path}/`,
	{
		fn: "delete-cargo",
		identifier: $('#cargo-select-name').val()
	},
	function(data, satus){
		//Get POST Data
		var result;
		result = JSON.parse(data);
		//Add ROW to DataTable
		if (result["stat"] == "success") {
			ReloadCargoCombos();
		}
		//Display Modal Info
		swal.fire(result["title"], result["desc"], result["stat"]);
	});
});


//FORM Trabajador: Mirror Date Values
$(document).off('focusout', '.mirror').on('focusout', '.mirror', function(){
	var id;
	var val;
	id = $(this).attr("id");
	val = $(this).val();
	$("#"+id+"mirror").val(val);
});

//FORM Trabajador: Change Pais
$(document).off('change', '#region').on('change', '#region', function() {
    if ($("#provincia option:selected").data().region_id != $("#region").val()) {
        $("#provincia").val('');
    }
    $("#provincia option").css('display', '').each(function() {
        if (Object.keys($(this).data()).length && $(this).data().region_id != $("#region").val()) {
            $(this).css('display', 'none');
        }
    });
    $("#provincia").change();
});

//FORM Trabajador: Change Provincia
$(document).off('change', '#provincia').on('change', '#provincia', function() {
    if ($("#comuna option:selected").data().provincia_id != $("#provincia").val()) {
        $("#comuna").val('');
    }
    $("#comuna option").css('display', '').each(function() {
        if (Object.keys($(this).data()).length && $(this).data().provincia_id != $("#provincia").val()) {
            $(this).css('display', 'none');
        }
    });
});

//FORM Trabajador: Change Nacionalidad
$(document).off('change', '#nacionalidad').on('change', '#nacionalidad', function(){
	if($(this).val() == '46'){
		$("#ciudadorigen").parent().parent().parent().slideUp();
	} else {
		$("#ciudadorigen").parent().parent().parent().slideDown();
	}
});

//FORM Trabajador: Change tipo contrato
$(document).off('change', '#contrato').on('change', '#contrato', function(){
	if($(this).val() == '1'){
		$("#contrato-hito").parent().parent().parent().slideUp();
		$("#contrato-fin").parent().parent().parent().slideDown();
	} else if($(this).val() == '5') {
		$("#contrato-fin").parent().parent().parent().slideDown();
		$("#contrato-hito").parent().parent().parent().slideDown();
	} else {
		$("#contrato-hito").parent().parent().parent().slideUp();
		$("#contrato-fin").parent().parent().parent().slideUp();
	}
});



//FORM Trabajador: Change Forma de Pago
$(document).off('change', '#formapago').on('change', '#formapago', function(){
	var fpval = $(this).val();
	$(".fp").fadeOut("fast").promise().done(function(){
		if(fpval == 1) {
			$("#fp-1").fadeIn("fast");
		}
		if(fpval == 3) {
			$("#fp-3").fadeIn("fast");
		}
		if(fpval == 4) {
			$("#fp-4").fadeIn("fast");
		}
	});
});

var tCCs, tCargas, tBonos;

//Initialize Worker FIELDS
function InitWorkerFields() {
	//INIT FIELDS
    LoadLogList($('#dhistorial pre').html());

    $("#region").change();
    
	//Cargas Familiares
	tCargas = dtInit("#cargas", {
		paging: false,
		ordering: false,
		searching: false,
        autoWidth: false,
        bInfo: false
	});

	//Centros de Costos
	tCCs = dtInit("#centroscostos", {
		paging: false,
		ordering: false,
		searching: false,
        autoWidth: false,
        bInfo: false
	});
    
	//Bonos
	tBonos = dtInit("#bonos", {
		paging: false,
		ordering: false,
		searching: false,
        autoWidth: false,
        bInfo: false
	});

	//Email
	$('#email').focusout(function() {
		if ($(this).val() && $(this).data().invalid != $(this).val() && !validEmail($(this).val())) {
			$(this).data().invalid = $(this).val();
			swal.fire({
				type: 'warning',
				text: 'Correo inválido'
			}).then((result) => {
				if (result) {
					$('#email').focus();
				}
			});
			

		}
	});
	
	//WYSIWYG
	$('#cargo-description').wysiwyg();
	
	//Datetime Pickers
	$('#contrato-fin').datetimepicker({
		locale: 'es',
		format: 'YYYY-MM-DD',
		viewMode: 'months'
	});
	$('#datenacimiento').datetimepicker({
		locale: 'es',
		format: 'YYYY-MM-DD',
		viewMode: 'years'
	});
	$('#dateingreso').datetimepicker({
		locale: 'es',
		format: 'YYYY-MM-DD',
		viewMode: 'months'
	});
	//autoNumerics
	var moneda = {
		mDec: '0',
		aSep: '.',
		aDec: ',',
		aPad: false,
		lZero: 'deny',
		wEmpty: 'zero'
	};
	$('#rut-s').autoNumeric('init', {
		lZero: 'deny',
		aSep: '.',
		aDec: ',',
		mDec: '0',
		vMax: 99999999,
		wEmpty: 'zero'
	});
	$('#sueldobase').autoNumeric('init', moneda);
	$('#sueldoquincena').autoNumeric('init', moneda);
	$('#horaslunvie').autoNumeric('init', {
		lZero: 'deny',
		aSep: '.',
		aDec: ',',
		mDec: '0',
		vMin: '0',
		vMax: '24',
		wEmpty: 'zero'
	});
	$('#horassabdom').autoNumeric('init', {
		lZero: 'deny',
		aSep: '.',
		aDec: ',',
		mDec: '0',
		vMin: '0',
		vMax: '24',
		wEmpty: 'zero'
	});
	$('#bonoasistencia').autoNumeric('init', moneda);
	$('#bonomovilizacion').autoNumeric('init', moneda);
	$('#bonocolacion').autoNumeric('init', moneda);
	
	$('#isapreadicional').autoNumeric('init', {
		lZero: 'deny',
		wEmpty: 'zero',
		mDec: 3
	});
	$('#apvuf').autoNumeric('init', {
		lZero: 'deny',
		wEmpty: 'zero'
	});
	$('#apvporcentaje').autoNumeric('init', {
		lZero: 'deny',
		wEmpty: 'zero'
	});
	$('#apvpactado').autoNumeric('init', moneda);
	$('#ccaf').autoNumeric('init', {
		lZero: 'deny',
		wEmpty: 'zero'
	});
	
	//Validator
	$("#trabajador_new").validator();
	
	//Gratificacion
	$("#gratificacion-mode").change(function() {
		if ($(this).val() == 3) {
			$("#gratificacionlegal").prop("readonly", false);
			$('#gratificacionlegal').autoNumeric('init', {
				lZero: 'deny',
				wEmpty: 'zero',
				mDec: '0',
				vMin: '0'
			});
			$('#gratificacionlegal').focus();
		} else {
			$("#gratificacionlegal").prop("readonly", true);
			$('#gratificacionlegal').autoNumeric('destroy');
			$("#gratificacionlegal").val("");
		}
	}).change();
	
	//Bootstrap Tooltip
	$('[data-toggle="tooltip"]').tooltip();
}

// 
// ASSIST
// 

//ASSIST MODAL: Control de asistencia
 $(document).off('click', '#element-worker-assist').on('click', '#element-worker-assist', function(){
	selected = dTable.row($(this).closest('tr'));
	$.post(`${path}/`,
	{
		fn: "assist",
		identifier: selected.data()[0]
	},
	function(data, satus){
		$( "#ez-Modal-Title" ).html( 'Asistencia' );
		$( "#ez-Modal-Body" ).html( data );

		$('#element-assist-currentdate').datetimepicker({
			locale: 'es',
			format: 'YYYY-MM',
			viewMode: 'months',
			useCurrent: false,
			defaultDate: new Date(),
			maxDate: new Date()
		});
		$("#element-assist-currentdate").on("dp.change", function(e) {
			if ($(this).data().last != $(this).val()) {
				$("#element-assist-currentdate").blur();
				asSelMonth($(this).val());
			}
		});
		
		$(".time").datetimepicker({
			locale: 'es',
			format: 'LT',
			showClear: true
		});
		
		$( ".modal-footer" ).html(	'<button id="modal-assist-save" type="button" class="btn btn-primary"  style="margin-right: 15px;"><span class="fa fa-floppy-o" aria-hidden="true"></span> Guardar</button>'+
								' o <button type="button" class="btn btn-link closemodal">Cerrar</button>');
		$( ".modal-footer" ).show();
		$("#ez-Modal").modal({backdrop: 'static', keyboard: false});
		$("#ez-Modal").modal( 'show' );
	});
});
//ASSIST MODAL: Seleccion mes
function asSelMonth(date) {

	var changeFunc = function(){
		$.post(`${path}/`,
		{
			fn: "assist",
			date: date,
			identifier: selected.data()[0]
		},
		function(data, satus){
			$("#element-assist-currentdate").data().last = date;
			$( "#element-assist" ).html( data );
			
			$(".time").datetimepicker({
				locale: 'es',
				format: 'LT',
				showClear: true
			});
		});
	}
	if (EditFlag()) {
		swal.fire({
			confirmButtonText: "Confirmar",
			cancelButtonText: "Cancelar" ,
			title: "¿Está seguro que desea cambiar de mes?",
			text: "Los cambios que ha realizado no serán guardados.",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
			confirmButtonText: "Ir de todos modos"
		}).then((result) => {
			if (result) {
				changeFunc();
			}
		}, (dismiss) => {
			if (dismiss === 'cancel') {
				$("#element-assist-currentdate").val($("#element-assist-currentdate").data().last);
			}
		});
	} else {
		changeFunc();
	}
}

//ASSIST MODAL: Toggle Weeks
$(document).off('click', ".assist-week-header").on('click', ".assist-week-header", function() {
	if($(this).hasClass("active")) {
		$("."+$(this).attr("weektable")).slideToggle();
	} else {
		$(".assist-week-table").slideUp();
		$("."+$(this).attr("weektable")).slideDown();
		$(".assist-week-header").removeClass("active");
		$(this).addClass("active");
	}
});

//ASSIST MODAL: Marcar horas editadas
$(document).off('focusout', '.time').on('focusout', '.time', function(event) {
	$(this).addClass("edited");
	$(this).parent().parent().parent().addClass("edited");
});
//ASSIST MODAL: Marcar horas editadas
$(document).off('focusout', '.cash').on('focusout', '.cash', function(event) {
	$(this).addClass("edited");
	$(this).parent().parent().parent().parent().addClass("edited");
});
//ASSIST MODAL: Medio día
$(document).off('focusin', '.time').on('focusin', '.time', function(event) {
	status = $(this).parent().parent().parent().find(".dropdown-toggle").attr('status');
	index = $(this).parent().parent().parent().find(".time").index(this);
	$(this).parent().parent().parent().find(".time").each(function( indexcol ) {
		if ((index >= 2 && index <= 3) && (indexcol >= 0 && indexcol <= 1) && status == "F2") {
			$(this).data("DateTimePicker").clear();
			$(this).addClass("edited");
		}
		else if ((index >= 0 && index <= 1) && (indexcol >= 2 && indexcol <= 3) && status == "F2") {
			$(this).data("DateTimePicker").clear();
			$(this).addClass("edited");
		}
	});
	event.stopPropagation();
});
//ASSIST MODAL: Cambiar Control Día
$(document).off('click', '.time-control').on('click', '.time-control', function() {
	var button = $(this).parent().parent().parent().find("input");
	var row = $(this).parent().parent().parent().parent().parent();
	
	button.removeClass (function (index, css) {
		return (css.match (/(^|\s)btn-\S+/g) || []).join(' ');
	});
	
	button.val( $(this).text() );
	var className = $.grep(this.className.split(" "), function(v, i){
	   return v.indexOf('bg') === 0;
	}).join();
	className = className.replace("bg", "btn");
	
	button.addClass(className);
	button.addClass("edited");
	row.addClass("edited");
	button.attr("status", $(this).attr("id"));
	if ($(this).attr("locked") == "1") {
		row.find(".time").each(function() {
			if ($(this).val() != "") {
				$(this).val("");
				$(this).addClass("edited");
			}
			$(this).prop("disabled", true);
		});
	} else {
		row.find(".time").prop("disabled", false);
	}
});

//ASSIST MODAL: Guardar
$(document).off('click', '#modal-assist-save').on('click', '#modal-assist-save', function() {
	if (EditFlag()) {
		//Generar tabla a enviar
		var days = new Object();
		$(".timerow").each(function( indexrow ) {
			if ($(this).hasClass("edited")) {
				var row = new Object();
				$( this ).find(".jcell").each(function( indexcol ) {
					//Relojes
					if ($(this).is(".time.edited")) {
						row[indexcol] = $(this).val();
					}
					//Estado
					if ($(this).is(".dropdown-toggle.edited")) {
						row[indexcol] = $(this).attr('status');
					}
					//Flujo
					if ($(this).is(".cash.edited")) {
						row[indexcol] = $(this).val();
					}
				});
				days[indexrow+1] = row;
			}
		});
		//Enviar datos mediante POST
		$.post(`${path}/`,
		{
			fn: "assist-save",
			year: $('#element-assist-currentdate').val().split("-")[0],
			month: $('#element-assist-currentdate').val().split("-")[1],
			identifier: selected.data()[0],
			jdays: JSON.stringify(days)
		},
		function(data, satus){
			if(data != false) {
				swal.fire("¡Éxito!", "Se han guardado los datos satisfactoriamente.", "success");
				dTable.draw(false);
				$("#ez-Modal .edited").removeClass("edited");
			}
		});
	} else {
		swal.fire("Un momento...", "No hay nada que guardar.", "warning");
	}
	
});

//
//	REMUN
//

//Remun: Open Modal
$(document).off('click', '#element-worker-remun').on('click', '#element-worker-remun', function(){
	selected = dTable.row($(this).closest('tr'));
	var d = new Date();
	$.post(pathSite+"api/ajax/files.r_remun.php",
	{
		fn: "MainModule"
	},
	function(data, satus){
		$( "#ez-Modal-Title" ).html( 'Remuneración' );
		$( "#ez-Modal-Body" ).html( data );

		$('#element-remun-currentdate').datetimepicker({
			locale: 'es',
			format: 'YYYY-MM',
			viewMode: 'months',
			useCurrent: false,
			defaultDate: new Date(),
			maxDate: new Date()
		});
		$("#element-remun-currentdate").on("dp.change", function(e) {
			if ($(this).data().last != $(this).val()) {
				$("#element-remun-currentdate").blur();
				remSelMonth($(this).val());
			}
		});
		
		//Cargar DATA en Modal
		$.post(pathSite+"api/ajax/files.r_remun.php",
		{
			fn: "ex-hours",
			identifier: selected.data()[0],
			date: $('#element-remun-currentdate').val(),
			year: d.getFullYear(),
			month: d.getMonth()+1
		},
		function(data, satus){
			//Resultados POST
			var result;
			result = JSON.parse(data);
			//Llenado de campos
			$( ".list-group-item-text" ).text(result['T141']);
			jQuery.each( result, function( i, val ) {
				$( "#" + i ).text( val );
			});
		});
		
		$( ".modal-footer" ).html('<button type="button" class="btn btn-link closemodal">Cerrar</button>');
		$( ".modal-footer" ).show();
		$("#ez-Modal").modal({backdrop: 'static', keyboard: false});
		$("#ez-Modal").modal( 'show' );
		
	});
});

//Remun: Print PDF
$(document).off('click', '#modal-remun-pdf').on('click', '#modal-remun-pdf', function(){
	submit_post_via_hidden_form(
		pathSite+"core/dompdf/liquidacion.php",
		{
			id: selected.data()[0],
			year: $('#element-remun-currentdate').val().split("-")[0],
			month: $('#element-remun-currentdate').val().split("-")[1],
		}
	);
});

//Remun: Click Month
function remSelMonth(date) {
	//var year = $('#element-remun-year');
	//var month = $(this);
	var changeFunc = function(){
		//Cargar DATA en Modal
		$.post(pathSite+"api/ajax/files.r_remun.php",
		{
			fn: "ex-hours",
			identifier: selected.data()[0],
			//year: year.val(),
			//month: month.attr('id'),
			date: date
		},
		function(data, satus){
			if (data.id == -1) {
				swal.fire({
					title: 'Ha seleccionado un mes anterior al ingreso del trabajador.',
					text: 'Ingreso del trabajador: '+data.val,
					type: 'warning'
				});
				return;
			}
			//$('#element-remun-currentdate').val( year.val()+"-"+month.attr('id') );
			//Llenado de campos
			$.each( data, function( i, val ) {
				$( "#" + i ).text( val );
			});
			//$( "#element-calendar" ).html('<span aria-hidden="true" class="glyphicon glyphicon-calendar"></span> ' + month.html() + " " + year.val() + ' <i class="fa fa-caret-down"></i>');
		}, "json");
	}
	changeFunc();
}

$(document).off('click', '.remun-sel-month').on('click', '.remun-sel-month', function(){

	
});

//
//  FILES
//

//Files: Open Modal
$(document).off('click', '#element-worker-files').on('click', '#element-worker-files', function(){
	selected = dTable.row($(this).closest('tr'));
	var d = new Date();
	$.post(pathSite+"api/ajax/files.r_files.php",
	{
		fn: "MainModule",
		identifier: selected.data()[0],
	},
	function(data, satus){
		$( "#ez-Modal-Title" ).html( 'Archivos' );
		$( "#ez-Modal-Body" ).html( data );
		InitFolderModule();
		
		$( ".modal-footer" ).html('<button type="button" class="btn btn-link closemodal">Cerrar</button>');
		$( ".modal-footer" ).show();
		$("#ez-Modal").modal({backdrop: 'static', keyboard: false});
		$("#ez-Modal").modal( 'show' );
		
	});
});

//
//	ETC
//

//MODAL FORM: RUT - Only Numbers
$(document).off('keydown', '#rut-s').on('keydown', '#rut-s', function(e){
	// Allow: backspace, delete, tab, escape, enter
	if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
		 // Allow: Ctrl+A, Command+A
		(e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
		 // Allow: home, end, left, right, down, up
		(e.keyCode >= 35 && e.keyCode <= 40)) {
			 // let it happen, don't do anything
			 return;
	}
	// Ensure that it is a number and stop the keypress
	if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
		e.preventDefault();
	}
});
//MODAL FORM: RUT - Generate Verifier
$(document).off('input', '#rut-s').on('input', '#rut-s', function(){
	$("#rut-v").html(dv($("#rut-s").autoNumeric('get')));
	$("#rut").val($("#rut-s").autoNumeric('get')+"-"+$("#rut-v").html());
});

//MODAL FORM: PHONE - Only Numbers and "-"
$(document).off('keydown', '#phone').on('keydown', '#phone', function(e){
	// Allow: backspace, delete, tab, escape, enter
	if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 187, 189]) !== -1 ||
		 // Allow: Ctrl+A, Command+A
		(e.keyCode === 65 && (e.ctrlKey === true || e.metaKey === true)) || 
		 // Allow: home, end, left, right, down, up
		(e.keyCode >= 35 && e.keyCode <= 40)) {
			 // let it happen, don't do anything
			 return;
	}
	// Ensure that it is a number and stop the keypress
	if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
		e.preventDefault();
	}
});

//Check Exit
$(document).off('click', '#ez-Modal .closemodal').on('click', '#ez-Modal .closemodal', function () {
	
	if (EditFlag()) {
		swal.fire({
			confirmButtonText: "Salir",
			cancelButtonText: "Volver" ,
			title: "¿Está seguro que desea salir?",
			text: "Los cambios que ha realizado no serán guardados.",
			type: "warning",
			showCancelButton: true,
			confirmButtonColor: "#DD6B55",
		}).then((result) => {
			if (result) {
				$("#ez-Modal").modal('hide');
			}
		});
	} else {
		$("#ez-Modal").modal('hide');
	}
})

function EditFlag() {
	var editflag = false;
	$(".timerow").each(function( indexrow ) {
		if ($(this).hasClass("edited")) {
			editflag = true;
			return false;
		}
	});
	return editflag;
}

function submit_post_via_hidden_form(url, params) {
    var f = $("<form target='_blank' method='POST' style='display:none;'></form>").attr({
        action: url
    }).appendTo(document.body);

    for (var i in params) {
        if (params.hasOwnProperty(i)) {
            $('<input type="hidden" />').attr({
                name: i,
                value: params[i]
            }).appendTo(f);
        }
    }

    f.submit();

    f.remove();
}