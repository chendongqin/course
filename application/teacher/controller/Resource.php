<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/29
 * Time: 8:52
 */
namespace app\teacher\controller;

use base\Teacherbase;
use think\Db;

class Resource extends Teacherbase{

    //课件列表
    public function index(){
        $user = $this->getUser();
        $courseId = $this->request->param('courseId','','int');
        $page = $this->request->param('page',1,'int');
        $course = Db::name('courses')->where('Id',$courseId) ->find();
        if(empty($course)){
            $this->assign('error','课程不存在');
            $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        if($course['teacher_id']!=$user['Id']){
            $this->assign('error','您没有权限进入该课程课件');
            $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $courseWares = Db::name('courseware')->where(['course_id'=>$courseId,'teacher_id'=>$user['Id']])
            ->order('create_time','desc')
            ->paginate(10,false,['page'=>$page])->toArray();
        $this->assign('pager',$courseWares);
        return $this->fetch();

    }
    //资源列表
    public function origin(){
        $user = $this->getUser();
        $courseId = $this->request->param('courseId','','int');
        $page = $this->request->param('page',1,'int');
        $course = Db::name('courses')->where('Id',$courseId) ->find();
        if(empty($course)){
            $this->assign('error','课程不存在');
            $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        if($course['teacher_id']!=$user['Id']){
            $this->assign('error','您没有权限进入该课程课件');
            $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $origins = Db::name('origin')->where(['course_id'=>$courseId,'teacher_id'=>$user['Id']])
            ->order('create_time','desc')
            ->paginate(10,false,['page'=>$page])->toArray();
        $this->assign('pager',$origins);
        return $this->fetch();
    }
    //添加
    public function add(){
        $user = $this->getUser();
        $name = $this->request->param('name','','string');
        $thumb = $this->request->param('thumb','','string');
        $courseId = $this->request->param('courseId','','string');
        if(empty($thumb))
            return $this->returnJson('课件路径不为空');
        if(!is_file(PUBLIC_PATH.$thumb))
            return $this->returnJson('文件不存在');
        $course = Db::name('courses')->where('Id',$courseId) ->find();
        if(empty($course)){
            $this->returnJson('课程不存在');

        }
        if($course['teacher_id']!=$user['Id']){
            $this->returnJson('您没有权限添加该课程课件');
        }
        $add = ['teacher_id'=>$user['Id'],'name'=>$name,'thumb'=>$thumb,'create_time'=>time(),'course_id'=>$courseId];
        $res = Db::name('courseware')->insert($add);
        if($res)
            return $this->returnJson('上传课件成功',true,1);
        return $this->returnJson('上传失败，请重试');
    }

    public function addorigin(){
        $user = $this->getUser();
        $name = $this->request->param('name','','string');
        $thumb = $this->request->param('thumb','','string');
        $courseId = $this->request->param('courseId','','string');
        if(empty($thumb))
            return $this->returnJson('课件路径不为空');
        if(!is_file(PUBLIC_PATH.$thumb))
            return $this->returnJson('文件不存在');
        $course = Db::name('courses')->where('Id',$courseId) ->find();
        if(empty($course)){
            $this->returnJson('课程不存在');

        }
        if($course['teacher_id']!=$user['Id']){
            $this->returnJson('您没有权限添加该课程资源');
        }
        $add = ['teacher_id'=>$user['Id'],'name'=>$name,'thumb'=>$thumb,'create_time'=>time(),'course_id'=>$courseId];
        $res = Db::name('origin')->insert($add);
        if($res)
            return $this->returnJson('上传资源成功',true,1);
        return $this->returnJson('上传失败，请重试');
    }
    //删除
    public function delete(){
        $id = $this->request->param('id','','int');
        $wareModel = Db::name('courseware');
        $ware = $wareModel->where('Id',$id)->find();
        if(empty($ware))
            return $this->returnJson('课件不存在');
        $user = $this->getUser();
        if($ware['teacher_id'] != $user['Id'])
            return $this->returnJson('您没有权限删除该课件');
        $res = Db::table('courseware')->where('Id',$ware['Id'])->delete();
        if(!$res)
            return $this->returnJson('删除失败，请重试');
        @unlink(PUBLIC_PATH.$ware['thumb']);
        return $this->returnJson('删除成功',true,1);
    }

    public function delorigin(){
        $id = $this->request->param('id','','int');
        $wareModel = Db::name('origin');
        $origin = $wareModel->where('Id',$id)->find();
        if(empty($origin))
            return $this->returnJson('课件不存在');
        $user = $this->getUser();
        if($origin['teacher_id'] != $user['Id'])
            return $this->returnJson('您没有权限删除该课件');
        $res = Db::table('origin')->where('Id',$origin['Id'])->delete();
        if(!$res)
            return $this->returnJson('删除失败，请重试');
        @unlink(PUBLIC_PATH.$origin['thumb']);
        return $this->returnJson('删除成功',true,1);
    }
}