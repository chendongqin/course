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
use think\Config;
use ku\Upload as kuUpload;
use ku\Pdf;


class Task extends Studentbase{

    private $_fileType = array('docx','doc','pdf');
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

    public function see(){
        $user = $this->getUser();
        $Id = $this->request->param('id','','int');
        $task = Db::name('task_job')->where(['Id'=>$Id])->find();
        if(empty($task)){
            $this->assign('error','没有该作业');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }

        $course = Db::name('course_students')->where(['course_id'=>$task['course_id'],'stu_id'=>$user['Id']])->find();
        if(empty($course)){
            $this->assign('error','你没有权限查看');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        if(!is_file(PUBLIC_PATH.$task['thumb']) or empty($task['thumb'])){
            $this->assign('error','没有改文件');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $url = Config::get('myUrl');
        $this->redirect($url.$task['thumb']);
    }

    public function add(){
        $taskJobId = $this->request->param('task_id','','int');
        $taskJob = Db::name('task_job')->where('Id',$taskJobId)->find();
        if(empty($taskJob))
            return $this->error('作业不存在');
        $upload = new kuUpload();
        $upload->setSupportResource(array());
        $upload->setSupportSuffix($this->_fileType);
        $upload->setFormName('taskFile');
        $result = $upload->exec();
        if(!$result){
            $error = array_values($upload->getErrval());
            $str = is_array($error)?implode(',',$error):$error;
            return $this->error($str);
        }
        $filename = $upload->getFilename();
        $fileArr = explode('.',$filename);
        $fileType = end($fileArr);
        $path = $upload->path('/uploads/student/task/');
        $upload->buildCode();
        $code = $upload->getRetval();
        $fileName = $path.$code['code'].'.'.$fileType;
        $result = $upload->moveFile($fileName);
        if(!$result){
            $error = array_values($upload->getErrval());
            $str = is_array($error)?implode(',',$error):$error;
            return $this->error($str);
        }
        if($fileType != 'pdf'){
            $converter = new Pdf();
            $source = $fileName;
            $export = $path.$code['code'].'.pdf';
            $converter->execute($source, $export);
            $fileName = $export;
            @unlink($source);
        }
        $file = str_replace(PUBLIC_PATH,'',$fileName);
        $user = $this->getUser();
        $add = ['stu_id'=>$user['Id'],'course_id'=>$taskJob['course_id'],'thumb'=>$file,'task_id'=>$taskJobId,'create_time'=>time()];
        $res = Db::name('task')->insert($add);
        if($res)
            return $this->success('上传成功');
        return $this->error('上传失败');
    }




}