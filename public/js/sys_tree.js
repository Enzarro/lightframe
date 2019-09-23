var dTable;
var path = 'sys_tree';
var table = 'arbol';

$.post(`${path}/list`, {
    config: true
}, function(columnDefs) {
    //Load DataTables
    dTable = $(`#${table}`).DataTable({
        "ajax": {
            "url": `${path}/list`,
            "type": 'POST',
            "data": {
                
            }
        },
        "columnDefs": columnDefs,
        select: {
            style:    'multi',
            selector: 'td:last-child'
        },
        "order": [
            [ 0, "asc" ]
        ],
        language: dtSpanish,
        "initComplete": function(settings, json) {
            //Format search box
            $(`#${table}_filter`).find("input").wrap("<div class='input-group'></div>");
            $(`#${table}_filter`).find(".input-group").prepend("<span class='input-group-addon'><span class='input-group-text'><span class='fa fa-search text-center' aria-hidden='true'></span></span></span>");
            $(`#${table}_filter`).find("input").css("margin", "0");
        }
    });
}, "json");

$(document).on("click", "#main-new,.main-edit", function() {
    var selected;
    if ($(this).hasClass("main-edit")) {
        selected = dTable.row($(this).closest('tr')).data()[0];
    }
    $.post(`${path}/form`, {
        id: selected
    }, function(data) {
        $("#modal-default .modal-body").html(data);
        InitFormFields("#modal-default .modal-body");
        $("#modal-default").modal("show");
    });
});