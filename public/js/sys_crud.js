var dTable;
var table = 'mantenedores';

$.post('sys_crud/list', {
    config: true
}, function(columnDefs) {
    //Load DataTables
    dTable = $(`#${table}`).DataTable({
        "ajax": {
            "url": 'sys_crud/list',
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
            [ 0, "desc" ]
        ],
        // "language": {
        //     "url": pathSite+"core/bootstrap_admin/vendor/datatables-plugins/spanish.json",
        //     select: {
        //         rows: {
        //             _: "Ha seleccionado %d Ítems",
        //             0: "Seleccione mediante los checkbox",
        //             1: "Sólo 1 ítem seleccionado"
        //         }
        //     }
        // },
        // "initComplete": function(settings, json) {
        //     //Format search box
        //     $(`#${table}_filter`).find("input").wrap("<div class='input-group'></div>");
        //     $(`#${table}_filter`).find(".input-group").prepend("<span class='input-group-prepend'><span class='input-group-text'><span class='fa fa-search text-center' aria-hidden='true'></span></span></span>");
        //     $(`#${table}_filter`).find("input").css("margin", "0");
        // }
    });
}, "json");