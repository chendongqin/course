$(function () {
    var id = $('#questionCourseId').val();
    getQuestion(id);
});

$(function () {
   $('.delet').click(function () {
       var id = $(this).attr('data-id');
       $.post('/teacher/task/delTaskjob',{id:id},function (json) {
           if(json.status == true){
               location.reload();
           }else{
               alert(json.msg);
           }
       })
   }) ;
});

$(function () {
   $('#pushQuestion').click(function () {
       var id= $('#questionCourseId').val();
      var msg = $('#pushString').val();
      $.post('/teacher/index/addQuestion',{courseid:id,msg:msg},function (json) {
          if(json.status == true){
              location.reload();
          }else{
              alert(json.msg);
          }
      });
   });
});
$(function () {
    $("body").on("click",".returnMsg",function () {
        var id= $(this).attr('data-id');
        var msg = $('#returnMsg_'+id).val();
        $.post('/teacher/index/addanswer',{id:id,msg:msg},function (json) {
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
    $.get('/teacher/index/questionJson?id='+id,function (json) {
        if(json.status === true){
            $('#question').empty();
            $.each(json.data,function (i,val) {
                str = str+ '<div class="sentmessage">' +
                    '                                <span class=" liuyan">' +
                    '                                    <h5 class="name">'+val.user_name+'</h5>' +
                    '                                    <p>'+UnixToDate(val.create_time)+'</p>' +
                    '                                </span>' +
                    '                                <br>' +
                    '                                <span class="liuyan-text">' +
                    '                                    <p><a href="/teacher/index/answer?fatherId='+val.Id+'">'+val.msg+'</a></p>' +
                    '                                </span>' +
                    '                                <br>';
                $.each(val.anwers,function (j,anw){
                    str = str+'<div class="recall_text">\n' +
                        '                                    <span class="comefrom">回复来自：</span>' +
                        '                                    <span class="recall_name"><h5 class="name">'+anw.user_name+':</h5></span><span  class=\'text_liuyan\'>'+anw.msg+'</span><br>\n' +
                        '                                    <span class="recall_time">'+UnixToDate(anw.create_time)+'</span>\n' +
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


