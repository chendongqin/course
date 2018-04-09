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
use ku\Upload;

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
//    //添加
//    public function add(){
//        $user = $this->getUser();
//        $name = $this->request->param('name','','string');
//        $thumb = $this->request->param('thumb','','string');
//        $courseId = $this->request->param('courseId','','string');
//        if(empty($thumb))
//            return $this->returnJson('课件路径不为空');
//        if(!is_file(PUBLIC_PATH.$thumb))
//            return $this->returnJson('文件不存在');
//        $course = Db::name('courses')->where('Id',$courseId) ->find();
//        if(empty($course)){
//            $this->returnJson('课程不存在');
//
//        }
//        if($course['teacher_id']!=$user['Id']){
//            $this->returnJson('您没有权限添加该课程课件');
//        }
//        $add = ['teacher_id'=>$user['Id'],'name'=>$name,'thumb'=>$thumb,'create_time'=>time(),'course_id'=>$courseId];
//        $res = Db::name('courseware')->insert($add);
//        if($res)
//            return $this->returnJson('上传课件成功',true,1);
//        return $this->returnJson('上传失败，请重试');
//    }

    //添加
    public function add(){
        $user = $this->getUser();
        $name = $this->request->param('name','','string');
        $courseId = $this->request->param('courseId','','string');
        $thumb = $this->upData('coursewareFile');
        if($thumb ===false)
            return $this->error('课件上传失败');
        if(!is_file(PUBLIC_PATH.$thumb))
            return $this->error('文件不存在');
        $course = Db::name('courses')->where('Id',$courseId) ->find();
        if(empty($course)){
            $this->error('课程不存在');
        }
        if($course['teacher_id']!=$user['Id']){
            $this->error('您没有权限添加该课程课件');
        }
        $add = ['teacher_id'=>$user['Id'],'name'=>$name,'thumb'=>$thumb,'create_time'=>time(),'course_id'=>$courseId];
        $res = Db::name('courseware')->insert($add);
        if($res)
            return $this->success('上传课件成功');
        return $this->error('上传失败，请重试');
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
    //下载公共资源
    public function downpOrigin(){
        $Id = $this->request->param('id','','int');
        $origin = Db::name('public_origin')->where('Id',$Id)->find();
        if(empty($origin)){
            $this->assign('error','课件不存在');
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


    public function upData($formName){
        if(empty($formName))
            return false;
        $upload = new Upload();
        $upload->setSupportResource(array());
        $upload->setSupportSuffix(array());
        $upload->setFormName($formName);
        $result = $upload->exec();
        if(!$result){
            return false;
        }
        $filename = $upload->getFilename();
        $fileArr = explode('.',$filename);
        $fileType = end($fileArr);
        $path = $upload->path('/uploads/teacher/'.strtolower($formName).'/');
        $upload->buildCode();
        $code = $upload->getRetval();
        $fileName = $path.$code['code'].'.'.$fileType;
        $result = $upload->moveFile($fileName);
        if(!$result){
            $error = array_values($upload->getErrval());
            $str = is_array($error)?implode(',',$error):$error;
            return false;
        }
        $file = str_replace(PUBLIC_PATH,'',$fileName);
        return $file;
    }

}