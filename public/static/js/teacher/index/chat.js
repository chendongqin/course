
$(function () {
    var stuName = $('#input_search').val();
    searchStu(stuName);
});
 setInterval(function working(){
     var stu_id = $('#stu_id').val();
     if(stu_id !=0){
         var url = '';
         var tmpStr = '';
         var more = $('#chat_more').val();
         if(more == 0){
             tmpStr =  '<a href="javascript:;" id="click">加载更多</a>';
             url = '/teacher/chat?stuId='+stu_id;
         }
         else
             url = '/teacher/chat/all?stuId='+stu_id;
         $.get(url,function (json) {
             var chatStr = tmpStr;
             $('.chat-box-right-top').empty();
             $.each(json.data,function (i,val) {
                 if(val.sender ==0)
                    chatStr =chatStr + '<div class="p_chat_left"><img src="/static/image/stu.jpg" alt="" id="img_left"><span id="text_left">'+val.msg+'</span></div>';
                 else
                     chatStr =chatStr + '<div class="p_chat_right"><span id="text_right">'+val.msg+'</span><img src="/static/image/teacher.jpg" alt="" id="img_right"></div><br>';
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
   $('.chat-box-right-top').on('click',"#click",function () {
      $('#chat_more').val(1);
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
    $.post('/teacher/chat/chatStu',{stuName:stuName},function (json) {
        $.each(json.data,function (i,val) {
            var str = '';
            str = str + '<div><a href="/teacher/index/chat?stuName='+stuName+'&stuId='+val.Id+'">'+val.name;
            console.log(str);
            $.post('/teacher/chat/dingNum',{sid:+val.Id},function (data) {
                var num = data.data.num;
                if(num ==0)
                    str = str+'</a></div>';
                else
                    str = str+'<span style="color: red"> '+data.data.num+'</span></a></div>';
                $('.chat_list').append(str);
            });
        });
    });

}