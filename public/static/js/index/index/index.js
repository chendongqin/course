$(function () {
    $("#changeCode").click(function () {
        var timestamp = new Date().getTime();
        $("#codeImg").attr('src','/index/index/virefy?channel=login&'+ timestamp);
    });
});