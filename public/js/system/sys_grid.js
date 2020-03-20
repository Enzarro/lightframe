var dTable, selected;
var path = 'sys_grid';
var primary = 'grid_id';
var table = 'mantenedores';

//Grid
$.post(`${path}/list`, {
    config: true
}, function(columnDefs) {
    //Load DataTables
    dTable = $(`#${table}`).DataTable({
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
            $(`#${table}_filter`).find("input").wrap("<div class='input-group'></div>");
            $(`#${table}_filter`).find(".input-group").prepend("<span class='input-group-addon'><span class='input-group-text'><span class='fa fa-search text-center' aria-hidden='true'></span></span></span>");
            $(`#${table}_filter`).find("input").css("margin", "0");
        }
    });
}, "json");

//Form
$(document).on("click", "#main-new,.main-edit", function() {
    selected = undefined;
    if ($(this).hasClass("main-edit")) {
        selected = dTable.row($(this).closest('tr')).data()[primary];
    }
    $.post(`${path}/form`, {
        id: selected
    }, function(data) {
        $("#modal-default .modal-body").html(data);
        InitFormFields("#modal-default .modal-body");
        $("#modal-default").modal("show");
    });
});

//Consolidate
$(document).on('click', '.main-consolidate', function() {
    selected = dTable.row($(this).closest('tr')).data()[primary];
    $.post(`${path}/consolidate`, {
        id: selected
    }, function(data) {
        swal.fire(data);
    }, 'json');
});

//Save
$("#save").click(function() {
    var data = formToObject("#form-grid");
    data.id = selected;
    $.post(`${path}/set`, data, function(data) {
        if (data.type == 'success') {
            $("#modal-default").modal("hide");
            dTable.ajax.reload();
        }
        swal.fire(data);
    }, 'json');
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
            console.log(dtSelectedIDs);
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