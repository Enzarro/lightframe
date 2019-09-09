$(document).ready(function() {
	
	//Set WebSocket
	/*var socket = io.connect('http://'+window.location.host+':8082', {
	    reconnection: true,
	    reconnectionDelay: 1000,
	    reconnectionDelayMax : 5000,
	    reconnectionAttempts: 99999
	});
	socket.on('connect', function(data){
		socket.emit('remote');
	});
	socket.on('sess', function(data) {
		for(let key in data) {
			//Triggear eventos de jQuery por cada llave del objeto recibido por el socket
			$(document).trigger('sess:'+key, data[key]);
		}
	});

	//$('#login_cliente option[value="' + $("#indClienteFrm").val() + '"]').prop('selected', true);
	$('#login_cliente').selectpicker('val', $("#indClienteFrm").val());
	$('#login_cliente').on('change', function() { if($(this).find(":checked").val() != "") enter_log($(this).find(":checked").val()); });
    */
});

function formArrayToObject(array) {
	let final = {};
	for (let obj of array) {
		final[obj.name] = obj.value;
	}
	return final;
}

function InitFormFields(form) {
    $(form).find("select,input").each(function() {
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
			}
		}
	});
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