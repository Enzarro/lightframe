var path = 'sys_socket';

socket.on(`${path}:update-bar`, function(data) {
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

$(document).on("click","#test2",function(){
    $.post(`${path}/message`, {
        message: $("#message").val()
    }, function(data) {
        console.log(data);
    })
});

$(document).on("click","#test3",function(){
    $.post(`${path}/list`, function(data) {
        console.log(data);
        for(var key in data){
            console.log(data[key]);
        }
    })
});