$(function () {
   $("#forgetSend").click(function () {
       var email = $("#registEmail").val();
       var data = {email:email};
       $.post('/index/email/findback',data,function (json) {
           if(json.status===true){
               window.location.href = '/index/index/findback?email='+email;
           }else{
               alert(json.msg);
           }
       })
   })
});