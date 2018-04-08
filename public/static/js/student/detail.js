$(function () {
    var id = $('#questionCourseId').val();
    getQuestion(id);
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
                    '                                <br>\n' +
                    '                                <input type="button" value="回复">\n' +
                    '                            </div>';
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