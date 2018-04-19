<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/4/9
 * Time: 0:30
 */
namespace app\student\controller;

use think\Db;
use base\Studentbase;

class Chat extends Studentbase{

    public function index(){
        $user = $this->getUser();
        $tid = $this->request->param('tId','','int');
        $sid = $user['Id'];
        $where = ['s_id'=>$sid,'t_id'=>$tid];
        $chats = Db::name('st_chat')->where($where)->order('time','desc')->limit(8)->select();
        $data = [];
        foreach ($chats as $chat){
            $data[$chat['Id']] = $chat;
        }
        ksort($data);
        return $this->returnJson('获取成功',true,1,$data);
    }

    public function all(){
        $user = $this->getUser();
        $tid = $this->request->param('tId','','int');
        $sid = $user['Id'];
        $where = ['s_id'=>$sid,'t_id'=>$tid];
        $chats = Db::name('st_chat')->where($where)->order('time','asc')->select();
        return $this->returnJson('获取成功',true,1,$chats);
    }

    public function swiths(){
        $user = $this->getUser();
        $fid = $user['Id'];
        $tid = $this->request->param('toId','','int');
        $where = ['from_id|to_id'=>$fid];
        $chats = Db::name('ss_chat')->where($where)->whereOr('from_id|to_id',$tid)->order('time','desc')->limit(8)->select();
        $data = [];
        foreach ($chats as $chat){
            $data[$chat['Id']] = $chat;
        }
        ksort($data);
        return $this->returnJson('获取成功',true,1,$data);
    }

    public function swithsAll(){
        $user = $this->getUser();
        $fid = $user['Id'];
        $tid = $this->request->param('toId','','int');
        $where = ['from_id|to_id'=>$fid];
        $chats = Db::name('ss_chat')->where($where)->whereOr('from_id|to_id',$tid)->order('time','asc')->select();
        return $this->returnJson('获取成功',true,1,$chats);
    }

    public function addst(){
        $user = $this->getUser();
        $teacherId = $this->request->param('id','','int');
        $msg = $this->request->param('msg','','string');
        if(strlen($msg)>556){
            return $this->returnJson('消息过长');
        }
        $teacher = Db::name('teachers')->where('Id',$teacherId)->find();
        if(empty($teacher))
            return $this->returnJson('老师不存在');
        $add = ['s_id'=>$user['Id'],'t_id'=>$teacherId,'msg'=>$msg,'time'=>time(),'sender'=>0];
        $res = Db::name('st_chat')->insert($add);
        if(!$res)
            return $this->returnJson('操作失败,请重发');
        return $this->returnJson('发送成功',true,1);
    }

    public function addss(){
        $user = $this->getUser();
        $studentId = $this->request->param('id','','int');
        if($studentId == $user['Id'])
            return $this->returnJson('不能给自己发信息');
        $msg = $this->request->param('msg','','string');
        if(strlen($msg)>556){
            return $this->returnJson('消息过长');
        }
        $student = Db::name('students')->where('Id',$studentId)->find();
        if(empty($student))
            return $this->returnJson('学生不存在');
        $add = ['from_id'=>$user['Id'],'to_id'=>$studentId,'msg'=>$msg,'time'=>time(),'sender'=>0];
        $res = Db::name('st_chat')->insert($add);
        if(!$res)
            return $this->returnJson('操作失败,请重发');
        return $this->returnJson('发送成功',true,1);
    }

    public function dingNum(){
        $user = $this->getUser();
        $where = ['s_id'=>$user['Id'],'sender'=>1,'is_see'=>0];
        $num1 = Db::name('st_chat')->where($where)->count();
        $where = ['to_id'=>$user['Id'],'is_see'=>0];
        $num2 = Db::name('ss_chat')->where($where)->count();
        $num = $num1+$num2;
        return $this->returnJson('获取成功',true,1,['num'=>$num]);
    }
    public function ding(){
        $user = $this->getUser();
        $where = ['s_id'=>$user['Id'],'sender'=>1,'is_see'=>0];
        $stNoSee = Db::name('st_chat')->where($where)->select();
        $where = ['to_id'=>$user['Id'],'is_see'=>0];
        $ssNoSee = Db::name('ss_chat')->where($where)->select();
        $data = ['st'=>$stNoSee,'ss'=>$ssNoSee];
        return $this->returnJson('获取成功',true,1,$data);
    }

    //获取聊天学生
    public function chatStu(){
        $user = $this->getUser();
        $stuName = $this->request->param('stuName','','string');
        $where = [];
        if(!empty($stuName))
            $where['name'] = ['like','%'.$stuName.'%'];
        $courseIds = Db::name('course_students')->where('stu_id',$user['Id'])->column('course_id');
        $studentIds = Db::name('course_students')->where('course_id','in',$courseIds)->where('stu_id','<>',$user['Id'])->column('stu_id');
        $where['Id'] = ['in',$studentIds];
        $students = Db::name('students')->where($where)->select();
        return $this->returnJson('获取成功',true,1,$students);
    }


    //获取聊天学生
    public function chatTeacher(){
        $user = $this->getUser();
        $name = $this->request->param('name','','string');
        $where = [];
        if(!empty($name))
            $where['name'] = ['like','%'.$name.'%'];
        $courseIds = Db::name('course_students')->where('stu_id',$user['Id'])->column('course_id');
        $teachers =  Db::name('courses')->where('teacher_id','in',$courseIds)->select();
        return $this->returnJson('获取成功',true,1,$teachers);
    }


}