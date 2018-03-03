$(function () {
    $("#idcardPhoto").change(function () {
        var formData = new FormData();
        formData.append('idcardPhoto',$("#idcardPhoto")[0].files[0]);
        $.ajax({
            url: '/index/upload/cardid/',
            type: 'POST',
            data: formData,
            async: true,
            cache: false,
            contentType: false,
            processData: false,
            success: function (returndata) {
                if(returndata.status==true){
                    $("#idcardImg").attr('src',returndata.fileName);
                    $("#virefy_card_id").val(returndata.fileName);
                }else{
                    alert(returndata.msg);
                }
            },
            error: function () {
                alert('上传错误');
            }
        });
    })
});
$(function () {
    $("#teacherPhoto").change(function () {
        var formData = new FormData();
        formData.append('teacherPhoto',$("#teacherPhoto")[0].files[0]);
        $.ajax({
            url: '/index/upload/teacher/',
            type: 'POST',
            data: formData,
            async: true,
            cache: false,
            contentType: false,
            processData: false,
            success: function (returndata) {
                if(returndata.status==true){
                    $("#teacherImg").attr('src',returndata.fileName);
                    $("#virefy_photo").val(returndata.fileName);
                }else{
                    alert(returndata.msg);
                }
            },
            error: function () {
                alert('上传错误');
            }
        });
    })
});
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
        $("#codeImg").attr('src','/index/index/virefy?channel=apply&'+ timestamp);
    });
});