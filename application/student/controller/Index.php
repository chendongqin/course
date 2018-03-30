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
class Index extends Studentbase{

    public function index(){
        echo 'welcome!';
        return $this->fetch('index');
    }

    public function course(){
        $user = $this->getUser();
        $where = [];
        $where['end_time'] = ['>=',time()];
        $request = $this->request;
        $name = $request->param('name','','string');
        if($name)
            $where['name'] = ['like','%'.$name.'%'];
        $teacherName = $request->param('teacherName','','string');
        if($teacherName){
            $Ids = Db::name('teachers')
                ->where(['name'=>['like','%'.$teacherName.'%']])->column('Id');
            $where['Id'] = ['in',implode(',',$Ids)];
        }
        $page = $request->param('page',1,'int');
        $courses  = Db::name('courses')->where($where)->paginate(6,false,['page'=>$page])->toArray();
        foreach ($courses['data'] as $key=>$value){
            $teacher = Db::name('teachers')->where('Id',$value['teacher_id'])->find();
            $courses['data'][$key]['teacherName'] = $teacher['name'];
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
            $courseStudents['data'][$key]['course'] = $course;
        }
        $this->assign('pager',$courseStudents);
        return $this->fetch();
    }
    //课程详情
    public function coursedetail(){
        $request = $this->request;
        $courseId = $request->param('id','','int');
        $page = $request->param('page',1,'int');
        $user = $this->getUser();
        $courseStuModel = Db::name('course_students');
        $myJoin = $courseStuModel->where(['course_id'=>$courseId,'stu_id'=>$user['Id']])->find();
        if(empty($myJoin)){
            $this->assign('error','您没有加入该课程');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $course = Db::name('courses')->where('Id',$courseId)->find();
        $this->assign('course',$course);
        $teacher = Db::name('teacher')->where('Id',$course['teacher_id'])->find();
        $this->assign('teacher',$teacher);
        $courseStudents = $courseStuModel
            ->where('course_id',$courseId)
            ->order('create_time','asc')
            ->paginate(10,false,['page'=>$page])
            ->toArray();
        $this->assign('students',$courseStudents);
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
        $teacher = Db::name('teacher')->where('Id',$course['teacher_id'])->find();
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
            $userName = Db::name($table)->where('Id',$data['user_id'])->column('name');
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
        $courseStudent = Db::name('course_students')->where(['stu_id'=>$user['Id'],'course_id'=>$courseId])->find();
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
            return $this->returnJson('两次密码输入不正确');
        if(strlen($newpwd1)<6)
            return $this->returnJson('密码长度要大于6位');
        if($oldpwd == $newpwd1)
            return $this->returnJson('新密码不可以与原密码相同');
        $oldpwdSha1 = sha1($oldpwd.substr($teacher['idcard'],-4));
        if(strcasecmp($oldpwdSha1,$teacher['password'])!==0)
            return $this->returnJson('原密码不正确');
        $password = sha1($newpwd1.substr($teacher['idcard'],-4));
        $upData = ['Id'=>$teacher['Id'],'password'=>$password];
        $res = Db::name('students')->update($upData);
        if(!$res)
            return $this->returnJson('更改密码失败');
        Session::delete('student_user');
        return $this->returnJson('修改成功',true,1);
    }
}