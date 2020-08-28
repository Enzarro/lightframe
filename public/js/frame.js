var btn_save_layer = "<i class='fas fa-spinner fa-spin'></i> Guardando...";
var loading_layer = "<div id='frm_loading' class='alert bg-success'><i class='fas fa-spinner fa-spin'></i> Cargando...</div>";
var loading_layer_txt = "<div id='frm_loading'><i class='fas fa-spinner fa-spin'></i> Cargando...</div>";
var loading_layer_small = "<i class='fas fa-spinner fa-spin'></i>";
var socket;

var swalLoadObj = {
	html: '<i class="fa fa-cog fa-spin"></i> cargando...',	
	title: '',
	showConfirmButton: false,
	allowOutsideClick: false,
	allowEscapeKey: false,
	allowEnterKey: false
}

if (typeof socket_enabled !== 'undefined' && socket_enabled) {
	if (typeof io !== 'undefined') {
		socket = io.connect(socket_address, {
			query: `system=${socket_system}&token=${$.cookie('token')}&timeout=${socket_timeout}&path=${window.location.pathname}`
		});

		socket.on('sql-error', function(data) {
			console.error(data.error);
			console.error(data.query);
		});
	} else {
		socket = {
			on: function(action, fn) {
				console.error(action, 'Unable to load socket.io file')
			}
		}
	}

} else {
	socket = {
		on: function(action, fn) {
			console.error(action, 'Socket disabled')
		}
	}
}

if (typeof dbstatus !== 'undefined' && dbstatus && $("#client-selector").length) {
	if (!(sessionStorage.getItem("client") == undefined || sessionStorage.getItem("client") == '')) {
        $("#client-selector").val(sessionStorage.getItem("client"));
	} else {
		var clientCount = $("#client-selector").find("option").filter(function() {
			return $(this).attr("value") > 0;
		}).length;
		if (clientCount > 1) {
			var clientPopup = $("#client-selector").clone();
			clientPopup.attr('id', 'client-selector-onempty');
			
			swal.fire({
				title: `<i class="fa fa-globe"></i> BIENVENIDO`,
				html: clientPopup,
				showConfirmButton: false,
				// showCloseButton: true,
				// allowOutsideClick: false
			}).then((result) => {
				if (result.dismiss == 'close') {
					sessionStorage.setItem("client", '');
				}
			});
	
			$("#client-selector-onempty").focus();
	
			$("#client-selector-onempty").selectpicker({
				liveSearch: true
			});
			$("#client-selector-onempty").change(function() {
				$("#client-selector").selectpicker('val', $(this).val());
				sessionStorage.setItem("client", $(this).val());
				$(document).trigger('client-change');
				swal.close();
			})
		} else {
			var firstClientVal = $("#client-selector").find("option").filter(function() {
				return $(this).attr("value") > 0;
			}).first().val();
			if (firstClientVal !== undefined) {
				$("#client-selector").val(firstClientVal);
				sessionStorage.setItem("client", firstClientVal);
			} else {
				sessionStorage.setItem("client", '');
			}
		}
	}
}

$("#client-selector").change(function() {
	sessionStorage.setItem("client", $(this).val());
	$(document).trigger('client-change');
});

$(document).ready(function() {
	$('html').fadeIn();
	$("#client-selector").selectpicker({
		liveSearch: true,
		dropdownAlignRight: true
	});
});

function getBase64(file) {
	return new Promise((resolve, reject) => {
	  const reader = new FileReader();
	  reader.readAsDataURL(file);
	  reader.onload = () => resolve(reader.result);
	  reader.onerror = error => reject(error);
	});
}

function formToFormData(form){
	var formdata = new FormData();
	$(form).find('*[name]').each(function() {
        if ($(this).attr('type') == 'checkbox') {
			formdata.append($(this).attr('name'), $(this).is(":checked")?1:0);
        } else if ($(this).attr('type') == 'file') {
			if($(this)[0].files[0]){
				if($(this).data("dropify").settings.defaultFile){
					formdata.append("linkname", $(this).data("dropify").settings.defaultFile);
				}
				formdata.append($(this).attr('name'), $(this)[0].files[0]);
				// var base64 = getBase64($(this)[0].files[0]).then(data => console.log(data));	
				// formdata.append($(this).attr('name'), base64);							
			}
		} else {
			if ($(this).data().fitype !== undefined) {
				if ($(this).data().fitype == 'anumeric') {
					formdata.append($(this).attr('name'), $(this).autoNumeric('get'));
				} else {
					formdata.append($(this).attr('name'), $(this).val()?$(this).val():null);
				}
			} else {
				formdata.append($(this).attr('name'), $(this).val()?$(this).val():null);
			}
        }
    });
    //Datatables array to textarea 
    $(form).find('*[data-fitype="dtable"]').each(function() {
        var data = $(this).find("table").DataTable().data().toArray();
        data = data.map(function(row) {
            let obj = {};
            for (let key of Object.keys(row)) {
                if (key) obj[key] = row[key];
            }
            return obj;
        });
	
		formdata.append($(this).attr('id'), data);
        // $(this).find("#data").val(JSON.stringify($(this).find("table").DataTable().data().toArray()));
	});

	return formdata
}

function formToObject(form) {
    //Object
    // var object = formArrayToObject($(form).serializeArray());
	var object = {};
    $(form).find('*[name]').each(function() {
        if ($(this).attr('type') == 'checkbox') {
			object[$(this).attr('name')] = $(this).is(":checked")?1:0;
        } else {
			if ($(this).data().fitype !== undefined) {
				if ($(this).data().fitype == 'anumeric') {
					object[$(this).attr('name')] = $(this).autoNumeric('get');
				} else {
					object[$(this).attr('name')] = $(this).val()?$(this).val():null;
				}
			} else {
				object[$(this).attr('name')] = $(this).val()?$(this).val():null;
			}
        }
    });
    //Datatables array to textarea 
    $(form).find('*[data-fitype="dtable"]').each(function() {
        var data = $(this).find("table").DataTable().data().toArray();
        data = data.map(function(row) {
            let obj = {};
            for (let key of Object.keys(row)) {
                if (key) obj[key] = row[key];
            }
            return obj;
        });
		object[$(this).attr('id')] = data;
        // $(this).find("#data").val(JSON.stringify($(this).find("table").DataTable().data().toArray()));
	});
	

    return object;
    // return formArrayToObject($(form).serializeArray());
}

function validateForm(form) {
	if ($(form).validator('validate').has('.has-error').length === 0) {
		if ($(form).find('*[data-fitype="dtable"]').length) {
			var tables = false;
			$(form).find('*[data-fitype="dtable"]').each(function() {
				if (validateTable($(this).find('table'))) {
					tables = true;
				}
			});
			if (tables) {
				swal.fire({
					text: "Hay cambios sin confirmar en subtablas.",
					type: "warning"
				});
			} else {
				return true;
			}
		} else {
			return true;
		}
		
	} else {
		swal.fire({
			text: "Debe completar todos los campos obligatorios.",
			type: "warning"
		});
	}
}

function formArrayToObject(array) {
	let final = {};
	for (let obj of array) {
		if (obj.name) {
			final[obj.name] = obj.value;
		}
	}
	return final;
}

function InitFormFields(form) {
    $(form).find('*[data-fitype]').each(function() {
		let data = $(this).data();
		if (data.fitype !== undefined) {

			//Ejecutar llamadas a funciones contenidas
			for(let index in data.fisettings) {
				if (/\((.*?)\)/.test(data.fisettings[index])) {
					data.fisettings[index] = eval(data.fisettings[index]);
				}
			}

			//Validar tipos de fi type
			if (data.fitype == "bselect") {
				$(this).selectpicker(data.fisettings);
			} else if (data.fitype == "dtpicker") {
				//if (data.fisettings.defaultDate !== undefined) data.fisettings.defaultDate = eval(data.fisettings.defaultDate);
				$(this).datetimepicker(data.fisettings);
			} else if (data.fitype == "anumeric") {
				$(this).autoNumeric('init', data.fisettings);
			} else if(data.fitype == "image"){
				var dropConfig = (data.fisettings !== undefined) ? data.fisettings : {};
				dropConfig['messages'] = {
					remove : "Eliminar imagen",
					default: "Click para seleccionar imagen",
					replace: "Click para reemplazar imagen",
					error : "Error: el archivo debe ser una imagen con extensión .jpg o .png"
				}
				dropConfig['allowedFileExtensions'] = ["png", "jpg", "jpeg"]
				dropConfig['error'] = {
					imageFormat: "Formato de imagen no permitido. Formato permitido: {{ value }}",
					fileExtension: "Extensiones Permitidas: {{ value }}"
				}
				$(this).dropify(dropConfig);
			} else if(data.fitype == "upfile"){
				var dropConfig = (data.fisettings !== undefined) ? data.fisettings : {};
				dropConfig['messages'] = {
					remove : "Eliminar documento",
					default: "Click para seleccionar documento",
					replace: "Click para reemplazar documento",
					error : "Error: el archivo debe ser una archivo con extensión .xls, .csv, .xlsx, .pdf, doc o docx"
				}
				dropConfig['allowedFileExtensions'] = ["xls", "xlsx", "pdf", "doc", "docx", "csv"]
				dropConfig['error'] = {
					imageFormat: "Formato de archivo no permitido. Formato permitido: {{ value }}",
					fileExtension: "Extensiones Permitidas: {{ value }}"
				}
				$(this).dropify(dropConfig);   
			} else if (data.fitype == "dtable") {
				var tableCfg = {
					paging: false,
					ordering: false,
					searching: false,
					autoWidth: false,
					// scrollX: true,
					bInfo: false
				};
				if (data.fisettings !== undefined) {
					tableCfg = data.fisettings;
				}
				tCCs = dtInit(`#${$(this).attr('id')}`, tableCfg);
			} else if (data.fitype == "rut") {
				$(this).keyup(function() {
					if ($(this).val().length > 1) {
						$(this).val(formatRut($(this).val()));
						if (validateRut($(this).val())) {
							$(this).css({'border-color': 'forestgreen'});
							$(this).data('valid', true);
						} else {
							$(this).css({'border-color': '#a94442'});
							$(this).data('valid', false);
						}
					} else {
						$(this).css({'border-color': ''});
						$(this).data('valid', false);
					}
				}).keyup();
			}
		}
	});
}

function dtInit(domid, opts) {
	//Cargas Familiares
	var dtConfig = {
		paging: false,
		ordering: false,
		searching: false,
		autoWidth: false,
		bInfo: false,
		data: JSON.parse($(domid).find('#data').val()),
		columnDefs: JSON.parse($(domid).find("#config").text()),
		drawCallback: function(settings) {
			dtEditDrawCallback(settings);
		},
		language: dtSpanish
	};
	if (opts) {
		for (let key of Object.keys(opts)) {
			dtConfig[key] = opts[key];
		}
	}

    var dtObj = $(domid).find('table');
	dtObj.on('preInit.dt', function() {
        var tableID = $(this).attr('id');
        //Format search box
        $(`#${tableID}_filter`).find("input").wrap(`<div class='input-group'></div>`);
        $(`#${tableID}_filter`).find(".input-group").prepend(`<span class='input-group-addon'><span class='glyphicon glyphicon-search text-center' aria-hidden='true'></span></span>`);
        $(`#${tableID}_filter`).find("input").css("margin", "0");
    }).DataTable(dtConfig);
	$(domid).find("#agregar").click(function() {
		//Fila vacía desde textarea
		var emptyrow = JSON.parse($(domid).find("#emptyrow").text());
		//Toda la data para obtener orden mas alto
		if (emptyrow.orden !== undefined) {
			var totaldata = dtObj.DataTable().data().toArray().map(function(row) {
				return row.orden;
			})
			if (totaldata.length) {
				emptyrow.orden = Math.max(...totaldata) + 1;
			} else {
				emptyrow.orden = 1;
			}
		}

		var newrow = dtObj.DataTable().row.add(emptyrow);
		dtObj.DataTable().draw(); 
		if ($(this).data('callback') !== undefined) {
			let fname = $(this).data('callback');
			window[fname](newrow);
		}
	});
	$(domid).find("#apply-all").click(function() {
		dtObj.DataTable().rows().every( function ( rowIdx, tableLoop, rowLoop ) {
			var row = this.node();
			$(row).find("button.apply").click();
		});
	});
	return dtObj;
}

function dtColumnsToInputs(columns, row, extract = false) {
	var rowObj = row.node();
	var data = row.data()
	var colsResult = {};
	for (let key in columns) {
		let res
		if (data.fixed !== undefined && data.fixed == true && columns[key].editConfig !== undefined && columns[key].editConfig.blockFixed !== undefined && columns[key].editConfig.blockFixed == true) {
			//Fila fija, columna fija
		} else {
			res = dtInitInput({
				columns: columns, 
				row: row,
				cell: columns[key],
				extract: extract
			});
		}
		
		if (res !== undefined) {
			if (extract) {
				colsResult[columns[key].data] = res;
			} else {
				colsResult[key] = res;
			}
		}
		
	}
	// console.log(colsResult);
	return colsResult;
}

function dtInitInput(data) {
	var rowObj = data.row.node();

	//Remove fixed config parameter
	if (data.cell.editConfig !== undefined && data.cell.editConfig.blockFixed !== undefined) {
		delete data.cell.editConfig.blockFixed;
	}
	//Class
	if (data.cell.editClass !== undefined) {
		data.cell.editClass = `form-control ${data.cell.editClass}`;
	} else {
		data.cell.editClass = `form-control`;
	}
	//RUT
	var input;
	if (data.cell.editType == 'rut') {
		if (data.extract) {
			return $(data.cell.cellNode).find('input').val();
		} else {
			input = $(`<input name="${data.cell.data}" type="text" class="${data.cell.editClass}" style="width: 100%;">`);
			input.val(data.cell.value);
			
			input.keyup(function() {
				if ($(this).val().length > 1) {
					$(this).val(formatRut($(this).val()));
					if (validateRut($(this).val())) {
						$(this).css({'border-color': 'forestgreen'});
						$(this).data('valid', true);
					} else {
						$(this).css({'border-color': '#a94442'});
						$(this).data('valid', false);
					}
				} else {
					$(this).css({'border-color': ''});
					$(this).data('valid', false);
				}
			}).keyup();
			//input.autoNumeric();
			if (data.cell.editCallback !== undefined) {
				window[data.cell.editCallback](input);
			}
			$(data.cell.cellNode).empty().append(input);
		}
	}
	//ANUMERIC
	if (data.cell.editType == 'anumeric') {
		if (data.extract) {
			return $(data.cell.cellNode).find('input').autoNumeric('get');
		} else {
			input = $(`<input name="${data.cell.data}" type="text" class="${data.cell.editClass}" style="width: 100%;">`);
			input.val(data.cell.value);
			if (data.cell.editConfig !== undefined) {
				input.autoNumeric(data.cell.editConfig);
			} else {
				input.autoNumeric();
			}
			input.on('keypress',function(e) {
				if(e.which == 13) {
					$(rowObj).find('.apply').click();
				}
			});
			if (data.cell.editCallback !== undefined) {
				window[data.cell.editCallback](input);
			}
			$(data.cell.cellNode).empty().append(input);
		}
	}
	//STRING
	if (data.cell.editType == 'string') {
		if (data.extract) {
			return $(data.cell.cellNode).find('input').val();
		} else {
			input = $(`<input name="${data.cell.data}" type="text" class="${data.cell.editClass}" style="width: 100%;">`);
			input.val(data.cell.value);
			input.on('keypress',function(e) {
				if(e.which == 13) {
					$(rowObj).find('.apply').click();
				}
			});
			input.on('keyup', function(e) {
				if(e.which == 27) {
					$(rowObj).find('.undo').click();
				}
			});
			if (data.cell.editCallback !== undefined) {
				window[data.cell.editCallback](input);
			}
			$(data.cell.cellNode).empty().append(input);
		}
	}
	//DATE
	if (data.cell.editType == 'dtpicker' || data.cell.editType == 'drpicker') {
		if (data.extract) {
			return $(data.cell.cellNode).find('input').val();
		} else {
			input = $(`<input name="${data.cell.data}" type="text" class="${data.cell.editClass}" style="width: 100%;">`);
			input.val(data.cell.value);
			if (data.cell.editConfig == undefined) {
				data.cell.editConfig = {};
			}
			data.cell.editConfig.debug = true;
			if (data.cell.editType == 'dtpicker') input.datetimepicker(data.cell.editConfig);
			if (data.cell.editType == 'drpicker') input.daterangepicker(data.cell.editConfig);
			input.on('keypress',function(e) {
				if(e.which == 13) {
					$(rowObj).find('.apply').click();
				}
			});
			if (data.cell.editCallback !== undefined) {
				window[data.cell.editCallback](input);
			}
			$(data.cell.cellNode).empty().append(input);
		}
	}
	//SELECT
	if (data.cell.editType == 'select' | data.cell.editType == 'bselect' | data.cell.editType == 'eselect') {
		if (data.extract) {
			return $(data.cell.cellNode).find('select').val();
			// console.log(colsResult[cell.data]);
		} else {
			input = $(`<select name="${data.cell.data}" class="${data.cell.editClass}"></select>`);
			if (data.cell.editData !== undefined) {
				input.html(buildSelectValuesFIObject(data.cell.editData));
			}
			//Multiple
			if (data.cell.editConfig !== undefined) {
				if (data.cell.editConfig.multiple !== undefined && data.cell.editConfig.multiple == true) {
					input.attr('multiple', true);
				}
			}
			//Setear valor
			input.val(data.cell.value);
			if (data.cell.editCallback !== undefined) {
				window[data.cell.editCallback](input);
			}
			$(data.cell.cellNode).empty().append(input);
			//BSELECT
			if (data.cell.editType == 'bselect') {
				if (data.cell.editConfig !== undefined) {
					if(data.cell.editConfig.addCallback!==undefined){
						data.cell.editConfig.header=`<button onclick="${data.cell.editConfig.addCallback}(this);" type="button" class="btn btn-primary btn-xs btn-block"><i class="fa fa-plus" aria-hidden="true"></i> Nuevo</button>`;
						input.selectpicker(data.cell.editConfig).on('loaded.bs.select', function(e) {
							input.parent().find(".bs-searchbox").find("input").addClass("input-sm");
							input.parent().find(".popover-header").css({padding: '1px'}).find(".close").remove();
						})
					}else{
						input.selectpicker(data.cell.editConfig);
					}
				} else {
					input.selectpicker();
				}
				if (data.cell.editConfig.height !== undefined) {
					input.closest('.bootstrap-select').css({'height': data.cell.editConfig.height});
					input.next().css({'height': data.cell.editConfig.height});
				}
			}
			//ESELECT -- Edit Select
			if (data.cell.editType == 'eselect') {
				var button = $(`<div class="input-group-append">
					<button type="button" class="btn btn-success"><i class="fa fa-cog"></i></button>
				</div>`);
				button.click(function() {

					swal.fire({
						html: 'test',
						showCancelButton: true,
						confirmButtonText: 'Ok',
						cancelButtonText: 'Cancelar'
					}).then((result) => {
						if (result.value) {
							console.log(formToObject("#swal2-content form"));
						}
					});
					
					var rowData = data.row.data();
					var html = $(`<form></form>`);
					for (let key in data.columns) {
						//Si columna recorrida está en arreglo de opción
						if (data.cell.editConfig.efields[input.val()].indexOf(data.columns[key].data) != -1) {
							// var swalInput = dtInitInput()
							var frmGrp = $(`<div class="form-group">
								<label for="${data.columns[key].data}">${data.columns[key].title}</label>
							</div>`);

							// var newitem = dtInitInput({
							// 	columns: data.columns,
							// 	row: data.row,
							// 	cell: data.columns[key]
							// });
							// console.log(data.columns[key])
							// frmGrp.append(newitem.clone())

							frmGrp.append($(data.columns[key].cellNode).clone(true))
							

							html.append(frmGrp)
						}
					}

					$('#swal2-content').empty().append(html);
					
					// swal.fire({
					// 	html: `<pre>${JSON.stringify(data)}</pre>`
					// });
				});
				
				input.wrap(`<div class="input-group"></div>`);
				input.closest('.input-group').append(button);
			}
		}
	}
	//CHECKBOX
	if (data.cell.editType == 'checkbox') {
		if (data.extract) {
			return $(data.cell.cellNode).find('input').is(":checked");
		} else {
			input = $(`<input type="checkbox" style="width: 100%; height: 20px;"></input>`);
			if (data.cell.value) {
				input.attr('checked', true);
			}
			if (data.cell.editCallback !== undefined) {
				window[data.cell.editCallback](input);
			}
			$(data.cell.cellNode).empty().append(input);
		}
	}
	return input;
}

function dtRefreshColumn(tblObject, column) {
	var settings = tblObject.dataTable().fnSettings();
	tblObject.DataTable().rows().every( function ( rowIdx, tableLoop, rowLoop ) {
		var row = this;
		var data = row.data();
		var columns = settings.aoColumns;
		if (data.estado == 'editing') {
			//Inicializar campos
			for (let key in columns) {
				if (columns[key].editType !== undefined && columns[key].data==column) {
					var initColumn = {
						title: columns[key].title,
						data: columns[key].data,
						editType: columns[key].editType,
						editConfig: columns[key].editConfig,
						editClass: columns[key].editClass,
						editCallback: columns[key].editCallback,
						editData: columns[key].editData,
						cellNode: tblObject.DataTable().cell(row, key).node()
					};

					//guardar valor seleccionado del campo de forma temporal
					initColumn.value = dtInitInput({
						row: row,
						cell: initColumn,
						extract: true
					});
					//bajar el input
					$(tblObject.DataTable().cell(row, key).node()).html("");
					//subir el input y setear el valor
					dtInitInput({
						row: row,
						cell: initColumn
					});
				}
			}
		}
	});
}

function dtEditDrawCallback(settings) {
	//Por cada objeto fila DataTables
	var tblObject = $(settings.nTable);
	tblObject.DataTable().rows().every( function ( rowIdx, tableLoop, rowLoop ) {
		var row = this;
		var data = row.data();
		var columns = settings.aoColumns;

		/**
		 * Si el botón editar fue pulsado, el estado es puesto en 'edit'.
		 * Si al refrescar una fila se encuentra en este estado, es puesta en estado 'editing'
		 * y sus campos son inicializados según lo que haya sido definido en la inicialización de DataTables.
		 */
		if (data.estado == 'edit') {
			//Cambiar estado
			data.estado = 'editing';
			row.data(data);
			//Inicializar campos
			var initColumns = {};
			for (let key in columns) {
				if (columns[key].editType !== undefined) {
					initColumns[key] = {
						title: columns[key].title,
						data: columns[key].data,
						value: data[columns[key].data],
						editType: columns[key].editType,
						editConfig: columns[key].editConfig,
						editClass: columns[key].editClass,
						editCallback: columns[key].editCallback,
						editData: columns[key].editData,
						cellNode: tblObject.DataTable().cell(row, key).node()
					};
				}
			}
			dtColumnsToInputs(initColumns, row);
		}

        var id_column = null;
		for (let key in columns) {
            //ID Column
            if (columns[key].editType !== undefined && columns[key].editType == 'id') {
                id_column = columns[key].data;
            }
			//Action Buttons
			if (columns[key].name !== undefined && columns[key].name == 'actions') {
				var actionCell = tblObject.DataTable().cell(row, key).node();
				dtActionButtons(data, actionCell, columns[key].editConfig, id_column, settings);
			}
			//Combo columns / set text
			if (columns[key].editType !== undefined && (columns[key].editType == 'select' | columns[key].editType == 'bselect')/* && data.estado != 'editing'*/) {
				if (data.estado != 'editing' || (row.data().fixed !== undefined && row.data().fixed == true && columns[key].editConfig !== undefined && columns[key].editConfig.blockFixed !== undefined && columns[key].editConfig.blockFixed == true)) {
					var actionCell = tblObject.DataTable().cell(row, key).node();
					//Si el combo tiene opciones
					if (columns[key].editData !== undefined) {
						// console.log(data[columns[key].data]);
						if (Array.isArray(data[columns[key].data])) {
							var arrText = [];
						}
						//Recorrer todas las opciones del objeto del combo
						for (let kOpt in columns[key].editData) {
							if (Array.isArray(data[columns[key].data]) && data[columns[key].data].indexOf(columns[key].editData[kOpt].id) != -1) {
								arrText.push(columns[key].editData[kOpt].text);
							} else if (columns[key].editData[kOpt].id == data[columns[key].data]) {
								//Si la opción es encontrada, la celda es vaciada y el texto de la opción es situado en la misma
								$(actionCell).empty().append(columns[key].editData[kOpt].text);
								
							}
						}
						if (Array.isArray(data[columns[key].data])) {
							$(actionCell).empty().append(`<div style="overflow: auto; height: 40px;">${arrText.join(', ')}</div>`);
						}
					}
				}
				
			}
			
			//Checkbox
			if (columns[key].editType !== undefined && columns[key].editType == 'checkbox' && data.estado != 'editing') {
				var actionCell = tblObject.DataTable().cell(row, key).node();
				if (data[columns[key].data]) {
					$(actionCell).empty().append(`<span class="fa fa-check"></span>`);
				} else {
					$(actionCell).empty().append(`<span class="fa fa-times"></span>`);
				}
			}
		}

		

	});

}

function dtActionButtons(row, cell, config, id_column, settings) {
	var buttons = null;
	var tblObject = $(settings.nTable);

	// console.log(config);
	//Class
	var groupClass = '';
	if (config.groupClass !== undefined) {
		groupClass = `btn-group ${config.groupClass}`;
	} else {
		groupClass = `btn-group`;
	}
	//Text style
	var textStyle = '';
	if (config.textStyle !== undefined) {
		textStyle = `style="${config.textStyle}"`;
	} else {
		textStyle = ``;
	}

	var elements = {
		group: $(`<div class="btn-group btn-group-justified" role="group" style="width: auto;"></div>`),
		edit: $(`<div class="${groupClass}" role="group">
					<button type="button" class="btn btn-success edit" title="Editar"><span class="fas fa-edit" ${textStyle}></span></button>
				</div>`),
		apply: $(`<div class="${groupClass}" role="group">
					<button type="button" class="btn btn-success apply" title="Consolidar"><span class="fa fa-check" ${textStyle}></span></button>
				</div>`),
		undo: $(`<div class="${groupClass}" role="group">
					<button type="button" class="btn btn-warning undo" title="Revertir"><span class="fa fa-undo" ${textStyle}></span></button>
				</div>`),
		remove: $(`<div class="${groupClass}" role="group">
					<button type="button" class="btn btn-danger remove" title="Eliminar"><span class="fa fa-trash" ${textStyle}></span></button>
				</div>`),
	}

	//Elemento base
	buttons = elements.group;

	// console.log(row);

	//Elemento que tiene 'id', elemento existente en DB
	if (row[id_column] != undefined || row[id_column] != null) {
		if (!row.estado) {
			if (config.editExisting) {
				buttons.append(elements.edit);
			}
		} else if (row.estado == 'editing') {
			buttons.append(elements.apply);
			buttons.append(elements.undo);
		}
		if (config.deleteExisting && (row.fixed === undefined || row.fixed == false)) {
			buttons.append(elements.remove);
		}
	}
	
	//Elemento que no tiene 'id', elemento nuevo
	if (row[id_column] == undefined || row[id_column] == null) {
		if (!row.estado) {
			buttons.append(elements.edit);
			buttons.append(elements.remove);
		} else if (row.estado == 'editing') {
			buttons.append(elements.apply);
			buttons.append(elements.remove);
		}
	}

	$(cell).empty().append(buttons);

	$(cell).find('.edit').unbind('click').click(function() {
		var row = tblObject.DataTable().row($(this).closest('tr')[0]);
		var data = row.data();
		data.estado = 'edit';
		row.data(data);
		row.draw();
		if (config.editCallback !== undefined) {
			window[config.editCallback](row);
		}
	});

	$(cell).find('.undo').unbind('click').click(function() {
		var row = tblObject.DataTable().row($(this).closest('tr')[0]);
		var data = row.data();
		data.estado = null;
		row.data(data);
		row.invalidate().draw();
		if (config.undoCallback !== undefined) {
			window[config.undoCallback](row);
		}
	});

	$(cell).find('.remove').unbind('click').click(function() {
		var row = tblObject.DataTable().row($(this).closest('tr')[0]);
		//Recalcular orden de todas las filas
		if (row.data().orden !== undefined) {
			row.remove();
			var orden = 1;
			tblObject.DataTable().rows().every( function ( rowIdx, tableLoop, rowLoop ) {
				var row = this;
				var data = row.data();
				data.orden = orden++;
				row.data(data);
			});
			tblObject.DataTable().draw();
		} else {
			row.remove().draw();
		}
		if (config.removeCallback !== undefined) {
			window[config.removeCallback](row);
		}
	});

	$(cell).find('.apply').unbind('click').click(function() {
		//Objeto datatables correspondiente a fila y datos de esa fila
		var row = tblObject.DataTable().row($(this).closest('tr')[0]);
		var data = row.data();
		data.estado = null;

		//Cargar objeto con columnas editables
		var initColumns = {};
		var columns = settings.aoColumns;
		for (let key in columns) {
			if (columns[key].editType !== undefined) {
				initColumns[key] = {
					data: columns[key].data,
					editType: columns[key].editType,
					editConfig: columns[key].editConfig,
					cellNode: tblObject.DataTable().cell(row, key).node()
				};
			}
		}

		//Extraer valores de los campos inicializados en la fila
		var newdata = dtColumnsToInputs(initColumns, row, true);

		//Pisar atributos de objeto con los extraídos
		for(let key in newdata) {
			data[key] = newdata[key];
		}

		//Setear objeto de esta fila con el recién creado
		row.data(data);
		//Callback
		if (config.applyCallback !== undefined) {
			window[config.applyCallback](row);
		}
		//Invalidar fila para que vuelva a ser dibujada al llamar función draw
		row.invalidate().draw();
	});
}

function validateTable(tableObject) {
	editingRows = false;
	//Por cada objeto fila DataTables
	tableObject.DataTable().rows().every( function ( rowIdx, tableLoop, rowLoop ) {
		var row = this;
		var data = row.data();
		if (data.estado == 'editing') {
			editingRows = true;
		}
	});

	return editingRows;
}

function localStorageDOM(id, value = false) {
	if (value === null) {
		//Delete
		alert("aka");
		localStorage.removeItem(apiPath+".dom."+id);
	} else if (value === false) {
		//Get
		return JSON.parse(localStorage.getItem(apiPath+".dom."+id));
	} else {
		//Set
		localStorage.setItem(apiPath+".dom."+id, JSON.stringify(value));
	}
}

function localStorageSet(target, obj) {
	for(var key in obj) {
		localStorage.setItem(apiPath+"."+target+"."+key, JSON.stringify(obj[key]));
	}
}

function localStorageGet(target, key) {
	return JSON.parse(localStorage.getItem(apiPath+"."+target+"."+key));
}

//Array Functions
function arrayColumn(input, column_key, index_key = null) {
	if (input !== null && (typeof input === 'object' || Array.isArray(input))) {
		var newarray = [];

		if (typeof input === 'object') {
			let temparray = [];
			for (let key of Object.keys(input)) {
				temparray.push(input[key]);
			}
			input = temparray;
		}
		
		if (Array.isArray(input)) {
			for (let key of input.keys()) {
				if (index_key && input[key][index_key]) {
					if (column_key) {
						newarray[input[key][index_key]] = input[key][column_key];
					} else {
						newarray[input[key][index_key]] = input[key];
					}
				} else {
					if (column_key) {
						newarray.push(input[key][column_key]);
					} else {
						newarray.push(input[key]);
					}
				}
			}
		}
		
		return newarray;
	}
}

function arrayUnique(array){
	if (array !== null) {
		return array.filter(function(el, index, arr) {
			return index == arr.indexOf(el);
		});
	}
}

function arrayFilter(array, filter) {
	var newarray = {};
	for (var key in array) {
		var pass = true;
		for (var fil in filter) {
			if (typeof filter[fil] === 'array' || typeof filter[fil] === 'object') {
				if (filter[fil].indexOf(array[key][fil]) === -1) {
					pass = false;
				}
			} else {
				if (array[key][fil] != filter[fil]) {
					pass = false;
				}
			}
		}
		if (pass) {
			newarray[key] = array[key];
		}
	}
	return newarray;
}

function inArray(needle, haystack, strict = false) {
	for (let key in haystack) {
		if (strict) {
			if (haystack[key] === needle) {
				return true;
			}
		} else {
			if (haystack[key] == needle) {
				return true;
			}
		}
	}
	return false;
}

function arrayJoin(array, object, objcolkey, objcolval) {
	//array: [1, 2, 3, 4]
	//object: []
	var newarray = {};
	for (var key in array) {
		for (var okey in object) {
			if (array[key] == object[okey][objcolkey]) {
				newarray[array[key]] = {}
			}
		}
		
	}
}

function buildSelectValuesFIObject(object) {
	
	var excludeData = ['title', 'style', 'class', 'disabled'];

	var groups = {};
	for (let key in object) {
		if (object[key].data === undefined) {
			object[key].data = {group: 'default'};
		}
		if (object[key].data.group === undefined || object[key].data.group == '') {
			object[key].data.group = 'default';
		}
		if (groups[object[key].data.group] === undefined) {
			groups[object[key].data.group] = [];
		}
		if (object[key].id === null) {
			object[key].id = '';
		}
		groups[object[key].data.group].push({
			value: object[key].id, 
			text: object[key].text, 
			data: object[key].data
		});
	}

	var html = '';
	//Default
	for (let key in groups) {
		if (key != 'default') {
			html += '<optgroup label="'+key+'">';
		}
		for (let vkey in groups[key]) {
			html += '<option value="'+groups[key][vkey].value+'"';
			if (groups[key][vkey].data) {
				for (var dkey in groups[key][vkey].data) {
					if (dkey != 'group') {
						html += (!inArray(dkey, excludeData)?' data-':'')+dkey+'="'+groups[key][vkey].data[dkey]+'"';
					}
				}
			}
			html += '>'+groups[key][vkey].text+'</option>';
		}
		if (key != 'default') {
			html += '</optgroup>';
		}
	}
	return html;

	/*var options = '';
	for (var key in object) {
		options = options + '<option value="'+object[key].id+'"';
		if (object[key].data) {
			for (var dkey in object[key].data) {
				options = options + ' data-'+dkey+'="'+object[key].data[dkey]+'"';
			}
		}
		options = options + '>'+object[key].text+'</option>';
	}
	return options;*/
}

function validEmail(v) {
   	var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  	return re.test(v);
}

//Funciones de rut
function cleanRut (rut) {
	return typeof rut === 'string'
		? rut.replace(/^0+|[^0-9kK]+/g, '').toUpperCase()
		: ''
}
	
function validateRut (rut) {
	if (typeof rut !== 'string') {
		return false
	}
	if (!/^0*(\d{1,3}(\.?\d{3})*)-?([\dkK])$/.test(rut)) {
		return false
	}
	
	rut = cleanRut(rut)
	
	var t = parseInt(rut.slice(0, -1), 10)
	var m = 0
	var s = 1
	
	while (t > 0) {
		s = (s + (t % 10) * (9 - m++ % 6)) % 11
		t = Math.floor(t / 10)
	}
	
	var v = s > 0 ? '' + (s - 1) : 'K'
	return v === rut.slice(-1)
}

function dvRut (T) {
	var M=0,S=1;
	for(;T;T=Math.floor(T/10))
	S=(S+T%10*(9-M++%6))%11;
	return S?S-1:'K';
}
	
function formatRut (rut) {
	rut = cleanRut(rut)
	
	var result = rut.slice(-4, -1) + '-' + rut.substr(rut.length - 1)
	for (var i = 4; i < rut.length; i += 3) {
		result = rut.slice(-3 - i, -i) + '.' + result
	}
	
	return result
}

// Return array of string values, or NULL if CSV string not well formed.
function CSVtoArray(text) {
    var re_valid = /^\s*(?:'[^'\\]*(?:\\[\S\s][^'\\]*)*'|"[^"\\]*(?:\\[\S\s][^"\\]*)*"|[^,'"\s\\]*(?:\s+[^,'"\s\\]+)*)\s*(?:,\s*(?:'[^'\\]*(?:\\[\S\s][^'\\]*)*'|"[^"\\]*(?:\\[\S\s][^"\\]*)*"|[^,'"\s\\]*(?:\s+[^,'"\s\\]+)*)\s*)*$/;
    var re_value = /(?!\s*$)\s*(?:'([^'\\]*(?:\\[\S\s][^'\\]*)*)'|"([^"\\]*(?:\\[\S\s][^"\\]*)*)"|([^,'"\s\\]*(?:\s+[^,'"\s\\]+)*))\s*(?:,|$)/g;
    // Return NULL if input string is not well formed CSV string.
    if (!re_valid.test(text)) return null;
    var a = [];                     // Initialize array to receive values.
    text.replace(re_value, // "Walk" the string using replace with callback.
        function(m0, m1, m2, m3) {
            // Remove backslash from \' in single quoted values.
            if      (m1 !== undefined) a.push(m1.replace(/\\'/g, "'"));
            // Remove backslash from \" in double quoted values.
            else if (m2 !== undefined) a.push(m2.replace(/\\"/g, '"'));
            else if (m3 !== undefined) a.push(m3);
            return ''; // Return empty string.
        });
    // Handle special case of empty last value.
    if (/,\s*$/.test(text)) a.push('');
    return a;
};

function getCleanedString(cadena){
	
	var specialChars = "!@#$^&%*()+=-[]\/{}|:<>?,.";
 
	
	for (var i = 0; i < specialChars.length; i++) {
		cadena= cadena.replace(new RegExp("\\" + specialChars[i], 'gi'), '');
	}   
 
	// Lo queremos devolver limpio en minusculas
	cadena = cadena.toLowerCase();
 
	// Quitamos acentos y "ñ". Fijate en que va sin comillas el primer parametro
	cadena = cadena.replace(/[áäà]/gi,"a");
	cadena = cadena.replace(/é/gi,"e");
	cadena = cadena.replace(/í/gi,"i");
	cadena = cadena.replace(/ó/gi,"o");
	cadena = cadena.replace(/ú/gi,"u");
	cadena = cadena.replace(/ñ/gi,"n");
	return cadena;
}

function checkEmpValue(obj){
	if($("#" + obj).val() == ""){
		swal.fire({
            type: "warning",
            title: '',
			text: obj.toUpperCase() + ", ES OBLIGATORIO",
			animation: true,
        });
		return false;
	}else{
		return true;
	}
}