<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/4/9
 * Time: 0:30
 */
namespace app\teacher\controller;

use think\Db;
use base\Teacherbase;

class Chat extends Teacherbase{

    public function index(){
        $sid = (int)$this->request->param('stuId','','int');
//        $tid = $this->request->param('tId','','int');
        $user = $this->getUser();
        $tid = $user['Id'];
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
        $sid = $this->request->param('stuId','','int');
        $tid = $user['Id'];
        $where = ['s_id'=>$sid,'t_id'=>$tid];
        $chats = Db::name('st_chat')->where($where)->order('time','asc')->select();
        return $this->returnJson('获取成功',true,1,$chats);
    }

    public function add(){
        $user = $this->getUser();
        $studentId = $this->request->param('id','','int');
        $msg = $this->request->param('msg','','string');
        if(strlen($msg)>556){
            return $this->returnJson('消息过长');
        }
        $student = Db::name('students')->where('Id',$studentId)->find();
        if(empty($student))
            return $this->returnJson('学生不存在');
        $add = ['t_id'=>$user['Id'],'s_id'=>$studentId,'msg'=>$msg,'time'=>time(),'sender'=>1];
        $res = Db::name('st_chat')->insert($add);
        if(!$res)
            return $this->returnJson('操作失败,请重发');
        return $this->returnJson('发送成功',true,1);
    }

    //未读信息数量
    public function dingNum(){
        $sId = $this->request->param('sid',0,'int');
        $user = $this->getUser();
        $where = ['t_id'=>$user['Id'],'s_id'=>$sId,'sender'=>0,'is_see'=>0];
        $num = Db::name('st_chat')->where($where)->count();
        return $this->returnJson('获取成功',true,1,['num'=>$num]);
    }

    //未读信息
    public function ding(){
        $user = $this->getUser();
        $where = ['t_id'=>$user['Id'],'sender'=>0,'is_see'=>0];
        $noSee = Db::name('st_chat')->where($where)->select();
        return $this->returnJson('获取成功',true,1,$noSee);
    }

    //获取聊天学生
    public function chatStu(){
        $user = $this->getUser();
        $stuName = $this->request->param('stuName','','string');
        $where = [];
        if(!empty($stuName))
            $where['name'] = ['like','%'.$stuName.'%'];
        $courseIds = Db::name('courses')->where('teacher_id',$user['Id'])->column('Id');
        $studentIds = Db::name('course_students')->where('course_id','in',$courseIds)->column('stu_id');
        $where['Id'] = ['in',$studentIds];
        $students = Db::name('students')->where($where)->select();
        return $this->returnJson('获取成功',true,1,$students);
    }



}