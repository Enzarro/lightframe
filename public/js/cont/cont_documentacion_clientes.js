var dTable, selected;
var table = 'mantenedores';
var path = 'cont_documentacion_clientes';

//Initialize
$(document).ready(function(){
	//GRID: Load DataTables Configurations
	$.post(`${path}/dtdata`,
	{
		config: true,
		client: sessionStorage.getItem("client")
	},
	function(columnDefs) {
		//Load DataTables
		dTable = $('#mantenedores').DataTable({
			responsive: true,
			"serverSide": true,
			"ajax": {
				"url": `${path}/dtdata`,
				"type": 'POST',
				data: function ( d ) {
					return $.extend( {}, d, {
						client: sessionStorage.getItem("client")
					});
				}
			},
			"columnDefs": columnDefs,
			select: {
				style:    'multi',
				selector: 'td:last-child'
			},
			"order": [
				[ 0, "desc" ]
			],
            language: dtSpanish,
			"initComplete": function(settings, json) {
				//Format search box
				// $("#dataTables-ccg_filter").find("input").wrap("<div class='input-group'></div>");
				// $("#dataTables-ccg_filter").find(".input-group").prepend("<span class='input-group-addon'><span class='fa fa-search text-center' aria-hidden='true'></span></span>");
				// $("#dataTables-ccg_filter").find("input").css("margin", "0");
			}
		});
		
	}, "json");
	
	
	// get_permissions();
});
function InitFields() {
	
}

// --- MAIN FUNCTIONS ---

$(document).on('client-change', function() {
    dTable.ajax.reload();
});

//Modal Save
$(document).off('click', '#modal-save').on('click', '#modal-save', function() {
	if (fileSelected === null) {
		saveFolder();
	} else {
		saveFile();
	}
});

//New folder
$(document).off('click', '#ccg-new').on('click', '#ccg-new', function(){
	selected = null;
	$.post(`${path}/form`,
	{
		client: sessionStorage.getItem("client")
	},
	function(data) {
		$(".modal-body").html(data);
		InitFields();
		fileSelected = null;
		$(".modal").modal("show");
	});
});

//Edit folder
$(document).off('click', '#ccg-edit').on('click', '#ccg-edit', function(){
    selected=undefined;
	selected = dTable.row($(this).closest('tr'));
	selected = selected.data()['id'];
	openFolder();
});
//Open folder
function openFolder() {
	$.post(`${path}/form`,
	{
        id: selected,
        client: sessionStorage.getItem("client")
	},
	function(data) {
		$(".modal-body").html(data);
		LoadDTFiles(selected);
		InitFields();
		fileSelected = null;
		$(".modal").modal("show");
	});
}

//Save Folder
function saveFolder() {
	
	if ($('#nombre').val() == '') {
		swal.fire({type: 'warning', text: 'No ha determinado el nombre de la carpeta.'});
		return;
	}
	
	//Validation
	if ($(".modal").find("form").validator('validate').has('.has-error').length === 0) {
		if (selected == null) {
			//New
			$.post(`${path}/new`,
			{
                client: sessionStorage.getItem("client"),
				data: JSON.stringify($(".modal").find("form").serializeArray())
			},
			function(data) {
				if (data.type == "success") {
					dTable.order( [ 0, 'desc' ] );
					dTable.ajax.reload();
					$(".modal").modal("hide");
				}
				swal.fire(data);
			}, "json");
		} else {
			//Edit
			$.post(`${path}/update`,
			{
				client: sessionStorage.getItem("client"),
				id: selected,
				data: JSON.stringify($(".modal").find("form").serializeArray())
			},
			function(data) {
				if (data.type == "success") {
					dTable.ajax.reload();
					$(".modal").modal("hide");
				}
				swal.fire(data);
			}, "json");
		}
	} else {
		swal.fire({
			title: "Debe completar todos los campos obligatorios.",
			type: "warning"
		});
	}
}

//Delete folder
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
	}).then(function (res) {
		//Build Array
		if (res.value) {
			var dtSelectedRows = dtRows.data().toArray();
			var dtSelectedIDs = [];
			$.each(dtSelectedRows, function( index, value ) {
				dtSelectedIDs.push(value['id']);
			})
			//Send Request
			$.post(`${path}/delete`,
			{
				client: sessionStorage.getItem("client"),
				list: JSON.stringify(dtSelectedIDs)
			},
			function(data) {
				if (data.type == "success") {
					dtRows.ajax.reload();
				}
				swal.fire(data);
			}, "json");
		}
	}).catch(swal.noop);
});


// -- FILES --

//Load files from folder in DataTables table (via ajax)
function LoadDTFiles(id) {
	dTableArchivos = $('#dt-archivos').DataTable({
		ajax: {
			url: `${path}/dt_files_data`,
			type: 'POST',
			data: {
				client: sessionStorage.getItem("client"),
				id: id
			}
		},
		columnDefs: [
			{
				targets: 0,
				data: 'id',
				title: 'ID',
				visible: false,
				searchable: false
			},
			{
				targets: 1,
				data: 'nombre',
				title: 'Nombre',
				width: '150px'
			},
			{
				targets: 2,
				data: 'ext',
				title: 'Tipo',
				width: '75px',
				orderable: false,
				searchable: false
			},
			{
				targets: 3,
				data: 'file',
				title: 'Archivo',
				visible: false,
				searchable: false
			},
			{
				targets: 4,
				data: 'descripcion',
				title: 'Descripción'
			},
			{
				targets: 5,
				data: 'activo',
				title: 'Activo',
				width: '60px',
				orderable: false,
				searchable: false
			},
			{
				targets: 6,
				title: 'Acciones',
				defaultContent:
					`<div class="btn-group btn-group-justified" role="group" style="width: auto;">
						<div class="btn-group btn-group-sm" role="group">
							<button class="btn btn-default file-download" title="Descargar archivo" type="button"><span  class="fa fa-download"></span></button>
						</div>
						<div class="btn-group btn-group-sm" role="group">
							<button class="btn btn-default file-preview" title="Vista previa" type="button" ><span  class="fa fa-search"></span></button>
						</div>
						<div class="btn-group btn-group-sm" role="group">
							<button class="btn btn-success file-edit" title="Modificar" type="button" btnPermis="btn_editar"><span class="fa fa-pencil-alt"></span></button>
						</div>
					</div>`,
				orderable: false,
				searchable: false,
				width: '80px'
			},
			{
				targets: 7,
				className: "select-checkbox",
				defaultContent: "",
				orderable: false,
				searchable: false,
				title: '<span class="fa fa-trash text-center" aria-hidden="true"></span>',
				width: '16px'
			}
		],
		select: {
			style:    'multi',
			selector: 'td:last-child'
		},
		"order": [
			[ 0, "desc" ]
		],
		"initComplete": function(settings, json) {
			//Format search box
			// $("#dt-archivos_filter").find("input").wrap("<div class='input-group'></div>");
			// $("#dt-archivos_filter").find(".input-group").prepend("<span class='input-group-addon'><span class='fa fa-search text-center' aria-hidden='true'></span></span>");
			// $("#dt-archivos_filter").find("input").css("margin", "0");
		}
	});
}


//New file (reload Modal HTML)
$(document).off('click', '#file-new').on('click', '#file-new', function() {
	fileSelected = true;
	$.post(`${path}/dt_files_form`,
	function(data) {
		$(".modal-body").html(data);
		initFileFields();
	});
});

//Edit file (reload Modal HTML)
$(document).off('click', '.file-edit').on('click', '.file-edit', function() {
	fileSelected = dTableArchivos.row($(this).closest('tr')).data();
	$.post(`${path}/dt_files_form`,
	{
		id: fileSelected.id,
		client: sessionStorage.getItem("client")
	},
	function(data) {
		$(".modal-body").html(data);
		initFileFields();
	});
});

//Init file fields
function initFileFields() {
	$("#upload-file").click(function () {
		$("#upload-file").val('');
	});
	$("#upload-file").on("change.bs.fileinput", function(objEvent) {
		var objFile = $(this)[0].files[0];
		if ($.inArray(objFile.name.split('.').pop(), ['xls', 'xlsx', 'doc', 'docx', 'pdf']) == -1) {
			$(this)[0].value = '';
			if (fileSelected !== null) {
				$('#archivo').val(fileSelected.file);
			} else {
				$('#archivo').val('');
			}
			swal.fire({type: 'warning', title: 'Formato no permitido', text: 'Sólo se admiten los siguientes formatos: Excel, Word y PDF.'});
			return;
		}
		$('#archivo').val(objFile.name);
	});
}

//Download file
$(document).off('click', '.file-download').on('click', '.file-download', function() {
	var fs = dTableArchivos.row($(this).closest('tr')).data();
	var link = document.createElement('a');
	var sUrl = 'http://'+document.domain+"/uploader/"+fs.file;
	link.href = sUrl;
	var fileName = sUrl.substring(sUrl.lastIndexOf('/') + 1, sUrl.length);
	link.download = fileName;
	if (document.createEvent) {
		var e = document.createEvent('MouseEvents');
		e.initEvent('click' ,true ,true);
		link.dispatchEvent(e);
		return true;
	}
});

//Preview file
$(document).off('click', '.file-preview').on('click', '.file-preview', function() {
	var fileSelected = dTableArchivos.row($(this).closest('tr')).data();
	window.open('https://docs.google.com/a/'+document.domain+'/viewer?url=http://'+document.domain+"/uploader/"+fileSelected.file);
});

//Save File
function saveFile() {
	var objFormData = new FormData();
	// GET FILE OBJECT 
	var objFile = $('#upload-file')[0].files[0];
	
	//Validation
	if ($('#nombre').val() == '') {
		swal.fire({type: 'warning', text: 'No ha determinado el nombre del archivo.'});
		return;
	}
	
	// APPEND FILE TO POST DATA
	if (fileSelected !== true && fileSelected !== null) {
		//Edit
		objFormData.append('id', fileSelected.id);
	} else {
		if (objFile === undefined) {
			swal.fire({type: 'warning', text: 'No ha seleccionado ningún archivo.'});
			return;
		}
		//New
		objFormData.append('carpeta', selected);
	}
	objFormData.append('client', sessionStorage.getItem("client"));
	objFormData.append('nombre', $('#nombre').val());
	objFormData.append('descripcion', $('#descripcion').val());
	objFormData.append('userfile', objFile);
	objFormData.append('activo', $("#file-active").is(":checked"));
	
	$.ajax({
		url: `${path}/file_upload`,
		type: 'POST',
		contentType: false,
		data: objFormData,
		//JQUERY CONVERT THE FILES ARRAYS INTO STRINGS.SO processData:false
		processData: false,
		success: function(data) {
			data = JSON.parse(data);
			if (data.type == "success") {
				openFolder();
				dTable.ajax.reload();
			}
			swal.fire(data);
			
		}
	});
}

//Delete file
$(document).off('click', '#file-delete').on('click', '#file-delete', function(){
	var dtRows = dTableArchivos.rows( { selected: true } );
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
	}).then(function (res) {
		if (res.value) {
			//Build Array
			var dtSelectedRows = dtRows.data().toArray();
			var dtSelectedIDs = [];
			$.each(dtSelectedRows, function( index, value ) {
				dtSelectedIDs.push(value['id']);
			})
			//Send Request
			$.post(`${path}/file_delete`,
			{
				list: JSON.stringify(dtSelectedIDs),
				client: sessionStorage.getItem("client")
			},
			function(data) {
				if (data.type == "success") {
					dtRows.ajax.reload();
					dTable.ajax.reload();
				}
				swal.fire(data);
			}, "json");
		}
	}).catch(swal.noop);
});

//Back (reload Folder)
$(document).off('click', '#file-back').on('click', '#file-back', function() {
	openFolder();
});

// --- ADDITIONAL FUNCTIONS ---

//Check Exit
$(document).off('click', '.closemodal').on('click', '.closemodal', function () {
	$(".modal").modal('hide');
})