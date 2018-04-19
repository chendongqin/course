
$(function () {
    var name = $('#input_search').val();
    searchStu(name);
});
 setInterval(function working(){
     var teacher_id = $('#teacher_id').val();
     if(teacher_id !=0){
         $.get('/student/chat?stuId='+stu_id,function (json) {
             var chatStr = '';
             $('.chat-box-right-top').empty();
             $.each(json.data,function (i,val) {
                 if(val.sender ==0)
                    chatStr =chatStr + '<div class="p_chat_left"><img src="/static/image/stu.jpg" alt="" id="img_left"><span id="text_left">'+val.msg+'</span></div>';
                 else
                     chatStr =chatStr + '<div class="p_chat_right"><span id="text_right">'+val.msg+'</span><img src="/static/image/teacher.jpg" alt="" id="img_right"></div>';
             });
             $('.chat-box-right-top').append(chatStr);
         });
     }
},2000);

$(function(){
   $('#btn_search').click(function () {
       var stuName = $('#input_search').val();
       searchStu(stuName);
   });
   $('#chat_sent').click(function () {
       var msg= $('#text_sent').val();
       var stu_id = $('#stu_id').val();
       var data = {msg:msg,id:stu_id};
       $.post('/teacher/chat/add',data,function (json) {
          if(json.status == false)
              alert(json.msg);
          else
              $('#text_sent').val("");
       });
   });
});

function searchStu(stuName) {
    $('.chat_list').empty();
    var str = '';
    $.post('/teacher/chat/chatStu',{stuName:stuName},function (json) {
        $.each(json.data,function (i,val) {
            str = str + '<a href="/teacher/index/chat?stuName='+stuName+'&stuId='+val.Id+'">'+val.name+'</a>';
        });
        $('.chat_list').append(str);
    });

}