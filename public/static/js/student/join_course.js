$(function () {
    var courseid = 0;
   $(".addcourse").click(function () {
       courseid = $(this).attr('data-id');
       $('#modelJoinCourse').modal('show');
   });

    $("#joinCourseButton").click(function () {
        var code = $('#model_string').val();
        $.post('/student/index/joincourse',{courseId:courseid,code:code},function (json) {
            if(json.status==true){
                window.location.href = '/student/index/coursedetail?id='+courseid;
            }else{
                $('.tc_error').html(json.msg);
                $('#error').show();
            }
        });
    });
    $("#applyCourseButton").click(function () {
        var reason = $('#model_string').val();
        $.post('/student/index/apply',{id:courseid,reason:reason},function (json) {
            if(json.status ==true){
               location.reload();
            }else{
                $('.tc_error').html(json.msg);
                $('#error').show();
            }
        });
    });
});