<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/29
 * Time: 8:52
 */
namespace app\student\controller;

use base\Studentbase;
use think\Db;

class Resource extends Studentbase{

    //课件列表
    public function index(){
        $courseId= $this->request->param('courseId','','int');
        $page= $this->request->param('page','','int');
        $user = $this->getUser();
        $course = Db::name('courses')->where('Id',$courseId)->find();
        if(empty($course)){
            $this->assign('error','课程不存在');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $courseStudent = Db::name('course_students')->where(['stu_id'=>$user['Id'],'course_id'=>$courseId])->find();
        if(empty($courseStudent)){
            $this->assign('error','您没有加入该课程');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $coursewares = Db::name('courseware')
            ->where('course_id',$courseId)
            ->order('create_time','desc')
            ->paginate(10,fasle,['page'=>$page])->toArray();
        $this->assign('pager',$coursewares);
        return $this->fetch();
    }
    //资源
    public function origin(){
        $courseId= $this->request->param('courseId','','int');
        $page= $this->request->param('page','','int');
        $user = $this->getUser();
        $course = Db::name('courses')->where('Id',$courseId)->find();
        if(empty($course)){
            $this->assign('error','课程不存在');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $courseStudent = Db::name('course_students')->where(['stu_id'=>$user['Id'],'course_id'=>$courseId])->find();
        if(empty($courseStudent)){
            $this->assign('error','您没有加入该课程');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $origins = Db::name('origin')
            ->where('course_id',$courseId)
            ->order('create_time','desc')
            ->paginate(10,fasle,['page'=>$page])->toArray();
        $this->assign('pager',$origins);
        return $this->fetch();
    }

    public function down(){
        $Id = $this->request->param('id','','int');
        $ware = Db::name('courseware')->where('Id',$Id)->find();
        if(empty($ware)){
            $this->assign('error','课件不存在');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $user = $this->getUser();
        $courseStudent = Db::name('course_students')
            ->where(['course_id'=>$ware['course_id'],'stu_id'=>$user['Id']])
            ->find();
        if(empty($courseStudent)){
            $this->assign('error','您没有加入该课程，无法下载');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        if(!is_file(PUBLIC_PATH.$ware['thumb'])) {
            $this->assign('error','文件不存在');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $fileArr = explode('.',$ware['thumb']);
        $type = end($fileArr);
        header('Content-Type:application/'.$type);
        header('Content-Disposition:attachment;filename='.$ware['name'].'.'.$type);
        header('Cache-Control:max-age=0');
        readfile(PUBLIC_PATH.$ware['thumb']);
        exit();
    }

    public function downorigin(){
        $Id = $this->request->param('id','','int');
        $origin = Db::name('origin')->where('Id',$Id)->find();
        if(empty($origin)){
            $this->assign('error','课件不存在');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $user = $this->getUser();
        $courseStudent = Db::name('course_students')
            ->where(['course_id'=>$origin['course_id'],'stu_id'=>$user['Id']])
            ->find();
        if(empty($courseStudent)){
            $this->assign('error','您没有加入该课程，无法下载');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        if(!is_file(PUBLIC_PATH.$origin['thumb'])) {
            $this->assign('error','文件不存在');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $fileArr = explode('.',$origin['thumb']);
        $type = end($fileArr);
        header('Content-Type:application/'.$type);
        header('Content-Disposition:attachment;filename='.$origin['name'].'.'.$type);
        header('Cache-Control:max-age=0');
        readfile(PUBLIC_PATH.$origin['thumb']);
        exit();
    }


}