$(function () {
    $("#courseImage").change(function () {
        var formData = new FormData();
        formData.append('courseImage',$("#courseImage")[0].files[0]);
        $.ajax({
            url: '/teacher/upload/courseImage/',
            type: 'POST',
            data: formData,
            async: true,
            cache: false,
            contentType: false,
            processData: false,
            success: function (returndata) {
                if(returndata.status==true){
                    $("#courseImg").attr('src',returndata.fileName);
                    $("#image").val(returndata.fileName);
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
