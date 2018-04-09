<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/27
 * Time: 20:33
 */
namespace app\teacher\controller;

use base\Teacherbase;
use ku\Upload as kuUpload;
use ku\Pdf;
use ku\Tool;

class Upload extends Teacherbase{

    private $_fileType = array('docx','doc','pdf','ppt','pptx',);

    public function index(){
        $formName = $this->request->param('type','','string');
        if(empty($fileName))
            return $this->returnJson('没有定义上传文件名');
        $upload = new kuUpload();
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
            return $this->returnJson($str);
        }
        $file = str_replace(PUBLIC_PATH,'',$fileName);
        return $this->returnJson('上传成功',true,1,['fileName'=>$file]);
    }



    public function taskjob(){
        $upload = new kuUpload();
        $upload->setSupportResource(array());
        $upload->setSupportSuffix($this->_fileType);
        $upload->setFormName('taskJob');
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
            $error = array_values($upload->getErrval());
            $str = is_array($error)?implode(',',$error):$error;
            return $this->returnJson($str);
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
        return $this->returnJson('上传成功',true,1,['fileName'=>$file]);
    }

    public function courseImage(){
        $upload = new kuUpload();
        $upload->setFormName('courseImage');
        $result = $upload->exec();
        if(!$result){
            return false;
        }
        $path = $upload->path('/uploads/teacher/courseimg/');
        $upload->buildCode();
        $code = $upload->getRetval();
        $fileName = $path.$code['code'].'.'.$upload->getFileSuffix();
        $result = $upload->moveFile($fileName);
        if(!$result){
            return false;
        }
        $res = Tool::uploadImage($fileName,$fileName);
        if(!$res){
            return json(array('status'=>false,'fileName'=>''));
        }
        $fileName = str_replace(PUBLIC_PATH,'',$fileName);
        return json(array('status'=>true,'fileName'=>$fileName));
    }


}