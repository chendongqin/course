$(function () {
    var id = $('#questionCourseId').val();
    getQuestion(id);
});
$(function () {
    $('#pushQuestion').click(function () {
        var id= $('#questionCourseId').val();
        var msg = $('#pushString').val();
        $.post('/student/index/addQuestion',{courseid:id,msg:msg},function (json) {
            if(json.status == true){
                location.reload();
            }else{
                alert(json.msg);
            }
        })
    });
    $("body").on("click",".returnMsg",function () {
        var id= $(this).attr('data-id');
        var msg = $('#returnMsg_'+id).val();
        $.post('/student/index/addanswer',{id:id,msg:msg},function (json) {
            if(json.status == true){
                location.reload();
            }else{
                alert(json.msg);
            }
        });
    });
});

function getQuestion(id) {
    var str = ''
    $.get('/student/index/questionJson?id='+id,function (json) {
        if(json.status === true){
            $('#question').empty();
            $.each(json.data,function (i,val) {
                str = str+ '<div class="sentmessage">\n' +
                    '                                <span class=" liuyan">\n' +
                    '                                    <h5 class="name">'+val.user_name+'</h5>\n' +
                    '                                    <p>'+UnixToDate(val.create_time)+'</p>\n' +
                    '                                </span>\n' +
                    '                                <br>\n' +
                    '                                <span class="liuyan-text">\n' +
                    '                                    <p>'+val.msg+'</p>\n' +
                    '                                </span>\n' +
                    '                                ';
                $.each(val.anwers,function (j,anw){
                    str = str +'<div class="recall_text">' +
                        '                                    <p class="comefrom">回复来自：</p><br>\n' +
                        '                                    <p class="recall_name"><h5 class="name">'+anw.user_name+':</h5></p><p  class=\'text_liuyan\'>'+anw.msg+'</p><br>\n' +
                        '                                    <p class="recall_time">'+UnixToDate(anw.create_time)+'</p>\n' +
                        '                                </div>';
                });
                str = str +
                    '                                </div><div class="input_liuyan">' +
                    '                                    <input type="text" id="returnMsg_'+val.Id+'"  placeholder="我也说一句..."><input type="button" class="returnMsg" data-id="'+val.Id+'" value="回复">' +
                    '                                </div>';
            });
            $('#question').append(str);
        }
    })
}
function UnixToDate(unixTime, isFull, timeZone) {
    if (typeof (timeZone) == 'number'){
        unixTime = parseInt(unixTime) + parseInt(timeZone) * 60 * 60;
    }
    var time = new Date(unixTime * 1000);
    var ymdhis = "";
    ymdhis += time.getUTCFullYear() + "-";
    ymdhis += (time.getUTCMonth()+1) + "-";
    ymdhis += time.getUTCDate();
    if (isFull === true){
        ymdhis += " " + time.getUTCHours() + ":";
        ymdhis += time.getUTCMinutes() + ":";
        ymdhis += time.getUTCSeconds();
    }
    return ymdhis;
}