<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/27
 * Time: 20:33
 */
namespace app\student\controller;

use base\Studentbase;
use ku\Upload as kuUpload;
use ku\Pdf;

class Upload extends Studentbase{

    private $_fileType = array('docx','doc','pdf');

    public function index(){

    }

    public function task(){
        $upload = new kuUpload();
        $upload->setSupportResource(array());
        $upload->setSupportSuffix($this->_fileType);
        $upload->setFormName('task');
        $result = $upload->exec();
        if(!$result){
            $error = array_values($upload->getErrval());
            $str = is_array($error)?implode(',',$error):$error;
            return $this->returnJson($str);
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



}