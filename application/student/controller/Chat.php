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
        $sid = $this->request->param('stuId','','int');
        $tid = $this->request->param('tId','','int');
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
        $sid = $this->request->param('stuId','','int');
        $tid = $this->request->param('tId','','int');
        $where = ['s_id'=>$sid,'t_id'=>$tid];
        $chats = Db::name('st_chat')->where($where)->order('time','asc')->select();
        return $this->returnJson('获取成功',true,1,$chats);
    }

    public function swiths(){
        $fid = $this->request->param('fromId','','int');
        $tid = $this->request->param('toId','','int');
        $where = ['from_id'=>$fid,'to_id'=>$tid];
        $chats = Db::name('ss_chat')->where($where)->order('time','desc')->limit(8)->select();
        $data = [];
        foreach ($chats as $chat){
            $data[$chat['Id']] = $chat;
        }
        ksort($data);
        return $this->returnJson('获取成功',true,1,$data);
    }

    public function swithsAll(){
        $fid = $this->request->param('fromId','','int');
        $tid = $this->request->param('toId','','int');
        $where = ['from_id'=>$fid,'to_id'=>$tid];
        $chats = Db::name('ss_chat')->where($where)->order('time','asc')->select();
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

}