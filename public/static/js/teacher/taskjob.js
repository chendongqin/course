$(function () {
    var id = 0;
   $('.correctTask').click(function () {
        id = $(this).attr('data-id');
       $('#modeltaskJob').modal('show');
   });
    $('#sureScoreButton').click(function () {
        var score = $('#modal_score').val();
        var remark = $('#modal_remark').val();
        $.post('/teacher/task/score',{taskId:id,score:score,remark:remark},function (json) {
           if(json.status == true)
               location.reload();
           else{
               $('.tc_error').html(json.msg);
               $('#error').show();
           }
        });
    });

});