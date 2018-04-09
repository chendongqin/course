$(function () {
    $.ajax({
        url: '/student/index/groom/',
        type: 'POST',
        async: true,
        cache: false,
        contentType: false,
        processData: false,
        success: function (data) {
            if(data.status==true){
                var str = '<ul>\n';
               $.each(data.data,function(key,val){
                   if(val.image.length ==0){
                       str = str +  '                    <li class="course">\n' +
                           '                        <div class="course_img">\n' +
                           '                            <img src="/static/image/css.jpg" alt="logo">\n';
                   }else{
                       str = str +  '                    <li class="course">\n' +
                           '                        <div class="course_img">\n' +
                           '                            <img src="'+val.image+'" alt="logo">\n';
                   }
                   str = str +
                   '                        </div>\n' +
                   '                        <div class="text">\n' +
                   '                            <span class="span_one">\n' +
                   '                                <h4>'+val.name+'</h4>\n' +
                   '                                <p>'+val.teacher_name+'</p>\n' +
                   '                            </span>\n' +
                   '                            <span class="span_two">\n' +
                   '                                <p><a href="javascript:;" class="addcourse" data-id="'+val.Id+'">添加课程</a></p>\n' +
                   '                                <p>推荐指数：5</p>\n' +
                   '                            </span>\n' +
                   '                        </div>\n' +
                   '                    </li>\n';
               });
                str = str +'                </ul>';
                    $(".groomCourse").append(str);
            }
        },
    });
});
$(function () {
    $('#changeGroom').click(function () {
        $('.groomCourse').empty();
        $.ajax({
            url: '/student/index/groom/',
            type: 'POST',
            async: true,
            cache: false,
            contentType: false,
            processData: false,
            success: function (data) {
                if(data.status==true){
                    var str = '<ul>\n';
                    $.each(data.data,function(key,val){
                        if(val.image.length ==0){
                            str = str +  '                    <li class="course">\n' +
                                '                        <div class="course_img">\n' +
                                '                            <img src="/static/image/css.jpg" alt="logo">\n';
                        }else{
                            str = str +  '                    <li class="course">\n' +
                                '                        <div class="course_img">\n' +
                                '                            <img src="'+val.image+'" alt="logo">\n';
                        }
                        str = str +
                            '                        </div>\n' +
                            '                        <div class="text">\n' +
                            '                            <span class="span_one">\n' +
                            '                                <h4>'+val.name+'</h4>\n' +
                            '                                <p>'+val.teacher_name+'</p>\n' +
                            '                            </span>\n' +
                            '                            <span class="span_two">\n' +
                            '                                <p><a href="javascript:;" class="addcourse" data-id="'+val.Id+'">添加课程</a></p>\n' +
                            '                                <p>推荐指数：5</p>\n' +
                            '                            </span>\n' +
                            '                        </div>\n' +
                            '                    </li>\n';
                    });
                    str = str +'                </ul>';
                    $(".groomCourse").append(str);
                }
            },
        });
    });
});
