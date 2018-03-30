<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/25
 * Time: 22:45
 */
namespace app\student\controller;
use base\Studentbase;
use think\Db;

class Task extends Studentbase{

    public function index(){
        $request = $this->request;
        $courseId = $request->param('id','','int');
        $page = $request->param('page',1,'int');
        $user = $this->getUser();
        $courseStuModel = Db::name('course_students');
        $myJoin = $courseStuModel->where(['course_id'=>$courseId,'stu_id'=>$user['Id']])->find();
        if(empty($myJoin)){
            $this->assign('error','您没有加入该课程');
            return $this->fetch('error');
        }
        $course = Db::name('courses')->where('Id',$courseId)->find();
        $this->assign('course',$course);
        $teacher = Db::name('teacher')->where('Id',$course['teacher_id'])->find();
        $this->assign('teacher',$teacher);
        $taskJob = Db::name('task_job')
            ->where('course_id',$courseId)
            ->order('create_time')
            ->paginate(10,false,['page'=>$page])
            ->toArray();
        $taskModel = Db::name('task');
        foreach ($taskJob['data'] as $key=>$job){
            $task = $taskModel->where(['task_id'=>$job['Id'],'stu_id'=>$user['Id']]);
            $taskJob['data'][$key]['task'] = $task;
        }
        $this->assign('taskJob',$taskJob);
        return $this->fetch();
    }

    public function down(){
        $Id = $this->request->param('id','','int');
        $taskJob = Db::name('task_job')->where('Id',$Id)->find();
        if(empty($taskJob)){
            $this->assign('error','作业不存在');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $user = $this->getUser();
        $courseStudent = Db::name('course_students')
            ->where(['course_id'=>$taskJob['course_id'],'stu_id'=>$user['Id']])
            ->find();
        if(empty($courseStudent)){
            $this->assign('error','您没有加入该课程，无法下载');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        if(empty($taskJob['thumb'])){
            $this->assign('error','改作业没有下载文件按');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        if(!file_exists(PUBLIC_PATH.$taskJob['thumb'])) {
            $this->assign('error','文件不存在');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $fileArr = explode('.',$taskJob['thumb']);
        $type = end($fileArr);
        header('Content-Type:application/'.$type);
        header('Content-Disposition:attachment;filename='.$taskJob['name'].'.'.$type);
        header('Cache-Control:max-age=0');
        readfile(PUBLIC_PATH.$taskJob['thumb']);
        exit();
    }

}