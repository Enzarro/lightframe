var path = 'sys_socket';

socket.on('update-bar', function(data) {
    // console.log(data);
    $("#progress-test").find("span").html(`${data.current}%`);
    $(".progress-bar").css('width', `${data.current}%`)
    $(".progress-bar").attr('aria-valuenow', data.current);

});

$("#progresstest").find("#test").click(function() {

    $("#progress-test").find("span").html(`${0}%`);
    $(".progress-bar").css('width', `${0}%`)
    // $(".progress-bar").attr('aria-valuenow', 0);

    setTimeout(() => {
        $.post(`${path}/get`, {
            time: $("#progresstest").find("#time").val(),
            step: $("#progresstest").find("#step").val()
        }, function(data) {
            console.log(data);
        })
    }, 200);

    
});