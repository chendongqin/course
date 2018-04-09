$(function () {
   $('.pass').click(function () {
       var id= $(this).attr('data-id');
       $.post('/teacher/index/pass',{id:id},function (json) {
           if(json.status==true)
               location.reload();
           else
               alert(json.msg);
       })
   });
    $('.refuse').click(function () {
        var id= $(this).attr('data-id');
        $.post('/teacher/index/refuse',{id:id},function (json) {
            if(json.status==true)
                location.reload();
            else
                alert(json.msg);
        })
    })
});