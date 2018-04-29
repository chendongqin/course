
$(function () {
    var name = $('#input_search').val();
    searchStu(name);
});
 setInterval(function working(){
     var teacher_id = $('#teacher_id').val();
     var url = '';
     var tmpStr = '';
     var more = $('#chat_more').val();
     if(more == 0){
         url = '/student/chat?tId='+teacher_id;
         tmpStr =  '<a href="javascript:;" id="click">加载更多</a>';
     }
     else
         url = '/student/chat/all?tId='+teacher_id;
     if(teacher_id !=0){
         $.get(url,function (json) {
             var chatStr = tmpStr;
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
    $('.chat-box-right-top').on('click',"#click",function () {
        $('#chat_more').val(1);
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
    $.post('/student/chat/chatTeacher',{name:name},function (json) {
        var str = '';
        $.each(json.data,function (i,val) {
            str = str + '<div ><a href="/student/index/chat?name='+name+'&teacherId='+val.Id+'">'+val.name;
            $.post('/student/chat/stdingNum',{tid:+val.Id},function (data) {
                var num = data.data.num;
                if(num ==0)
                    str = str+'</a></div>';
                else
                    str = str+'<span style="color: red"> '+num+'</span></a></div>';
                $('.chat_list').append(str);
            });
        });

    });

}