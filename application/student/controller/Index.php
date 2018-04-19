<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/2/14
 * Time: 21:45
 */
namespace app\student\controller;
use base\Studentbase;
use think\Db;
use think\Session;
class Index extends Studentbase{

    public function index(){
        $user =$this->getUser();
        $pubOrigin  = Db::name('public_origin')->order('create_time','desc')->limit(10)->select();
        $this->assign('origin',$pubOrigin);
        $courseStudents = Db::name('course_students')->where('stu_id',$user['Id'])->select();
        $mycourse = [];
        foreach ($courseStudents as $courseStudent){
            $course = Db::name('courses')->where('Id',$courseStudent['course_id'])->find();
            $mycourse[] = $course;
        }
        $this->assign('mycourse',$mycourse);
        return $this->fetch('index');
    }

    public function course(){
        $user = $this->getUser();
        $where = [];
        $where['end_time'] = ['>=',time()];
        $request = $this->request;
        $name = $request->param('name','','string');
        if(!empty($name))
            $where['name'] = ['like','%'.$name.'%'];
        $this->assign('name',$name);
        $teacherName = $request->param('teacherName','','string');
        if(!empty($teacherName)){
            $Ids = Db::name('teachers')
                ->where(['name'=>['like','%'.$teacherName.'%']])->column('Id');
            $where['teacher_id'] = ['in',$Ids];
        }
        $this->assign('teacherName',$teacherName);
        $page = $request->param('page',1,'int');
        $courses  = Db::name('courses')->where($where)->paginate(6,false,['page'=>$page])->toArray();
        foreach ($courses['data'] as $key=>$value){
            $teacher = Db::name('teachers')->where('Id',$value['teacher_id'])->find();
            $courses['data'][$key]['teacherName'] = $teacher['name'];
            $courses['data'][$key]['teacherEmail'] = $teacher['email'];
            $courseStudent = Db::name('course_students')
                ->where(['stu_id'=>$user['Id'],'course_id'=>$value['Id']])
                ->find();
            $courses['data'][$key]['join'] = empty($courseStudent)?0:1;
        }
        $this->assign('pager',$courses);
        return $this->fetch();
    }

    //获取成绩
    public function myscore(){
        $user = $this->getUser();
        $courseId = $this->request->param('courseId','','int');
        $course = Db::name('course')->where('Id',$courseId)->find();
        if(empty($course))
            return $this->returnJson('课程不存在');
        $score = Db::name('score')->where(['course_id'=>$courseId,'stu_id'=>$user['Id']])->find();
        return $this->returnJson('获取成功',true,1,$score);
    }

    public function joincourse(){
        $user = $this->getUser();
        $request = $this->request;
        $courseId = $request->param('courseId','','int');
        $virefyCode = $request->param('code','','string');
        $courseModel = Db::name('courses');
        $course = $courseModel->where('Id',$courseId)->find();
        if(empty($course))
            return $this->returnJson('课程不存在');
        if($course['end_time']<time())
            return $this->returnJson('课程已结束');
        $courseStudents = Db::name('course_students')
            ->where(['stu_id'=>$user['Id'],'course_id'=>$course['Id']])
            ->find();
        if(!empty($courseStudents))
            return $this->returnJson('已加入该课程');
        if(strcmp($virefyCode,$course['code'])!==0)
            return $this->returnJson('邀请码不正确');
        $add = array('stu_id'=>$user['Id'],'create_time'=>time(),'course_id'=>$course['Id']);
        Db::startTrans();
        $res = Db::name('course_students')->insert($add);
        if(!$res)
            return $this->returnJson('加入课程失败，请重试');
        $num = $course['stu_num'] +1 ;
        $upRes = $courseModel->update(['Id'=>$course['Id'],'stu_num'=>$num]);
        if($upRes){
            Db::commit();
            return $this->returnJson('加入成功');
        }
        Db::rollback();
        return $this->returnJson('加入课程失败，请重试');
    }

    public function mycourse(){
        $pager =  $this->request->param('page',1,'int');
        $user = $this->getUser();
        $courseStudents = Db::name('course_students')
            ->where('stu_id',$user['Id'])
            ->order('create_time','desc')
            ->paginate(6,false,['page'=>$pager])
            ->toArray();
        $courseModel = Db::name('courses');
        foreach($courseStudents['data'] as $key=>$courseStudent){
            $course = $courseModel->where('Id',$courseStudent['course_id'])->find();
            $teacher = Db::name('teachers')->where('Id',$course['teacher_id'])->find();
            $courseStudents['data'][$key]['course'] = $course;
            $courseStudents['data'][$key]['teacher'] = $teacher;
        }
        $this->assign('pager',$courseStudents);
        return $this->fetch('mycourse2');
    }
    //课程详情
    public function coursedetail(){
        $request = $this->request;
        $courseId = $request->param('id','','int');
        $user = $this->getUser();
        $courseStuModel = Db::name('course_students');
        $myJoin = $courseStuModel->where(['course_id'=>$courseId,'stu_id'=>$user['Id']])->find();
        if(empty($myJoin)){
            $this->assign('error','您没有加入该课程');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $course = Db::name('courses')->where('Id',$courseId)->find();
        $this->assign('course',$course);
        $teacher = Db::name('teachers')->where('Id',$course['teacher_id'])->find();
        $this->assign('teacher',$teacher);
//        $courseStudents = $courseStuModel
//            ->where('course_id',$courseId)
//            ->order('create_time','asc')
//            ->select();
//        $this->assign('students',$courseStudents);
        $courseware = Db::name('courseware')->where('Id',$courseId)->select();
        $this->assign('coursewares',$courseware);
        $taskJobs = Db::name('task_job')
            ->where('course_id',$courseId)
            ->order('create_time')
            ->select();
        foreach ($taskJobs as $key=>$taskJob){
            $task = Db::name('task')->where(['stu_id'=>$user['Id'],'task_id'=>$taskJob['Id']])->find();
            $taskJobs[$key]['task'] = $task;
        }
        $this->assign('tasks',$taskJobs);
        return $this->fetch();
    }

    //查看课件
    public function courseware(){
        $request = $this->request;
        $courseId = $request->param('id','','int');
        $page = $request->param('page',1,'int');
        $user = $this->getUser();
        $myJoin = Db::name('course_students')->where(['course_id'=>$courseId,'stu_id'=>$user['Id']])->find();
        if(empty($myJoin)){
            $this->assign('error','您没有加入该课程');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $course = Db::name('courses')->where('Id',$courseId)->find();
        $this->assign('course',$course);
        $teacher = Db::name('teacher')->where('Id',$course['teacher_id'])->find();
        $this->assign('teacher',$teacher);
        $courseware = Db::name('courseware')->where('Id',$courseId)->paginate(10,false,['page'=>$page]);
        $this->assign('courseware',$courseware);
        return $this->fetch();
    }

    //课程交流
    public function question(){
        $request = $this->request;
        $courseId = $request->param('id','','int');
        $page = $request->param('page',1,'int');
        $user = $this->getUser();
        $myJoin = Db::name('course_students')->where(['course_id'=>$courseId,'stu_id'=>$user['Id']])->find();
        if(empty($myJoin)){
            $this->assign('error','您没有加入该课程');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $course = Db::name('courses')->where('Id',$courseId)->find();
        $this->assign('course',$course);
        $teacher = Db::name('teachers')->where('Id',$course['teacher_id'])->find();
        $this->assign('teacher',$teacher);
        $messageModel = Db::name('message');
        $where = ['father_id'=>0,'course_id'=>$courseId];
        $question = $this->request->param('question','','string');
        if(!empty($question))
            $where['msg'] = ['like','%'.$question.'%'];
        $question = $messageModel->where($where)
            ->order('create_time','asc')
            ->paginate(10,false,['page'=>$page])
            ->toArray();
        foreach ($question['data'] as $key=>$data){
            $table = $data['is_teacher']==1?'teachers':'students';
            $userName = Db::name($table)->where('Id',$data['user_id'])->column('name');
            $question['data'][$key]['user_name'] = $userName;
        }
        $this->assign('question',$question);
        return $this->fetch();
    }
    public function questionJson(){
        $request = $this->request;
        $courseId = $request->param('id','','int');
        $user = $this->getUser();
        $myJoin = Db::name('course_students')->where(['course_id'=>$courseId,'stu_id'=>$user['Id']])->find();
        if(empty($myJoin)){
            return $this->returnJson('您没有加入该课程');
        }
        $course = Db::name('courses')->where('Id',$courseId)->find();
        $this->assign('course',$course);
        $teacher = Db::name('teachers')->where('Id',$course['teacher_id'])->find();
        $this->assign('teacher',$teacher);
        $messageModel = Db::name('message');
        $where = ['father_id'=>0,'course_id'=>$courseId];
        $question = $this->request->param('question','','string');
        if(!empty($question))
            $where['msg'] = ['like','%'.$question.'%'];
        $question = $messageModel->where($where)
            ->order('create_time','asc')
            ->select();
        foreach ($question as $key=>$data){
            $table = $data['is_teacher']==1?'teachers':'students';
            $userName = Db::name($table)->where('Id',$data['user_id'])->column('name');
            $question[$key]['user_name'] = $userName;
        }
        return $this->returnJson('获取成功',true,1,$question);
    }

    public function addquestion(){
        $courseId = $this->request->param('courseId','','int');
        $course = Db::name('courses')->find('Id',$courseId)->find();
        if(empty($course))
            return $this->returnJson('课程不存在');
        $user = $this->getUser();
        $courseStudent = Db::name('course_students')->where(['stu_id'=>$user['Id'],'course_id'=>$courseId])->find();
        if(empty($courseStudent))
            return $this->returnJson('发言请先加入课程');
        $msg = $this->request->param('msg','','string');
        if(empty($msg))
            return $this->returnJson('发送内容不能为空');
        $add = ['user_id'=>$user['Id'],'msg'=>$msg,'course_id'=>$courseId,'create_time'=>time()];
        $res = Db::name('message')->insert($add);
        if(!$res)
            return $this->returnJson('发布问题失败，请重试');
        return $this->returnJson('发布成功',true,1);
    }

    public function answer(){
        $fatherId = $this->request->param('id','','Id');
        $message = Db::name('message')->where('Id',$fatherId)->find();
        if(empty($message))
            return $this->returnJson('留言不存在,刷新重试');
        $answers = Db::name('message')->where('father_id',$fatherId)->order('create_time','asc')->select();
        foreach ($answers as $key=>$answer){
            $table = $answer['is_teacher']==1?'teachers':'students';
            $userName = Db::name($table)->where('Id',$answer['user_id'])->column('name');
            $answers[$key]['user_name'] = $userName;
        }
        return $this->returnJson('获取成功',true,1,$answers);
    }

    public function addanswer(){
        $user = $this->getUser();
        $fatherId = $this->request->param('id','','int');
        $msg = $this->request->param('msg','','string');
        if(empty($msg))
            return $this->returnJson('回复消息不能为空');
        $message = Db::name('message')->where('Id',$fatherId)->find();
        if(empty($message))
            return $this->returnJson('消息不存在,刷新重试');
        $course = Db::name('courses')->find('Id',$message['course_id'])->find();
        if(empty($course))
            return $this->returnJson('课程不存在');
        $courseStudent = Db::name('course_students')->where(['stu_id'=>$user['Id'],'course_id'=>$course['Id']])->find();
        if(empty($courseStudent))
            return $this->returnJson('发言请先加入课程');
        $add = ['course_id'=>$message['course_id'],'is_teacher'=>0,'user_id'=>$user['Id'],'father_id'=>$fatherId,'msg'=>$msg,'create_time'=>time()];
        $res = Db::name('message')->insert($add);
        if(!$res)
            return $this->returnJson('回复失败');
        return $this->returnJson('回复成功',true,1);
    }

    public function alterpwd(){
        $teacher = $this->getUser();
        $request = $this->request;
        $oldpwd = $request->param('oldpwd','','string');
        $newpwd1 = $request->param('newpwd1','','string');
        $newpwd2 = $request->param('newpwd2','','string');
        if($newpwd1 != $newpwd2)
//            return $this->returnJson('两次密码输入不正确');
            return $this->error('两次密码输入不正确');
        if(strlen($newpwd1)<6)
//            return $this->returnJson('密码长度要大于6位');
            return $this->error('密码长度要大于6位');
        if($oldpwd == $newpwd1)
//            return $this->returnJson('新密码不可以与原密码相同');
            return $this->error('新密码不可以与原密码相同');
        $oldpwdSha1 = sha1($oldpwd.substr($teacher['idcard'],-4));
        if(strcasecmp($oldpwdSha1,$teacher['password'])!==0)
//            return $this->returnJson('原密码不正确');
            return $this->error('原密码不正确');
        $password = sha1($newpwd1.substr($teacher['idcard'],-4));
        $upData = ['Id'=>$teacher['Id'],'password'=>$password];
        $res = Db::name('students')->update($upData);
        if(!$res)
            return $this->error('更改密码失败');
//            return $this->returnJson('更改密码失败');
        Session::delete('student_user');
//        return $this->returnJson('修改成功',true,1);
        return $this->success('修改成功','/');
    }

    public function groom(){
        $user =$this->getUser();
        $courseId = Db::name('course_students')->where('stu_id',$user['Id'])->column('course_id');
        $Ids = Db::name('courses')->where('Id','not in',$courseId)->column('Id');
        $max = count($Ids);
        if($max === 0)
            return $this->returnJson('没有推荐课程');
        $imax = $max < 5? $max:5;
        $in = array();
        shuffle($Ids);
        for ($i=0;$i<$imax;$i++){
            $in[] = $Ids[$i];
        }
        $courses = Db::name('courses')->where('Id','in',$in)->select();
        foreach ($courses as $key =>$course){
            $teacherName = Db::name('teachers')->where('Id',$course['teacher_id'])->column('name');
            $courses[$key]['teacher_name'] = $teacherName;
        }
        return $this->returnJson('获取成功',true,1,$courses);
    }

    public function changepwd(){
        return $this->fetch();
    }

    public function apply(){
        $courseId = $this->request->param('id','','int');
        $reason = $this->request->param('reason','','string');
        $course = Db::name('courses')->where('Id',$courseId)->find();
        if(empty($course))
            return $this->returnJson('课程不存在');
        $user = $this->getUser();
        $exist = Db::name('apply')->where(['stu_id'=>$user['Id'],'course_id'=>$courseId])->find();
        if(!empty($exist))
            return $this->returnJson('已经发起了申请');
        $joinExist = Db::name('course_students')->where(['stu_id'=>$user['Id'],'course_id'=>$courseId])->find();
        if(!empty($joinExist))
            return $this->returnJson('已经加入该课程');
        $add =['stu_id'=>$user['Id'],'course_id'=>$courseId,'reason'=>$reason,'create_time'=>time()];
        $res = Db::name('apply')->insert($add);
        if(!$res)
            return $this->returnJson('申请失败，请重试');
        return $this->returnJson('申请成功',true,1);
    }

    public function chat(){
        $name = $this->request->param('name','','string');
        $this->assign('name',$name);
        $user = $this->getUser();
        $teacherId = $this->request->param('teacherId',0,'int');
        $teacher = Db::name('teachers')->where('Id',$teacherId)->find();
        $this->assign('teacher',$teacher);
//        if(empty($teacher))
//            return $this->fetch();
        return $this->fetch();
    }

    public function sschat(){

        return $this->fetch();
    }
}