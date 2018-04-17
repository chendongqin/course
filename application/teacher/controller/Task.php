<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/26
 * Time: 16:59
 */
namespace  app\teacher\controller;

use base\Teacherbase;
use think\Db;
use think\Config;
use ku\Upload;
use ku\Pdf;

class Task extends Teacherbase{
    private $_fileType = array('docx','doc','pdf','ppt','pptx',);
    public function index(){
        $request = $this->request;
        $taskId= $request->param('taskId','','int');
        $taskJob = Db::name('task_job')->where('Id',$taskId)->find();
        if(empty($taskJob))
            return $this->fetch(APP_PATH.'index/view/index/error.html',['error'=>'作业不存在']);
        $this->assign('taskJob',$taskJob);
        $user = $this->getUser();
        $course = Db::name('courses')->where(['Id'=>$taskJob['course_id'],'teacher_id'=>$user['Id']])->find();
        if(empty($course)){
            $error = '您不是课程创建者，没有权限查看';
            $this->assign('error',$error);
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }
        $taskModel = Db::name('task');
        $tasks = $taskModel->where(['course_id'=>$taskJob['course_id'],'task_id'=>$taskId])
            ->order('create_time','asc')
            ->select();
        foreach ($tasks as $key=>$task){
            $user = Db::name('students')->where('Id',$task['stu_id'])->find();
            $tasks[$key]['userName'] = $user['name'];
            $tasks[$key]['stu_no'] = $user['stu_no'];
        }
        $this->assign('tasks',$tasks);
        return $this->fetch();
    }

    public function taskjob(){
        $user = $this->getUser();
        $courseId = $this->request->param('courseId','','int');
        $page = $this->request->param('page',1,'int');
        $taskJobModel = Db::name('task_job');
        $taskJobs = $taskJobModel->where(['teacher_id'=>$user['Id'],'course_id'=>$courseId])
            ->order('create_time','desc')
            ->paginate(10,false,['page'=>$page])
            ->toArray();
        $this->assign('pager',$taskJobs);
        return $this->fetch();
    }

    public function add(){
        $user = $this->getUser();
        $courseId = $this->request->param('courseId','','int');
        $course = Db::name('courses')->where('Id',$courseId)->find();
        if(empty($course))
            return $this->error('课程不存在');
        $name = $this->request->param('name','','string');
        $lastTime = $this->request->param('lastTime','','string');
        $lastTime = strtotime($lastTime);
        if($lastTime<time())
            return $this->error('截至时间不能小于当前时间');
        $describe = $this->request->param('describe','','string');
        $score = $this->request->param('score','','string');
        if(!is_numeric($score))
            return $this->error('分数为数值');
        $thumb = $this->uploads();
        $add = ['name'=>$name,'score'=>$score,'last_time'=>$lastTime,'create_time'=>time(),'describe'=>$describe,'course_id'=>$courseId,'teacher_id'=>$user['Id']];
        if($thumb !==false)
            $add['thumb']=$thumb;
        $res = Db::name('task_job')->insert($add);
        if(!$res)
            return error('添加失败,请重试');
        return $this->success('添加成功');
    }

    public function uploads(){
        $upload = new Upload();
        $upload->setSupportResource(array());
        $upload->setSupportSuffix($this->_fileType);
        $upload->setFormName('taskJobFile');
        $result = $upload->exec();
        if(!$result){
            return false;
        }
        $filename = $upload->getFilename();
        $fileArr = explode('.',$filename);
        $fileType = end($fileArr);
        $path = $upload->path('/uploads/teacher/taskjob/');
        $upload->buildCode();
        $code = $upload->getRetval();
        $fileName = $path.$code['code'].'.'.$fileType;
        $result = $upload->moveFile($fileName);
        if(!$result){
//            $error = array_values($upload->getErrval());
//            $str = is_array($error)?implode(',',$error):$error;
            return false;
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
        return $file;
    }
//    public function addtaskjob(){
//        $user = $this->getUser();
//        $request = $this->request;
//        $courseId= $request->param('courseId','','int');
//        $course = Db::name('courses')->where(['Id'=>$courseId,'teacher_id'=>$user['Id']])->find();
//        if(empty($course))
//            return $this->returnJson('课程不存在');
//        $add['name'] = $request->param('name','','string');
//        if(empty($add['name']))
//            return $this->returnJson('作业名不能为空');
//        $add['describe'] = $request->param('describe','','string');
//        $add['thumb'] = $request->param('name','','string');
//        if(!empty($add['thumb']) and !file_exists(PUBLIC_PATH.$add['thumb']))
//            return $this->returnJson('作业文件不存在');
//        $add['last_time'] = strtotime($request->param('time','','string'));
//        if($add['last_time']<=time())
//            return $this->returnJson('作业截至时间必须大于现在时间');
//        $add['create_time'] = time();
//        $add['course_id'] = $courseId;
//        $add['teacher_id'] = $user['Id'];
//        $res = Db::name('task_job')->insert($add);
//        if($res)
//            return $this->returnJson('布置作业成功',true,1);
//        return $this->returnJson('布置作业失败,请重试');
//    }

    public function altertaskjob(){
        $user = $this->getUser();
        $taskId = $this->request->param('id','','int');
        $taskJob = Db::name('task_job')->where('Id',$taskId)->find();
        if(empty($taskJob))
            return  $this->returnJson('作业不存在');
        if($taskJob['teacher_id'] !=$user['Id'])
            return $this->returnJson('你没有权限修改');
        $update = ['Id'=>$taskId];
        $update['last_time'] = strtotime($this->request->param('lastTime','','string'));
        if($lastTime<=time())
            return $this->returnJson('结束时间必须大于现在的时间');
        $update['describe'] = $this->request->param('describe','','string');
        $thumb = $this->request->param('thumb','','string');
        if(!empty($thumb)){
            if(!file_exists(PUBLIC_PATH.$thumb))
                return $this->returnJson('文件不存在');
            $update['thumb'] = $thumb;
        }
        $res = Db::name('task_job')->update($update);
        if($res){
            if(isset($update['thumb']))
                @unlink($taskJob['thumb']);
            return $this->returnJson('修改成功',true,1);
        }

        return $this->returnJson('修改失败，请重试');
    }

    public function check(){
        $user = $this->getUser();
        $Id = $this->request->param('id','','int');
        $task = Db::name('task')->where(['Id'=>$Id])->find();
        if(empty($task)){
            $this->assign('error','没有该作业');
            return $this->fetch(APP_PATH.'index/view/index/error.html');
        }

        $course = Db::name('courses')->where(['Id'=>$task['course_id'],'teacher_id'=>$user['Id']])->find();
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

    public function score(){
        $user = $this->getUser();
        $taskId = $this->request->param('taskId','','int');
        $task = Db::name('task')->where('Id',$taskId)->find();
        if(empty($task))
            return $this->returnJson('作业不存在');
        $taskJob = Db::name('task_job')->where('Id',$task['task_id'])->find();
        if($taskJob['teacher_id']!= $user['Id'])
            return $this->returnJson('您没有权限批改此作业');
        $score = $this->request->param('score','','string');
//        $scoreArr = ['A','B','C','D','E','F'];
        if(!is_numeric($score) or $score>$taskJob['score'])
            return $this->returnJson('分数为数字,且满分为'.$taskJob['score']);
        $update['remark'] = $this->request->param('remark','','string');
        $update ['Id'] =$taskId;
        $update ['teacher_id']= $user['Id'];
        $update ['score']=$score;
        $update ['correct_time']=time();
        $res = Db::name('task')->update($update);
        if($res)
            return $this->returnJson('批改成功',true,1);
        return $this->returnJson('批改失败，请重试');
    }

    //删除作业
    public function delTaskjob(){
        $id = $this->request->param('id',0,'int');
        $job = Db::name('task_job')->where('Id',$id)->find();
        if(empty($job))
            return $this->returnJson('作业不存在');
        Db::startTrans();
        $res = Db::name('task_job')->delete($id);
        if(!$res)
            return $this->returnJson('删除失败，请重试');
        $taskRes = Db::name('task')->where('task_id',$id)->delete();
        if(!$res){
            Db::rollback();
            return $this->returnJson('删除失败，请重试');
        }
        Db::commit();
        return $this->returnJson('删除成功',true,1);
    }

}