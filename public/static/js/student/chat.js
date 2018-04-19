
$(function () {
    var name = $('#input_search').val();
    searchStu(name);
});
 setInterval(function working(){
     var teacher_id = $('#teacher_id').val();
     if(teacher_id !=0){
         $.get('/student/chat?tId='+teacher_id,function (json) {
             var chatStr = '';
             $('.chat-box-right-top').empty();
             $.each(json.data,function (i,val) {
                 if(val.sender ==0)
                    chatStr =chatStr + '<div class="p_chat_right"><span id="text_right">'+val.msg+'</span><img src="/static/image/stu.jpg" alt="" id="img_left"></div>';
                 else
                     chatStr =chatStr +'<div class="p_chat_left"><img src="/static/image/teacher.jpg" alt="" id="img_right"><span id="text_left">' +val.msg+'</span></div>';
             });
             $('.chat-box-right-top').append(chatStr);
         });
     }
},2000);

$(function(){
   $('#btn_search').click(function () {
       var name = $('#input_search').val();
       searchStu(name);
   });
   $('#chat_sent').click(function () {
       var msg= $('#text_sent').val();
       var teacher_id = $('#teacher_id').val();
       var data = {msg:msg,id:teacher_id};
       $.post('/student/chat/addst',data,function (json) {
          if(json.status == false)
              alert(json.msg);
          else
              $('#text_sent').val("");
       });
   });
});

function searchStu(name) {
    $('.chat_list').empty();
    var str = '';
    $.post('/student/chat/chatTeacher',{name:name},function (json) {
        $.each(json.data,function (i,val) {
            str = str + '<a href="/student/index/chat?name='+name+'&teacherId='+val.Id+'">'+val.name+'</a>';
        });
        $('.chat_list').append(str);
    });

}