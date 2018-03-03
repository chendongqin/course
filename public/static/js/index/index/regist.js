$(function () {
    $("#sendEmailCode").click(function () {
        var email = $('#email').val();
        $.get('/index/email/regist?email='+email,function (json) {
            if(json.status===false){
                alert(json.msg);
            }
        })
    });
});
$(function () {
    $("#changeCode").click(function () {
        var timestamp = new Date().getTime();
        $("#codeImg").attr('src','/index/index/virefy?channel=regist&'+ timestamp);
    });
});
