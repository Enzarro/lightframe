var path = 'sys_query';
var table = null;

$(document).off('click', '#print').on('click', '#print', function() {
    console.log('asd')
    $.post(`${path}/query`, {
        query: $("#query").val()
    }, function(data) {
        if (data.swal) {
            swal.fire(data.swal)
        }
        if ( table != null ) {
            table.clear().destroy()
            $('#result').empty();
        }
        
        if (data.columns !== undefined && data.data !== undefined) {
            table = $("#result").DataTable({
                scrollX: true,
                columnDefs: data.columns,
                data: data.data
            })
        }
    }, 'json')
});

