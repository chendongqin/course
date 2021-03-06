<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/2/14
 * Time: 21:45
 */
namespace app\teacher\controller;
use base\Teacherbase;
use think\Db;
use think\Loader;
use think\Session;
use ku\Tool;

class Index extends Teacherbase{

    public function index(){
        $user = $this->getUser();
        $pubOrigin  = Db::name('public_origin')->order('create_time','desc')->limit(10)->select();
        $this->assign('origin',$pubOrigin);
        $mycourse = Db::name('courses')->where(['teacher_id'=>$user['Id'],'end_time'=>['>',time()]])->select();
        $this->assign('mycourse',$mycourse);
        $courseIds = Db::name('courses')->where(['teacher_id'=>$user['Id'],'end_time'=>['>',time()]])->column('Id');
        $applys = Db::name('apply')->where('course_id','in',$courseIds)->order('create_time','desc')->limit(10)->select();
        foreach ($applys as $key=>$apply){
            $course = Db::name('courses')->where('Id',$apply['course_id'])->find();
            $applys[$key]['courseName'] = $course['name'];
            $student = Db::name('students')->where('Id',$apply['stu_id'])->find();
            $applys[$key]['stuName'] = $student['name'];
        }
        $this->assign('applys',$applys);
        return $this->fetch();
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
        $upData = ['password'=>$password];
        $res = Db::name('teachers')->where('Id',$teacher['Id'])->update($upData);
        if(!$res)
            return $this->returnJson('更改密码失败');
        Session::delete('teacher_user');
        return $this->returnJson('修改成功',true,1);
    }

    public function  courseadd(){
        return $this->fetch();
    }

    public function addcourse(){
        $user = $this->getUser();
        $add = [];
        $request = $this->request;
        $add['name'] = $request->param('name','','string');
        if(empty($add['name']))
//            return $this->returnJson('课程名不能为空');
            return $this->error('课程名不能为空');
        $add['credit'] = $request->param('credit','','string');
        if(!is_numeric($add['credit']))
//            return $this->returnJson('学分数据不正确');
            return $this->error('学分数据不正确');
        $add['image'] = $request->param('image','','string');
        if(!empty($add['image']) and !file_exists(PUBLIC_PATH.$add['image']))
            return $this->error('图片不存在');
//            return $this->returnJson('图片不存在');
        $add['start_time'] = strtotime($request->param('start_time','','string'));
        $add['end_time'] = strtotime($request->param('end_time','','string'));
        if($add['start_time'] >$add['end_time'])
            return $this->error('开始时间不能大于结束时间');
//            return $this->returnJson('开始时间不能大于结束时间');
        $add['describe'] = $request->param('describe','','string');
        $add['teacher_id'] = $user['Id'];
        $add['create_time'] = time();
        $string = join('',array_merge(range(0,9),range('A','Z')));
        $code = '';
        for($i=0;$i<6;$i++){
            $text = str_shuffle($string){0};
            $code .= $text;
        }
        $add['code'] = $code;
        $res = Db::name('courses')->insert($add);
        if($res)
          return $this->success('创建课程成功');
//          return $this->returnJson('创建课程成功',true,1);
        return $this->error('创建失败');
//        return $this->returnJson('创建失败');
    }

    public function mycourse(){
        $page = $this->request->param('page',1,'int');
        $user = $this->getUser();
        $courseModel = Db::name('courses');
        $where = ['teacher_id'=>$user['Id']];
        $where['end_time'] = ['>=',time()];
        $pager = $courseModel->where($where)->order('create_time','asc')->paginate(6,false,['page'=>$page])->toArray();
        $this->assign('pager',$pager);
        return $this->fetch();
    }

    public function overcourse(){
        $page = $this->request->param('page',1,'int');
        $user = $this->getUser();
        $courseModel = Db::name('courses');
        $where = ['teacher_id'=>$user['Id']];
        $where['end_time'] = ['<',time()];
        $pager = $courseModel->where($where)->paginate(6,false,['page'=>$page])->toArray();
        $this->assign('pager',$pager);
        return $this->fetch();
    }

    public function question(){
        $request = $this->request;
        $courseId = $request->param('id','','int');
        $page = $request->param('page',1,'int');
        $user = $this->getUser();
        $course = Db::name('courses')->where('Id',$courseId)->find();
        $this->assign('course',$course);
        if($user['Id']!=$course['teacher_id']){
            $this->assign('error','您不是创建者');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
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

    public function answer(){
        $fatherId = $this->request->param('fatherId','','int');
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
        $add = ['course_id'=>$message['course_id'],'is_teacher'=>1,'user_id'=>$user['Id'],'father_id'=>$fatherId,'msg'=>$msg,'create_time'=>time()];
        $res = Db::name('message')->insert($add);
        if(!$res)
            return $this->returnJson('回复失败');
        return $this->returnJson('回复成功',true,1);
    }

    public function addQuestion(){
        $user = $this->getUser();
        $courseid = $this->request->param('courseid','','int');
        $msg = $this->request->param('msg','','string');
        if(empty($msg))
            return $this->returnJson('回复消息不能为空');
        $course = Db::name('courses')->where('Id',$courseid )->find();
        if(empty($course))
            return $this->returnJson('课程不存在,刷新重试');
        if($course['teacher_id']!==$user['Id'])
            return $this->returnJson('你没有权限发言');
        $add = ['course_id'=>$courseid,'is_teacher'=>1,'user_id'=>$user['Id'],'father_id'=>0,'msg'=>$msg,'create_time'=>time()];
        $res = Db::name('message')->insert($add);
        if(!$res)
            return $this->returnJson('发布失败');
        return $this->returnJson('发布成功',true,1);
    }

    public function scorelist(){
        $courseId = $this->request->param('courseId','','int');
        $scorelist = Db::table('score')
            ->alias('a')
            ->join('students s','a.stu_id = s.Id','LEFT')
            ->where('a.course_id',$courseId)
            ->select();
        $this->assign('scorelist',$scorelist);
        return $this->fetch();
    }

    public function applylist(){
        $page = (int)$this->request->param('page',1,'int');
        $user = $this->getUser();
        $courseIds = Db::name('courses')->where(['teacher_id'=>$user['Id'],'end_time'=>['>',time()]])->column('Id');
        $applys = Db::name('apply')->where('course_id','in',$courseIds)->paginate(15,false,['page'=>$page])->toArray();
        foreach ($applys['data'] as $key=>$apply){
            $user = Db::name('students')->where('Id',$apply['stu_id'])->find();
            $applys['data'][$key]['stuName'] = $user['name'];
            $course = Db::name('courses')->where('Id',$apply['course_id'])->find();
            $applys['data'][$key]['courseName'] = $course['name'];
        }
        $this->assign('pager',$applys);
        return $this->fetch();
    }

    public function pass(){
        $user = $this->getUser();
        $id = $this->request->param('id','','int');
        $apply = Db::name('apply')->where('Id',$id)->find();
        if(empty($apply))
            return $this->returnJson('申请不存在');
        $course = Db::name('courses')->where('Id',$apply['course_id'])->find();
        if(empty($course))
            return $this->returnJson('课程不存在');
        if($course['teacher_id']!=$user['Id'])
            return $this->returnJson('您没有权限');
        Db::startTrans();
        $res = Db::name('apply')->where('Id',$id)->delete();
        if(!$res){
            return $this->returnJson('操作失败');
        }
        $virefy = Db::name('course_students')->where(['course_id'=>$apply['course_id'],'stu_id'=>$apply['stu_id']])->find();
        if(!empty($virefy))
            return $this->returnJson('已加入课程');
        $add = ['course_id'=>$apply['course_id'],'stu_id'=>$apply['stu_id'],'create_time'=>time()];
        $res = Db::name('course_students')->insert($add);
        if(!$res){
            Db::rollback();
            return $this->returnJson('处理失败,请重试');
        }
        Db::commit();
        return $this->returnJson('操作成功',true,1);
    }

    public function refuse(){
        $user = $this->getUser();
        $id = $this->request->param('id','','int');
        $apply = Db::name('apply')->where('Id',$id)->find();
        if(empty($apply))
            return $this->returnJson('申请不存在');
        $course = Db::name('courses')->where('Id',$apply['course_id'])->find();
        if(empty($course))
            return $this->returnJson('课程不存在');
        if($course['teacher_id']!=$user['Id'])
            return $this->returnJson('您没有权限');
        $res = Db::name('apply')->where('Id',$id)->delete();
        if(!$res)
            return $this->returnJson('处理失败,请重试');
        return $this->returnJson('操作成功',true,1);
    }

    public function changepwd(){
        return $this->fetch();
    }

    public function coursedetail(){
        $user = $this->getUser();
        $request = $this->request;
        $courseId = $request->param('id','','int');
        $course = Db::name('courses')->where('Id',$courseId)->find();
        if(empty($course))
            return $this->fetch(APP_PATH.'index/view/index/error.html',['error'=>'课程不存在']);
        $this->assign('course',$course);
        if($user['Id']!= $course['teacher_id'])
            return $this->fetch(APP_PATH.'index/view/index/error.html',['error'=>'您没有权限查看']);
        $courseware = Db::name('courseware')->where('Id',$courseId)->select();
        $this->assign('coursewares',$courseware);
        $taskJobs = Db::name('task_job')
            ->where('course_id',$courseId)
            ->order('create_time')
            ->select(); 
        $this->assign('tasks',$taskJobs);
        return $this->fetch();
    }
    public function questionJson(){
        $request = $this->request;
        $courseId = $request->param('id','','int');
        $user = $this->getUser();
        $course = Db::name('courses')->where('Id',$courseId)->find();
        $this->assign('course',$course);
        if($user['Id']!=$course['teacher_id'])
            return $this->returnJson('您没有权限');
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
            $anwers = $messageModel->where('father_id',$data['Id'])->select();
            foreach ($anwers as $k=>$anwer){
                $table = $anwer['is_teacher']==1?'teachers':'students';
                $userName = Db::name($table)->where('Id',$data['user_id'])->column('name');
                $anwers[$k]['user_name'] = $userName;
            }
            $question[$key]['anwers'] = $anwers;
        }
        return $this->returnJson('获取成功',true,1,$question);
    }

    public function chat(){
       $user = $this->getUser();
       $stuId = $this->request->param('stuId',0,'int');
       $stuName = $this->request->param('stuName','','string');
       $this->assign('stuName',$stuName);
       $student = Db::name('students')->where('Id',$stuId)->find();
       $this->assign('stu',$student);
       if(empty($student))
           return $this->fetch();
        $where = ['t_id'=>$user['Id'],'s_id'=>$stuId,'sender'=>0,'is_see'=>0];
        Db::name('st_chat')->where($where)->update(['is_see'=>1]);
        return $this->fetch();
    }


}