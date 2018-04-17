
$(function () {
    var stuName = $('#input_search').val();
    searchStu(stuName);
});
 setInterval(function working(){
     var stu_id = $('#stu_id').val();
     if(stu_id !=0){
         $.get('/teacher/chat?stuId='+stu_id,function (json) {
             var chatStr = '';
             $('.chat-box-right-top').empty();
             $.each(json.data,function (i,val) {
                 if(val.sender ==0)
                    chatStr =chatStr + '<p>'+val.msg+'</p>';
                 else
                     chatStr =chatStr + '<p style="float: right">'+val.msg+'</p>';
             });
             $('.chat-box-right-top').append(chatStr);
         });
     }
},2000);

$(function(){
   $('#btn_search').click(function () {
       var stuName = $('#input_search').val();
       searchStu(stuName);
   })
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