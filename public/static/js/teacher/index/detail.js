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
      })
   });
});

function getQuestion(id) {
    var str = ''
    $.get('/teacher/index/questionJson?id='+id,function (json) {
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
                    '                                    <p><a href="/teacher/index/answer?fatherId='+val.Id+'">'+val.msg+'</a></p>\n' +
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


