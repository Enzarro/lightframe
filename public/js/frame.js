var btn_save_layer = "<i class='fas fa-spinner fa-spin'></i> Guardando...";
var socket;

if (socket_enabled) {
	socket = io.connect(socket_address, {
		query: `system=datcapital&token=${$.cookie('token')}`
	});

	socket.on('sql-error', function(data) {
		console.error(data.error);
		console.error(data.query);
	});
}

if (dbstatus && $("#client-selector").length) {
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
				type: 'warning',
				title: 'Bienvenido',
				html: clientPopup,
				showConfirmButton: false,
				showCloseButton: true,
				allowOutsideClick: false
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
				swal.close();
			})
		} else {
			var firstClientVal = $("#client-selector").find("option").filter(function() {
				return $(this).attr("value") > 0;
			}).first().val();
			$("#client-selector").val(firstClientVal);
			sessionStorage.setItem("client", firstClientVal);
		}
	}
}


$("#client-selector").selectpicker({
	liveSearch: true,
	dropdownAlignRight: true
});

$("#client-selector").change(function() {
	sessionStorage.setItem("client", $(this).val());
	$(document).trigger('client-change');
});

$(document).ready(function() {
	$('html').show();
});

function formToObject(form) {
    //Object
    // var object = formArrayToObject($(form).serializeArray());
    var object = {};
    $(form).find('*[name]').each(function() {
        if ($(this).attr('type') == 'checkbox') {
            object[$(this).attr('name')] = $(this).is(":checked")?1:0;
        } else {
            object[$(this).attr('name')] = $(this).val()?$(this).val():null;
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
			} else if (data.fitype == "dtable") {
				tCCs = dtInit(`#${$(this).attr('id')}`, {
					paging: false,
					ordering: false,
					searching: false,
					autoWidth: false,
					bInfo: false
				});
			}
		}
	});
}

function dtInit(domid, opts) {
	//Cargas Familiares
    var dtObj = $(domid).find('table');
	dtObj.on('preInit.dt', function() {
        var tableID = $(this).attr('id');
        //Format search box
        $(`#${tableID}_filter`).find("input").wrap(`<div class='input-group'></div>`);
        $(`#${tableID}_filter`).find(".input-group").prepend(`<span class='input-group-addon'><span class='glyphicon glyphicon-search text-center' aria-hidden='true'></span></span>`);
        $(`#${tableID}_filter`).find("input").css("margin", "0");
    }).DataTable({
		paging: opts.paging,
		ordering: opts.ordering,
		searching: opts.searching,
        autoWidth: opts.autoWidth,
        bInfo: opts.bInfo,
		data: JSON.parse($(domid).find('#data').val()),
		columnDefs: JSON.parse($(domid).find("#config").text()),
		drawCallback: function(settings) {
			dtEditDrawCallback(dtObj, settings);
		},
		language: dtSpanish
	});
	$(domid).find("#agregar").click(function() {
		dtObj.DataTable().row.add(JSON.parse($(domid).find("#emptyrow").text()));
		dtObj.DataTable().draw();
	});
	return dtObj;
}

function dtColumnsToInputs(columns, row, extract = false) {
	var rowObj = row.node();
	var colsResult = {};
	for (let key in columns) {
		var res = dtInitInput({
			columns: columns, 
			row: row, 
			cell: columns[key],
			extract: extract
		});
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
	//RUT
	var input;
	if (data.cell.editType == 'rut') {
		if (data.extract) {
			return $(data.cell.cellNode).find('input').val();
		} else {
			input = $(`<input name="${data.cell.data}" type="text" class="form-control" style="width: 100%;">`);
			input.val(data.cell.value);
			
			input.keyup(function() {
				if ($(this).val().length > 1) {
					$(this).val(formatRut($(this).val()));
					if (validateRut($(this).val())) {
						$(this).css({'border-color': 'forestgreen'});
					} else {
						$(this).css({'border-color': '#a94442'});
					}
				} else {
					$(this).css({'border-color': ''});
				}
			}).keyup();
			//input.autoNumeric();
			$(data.cell.cellNode).empty().append(input);
		}
	}
	//ANUMERIC
	if (data.cell.editType == 'anumeric') {
		if (data.extract) {
			colsResult[data.cell.data] = $(data.cell.cellNode).find('input').autoNumeric('get');
		} else {
			input = $(`<input name="${data.cell.data}" type="text" class="form-control" style="width: 100%;">`);
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
			$(data.cell.cellNode).empty().append(input);
		}
	}
	//STRING
	if (data.cell.editType == 'string') {
		if (data.extract) {
			return $(data.cell.cellNode).find('input').val();
		} else {
			input = $(`<input name="${data.cell.data}" type="text" class="form-control" style="width: 100%;">`);
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
			$(data.cell.cellNode).empty().append(input);
		}
	}
	//DATE
	if (data.cell.editType == 'dtpicker') {
		if (data.extract) {
			return $(data.cell.cellNode).find('input').val();
		} else {
			input = $(`<input name="${data.cell.data}" type="text" class="form-control" style="width: 100%;">`);
			input.val(data.cell.value);
			if (data.cell.editConfig !== undefined) {
				input.datetimepicker(data.cell.editConfig);
			} else {
				input.datetimepicker();
			}
			input.on('keypress',function(e) {
				if(e.which == 13) {
					$(rowObj).find('.apply').click();
				}
			});
			$(data.cell.cellNode).empty().append(input);
		}
	}
	//SELECT
	if (data.cell.editType == 'select' | data.cell.editType == 'bselect' | data.cell.editType == 'eselect') {
		if (data.extract) {
			return $(data.cell.cellNode).find('select').val();
			// console.log(colsResult[cell.data]);
		} else {
			input = $(`<select name="${data.cell.data}" class="form-control"></select>`);
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
			$(data.cell.cellNode).empty().append(input);
			//BSELECT
			if (data.cell.editType == 'bselect') {
				if (data.cell.editConfig !== undefined) {
					input.selectpicker(data.cell.editConfig);
				} else {
					input.selectpicker();
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
			$(data.cell.cellNode).empty().append(input);
		}
	}
	return input;
}

function dtEditDrawCallback(tblObject, settings) {
	//Por cada objeto fila DataTables
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
				dtActionButtons(data, actionCell, columns[key].editConfig, id_column);
			}
			//Combo columns / set text
			if (columns[key].editType !== undefined && (columns[key].editType == 'select' | columns[key].editType == 'bselect') && data.estado != 'editing') {
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
						$(actionCell).empty().append(`<div style="overflow: auto; height: 30px;">${arrText.join(', ')}</div>`);
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

	tblObject.find('.edit').unbind('click').click(function() {
		var row = tblObject.DataTable().row($(this).closest('tr')[0]);
		var data = row.data();
		data.estado = 'edit';
		row.data(data);
		row.draw();
	});

	tblObject.find('.undo').unbind('click').click(function() {
		var row = tblObject.DataTable().row($(this).closest('tr')[0]);
		var data = row.data();
		data.estado = null;
		row.data(data);
		row.invalidate().draw();
	});

	tblObject.find('.remove').unbind('click').click(function() {
		var row = tblObject.DataTable().row($(this).closest('tr')[0]);
		row.remove().draw();
	});

	tblObject.find('.apply').unbind('click').click(function() {
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
		//Invalidar fila para que vuelva a ser dibujada al llamar función draw
		row.invalidate().draw();
	});
}

function dtActionButtons(row, cell, config, id_column) {
	var buttons = null;

	var elements = {
		group: $(`<div class="btn-group btn-group-justified" role="group" style="width: auto;"></div>`),
		edit: $(`<div class="btn-group" role="group">
					<button type="button" class="btn btn-success edit" title="Editar"><span class="fas fa-edit"></span></button>
				</div>`),
		apply: $(`<div class="btn-group" role="group">
					<button type="button" class="btn btn-success apply" title="Consolidar"><span class="fa fa-check"></span></button>
				</div>`),
		undo: $(`<div class="btn-group" role="group">
					<button type="button" class="btn btn-warning undo" title="Revertir"><span class="fa fa-undo"></span></button>
				</div>`),
		remove: $(`<div class="btn-group" role="group">
					<button type="button" class="btn btn-danger remove" title="Eliminar"><span class="fa fa-trash"></span></button>
				</div>`),
	}

	//Elemento base
	buttons = elements.group;

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
		if (config.deleteExisting) {
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
		groups[object[key].data.group].push({value: object[key].id, text: object[key].text, data: object[key].data});
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
	
function formatRut (rut) {
	rut = cleanRut(rut)
	
	var result = rut.slice(-4, -1) + '-' + rut.substr(rut.length - 1)
	for (var i = 4; i < rut.length; i += 3) {
		result = rut.slice(-3 - i, -i) + '.' + result
	}
	
	return result
}