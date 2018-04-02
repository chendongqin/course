$(function () {
    $("#changeCode").click(function () {
        var timestamp = new Date().getTime();
        $("#codeImg").attr('src','/index/index/virefy?channel=login&'+ timestamp);
    });
});

//登陆
$(function () {
    $("#login").click(function () {
        var data = $('#loginForm').serialize();
        // console.log(data);

        $.ajax({
            url: '/index/index/login',
            type: 'POST',
            data: data,
            cache: false,
            dataType:'json',
            success: function (returndata) {
                if(returndata.status==true){
                    if(returndata.code ==1){
                        window.location.href = '/student';
                    }else{
                        window.location.href = '/teacher';
                    }
                }else{
                    alert(returndata.msg);
                }
            },
            error: function () {
                alert('登录失败')
            }
        });
    })
 });
 