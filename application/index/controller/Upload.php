<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/2/26
 * Time: 22:58
 */
namespace app\index\controller;
use think\Controller;
use ku\Upload as kuUpload;
use ku\Tool;
class Upload extends Controller{

    public function index(){

    }

    //上传身份证
    public function cardid(){
        $upload = new kuUpload();
        $upload->setFormName('idcardPhoto');
        $result = $upload->exec();
        if(!$result){
            return json(array('status'=>false,'msg'=>'文件未上传'));
        }
        $path = $upload->path('/uploads/teacher/idcard/');
        $upload->buildCode();
        $code = $upload->getRetval();
        $fileName = $path.$code['code'].'.'.$upload->getFileSuffix();
        $result = $upload->moveFile($fileName);
        if(!$result){
            return json(array('status'=>false,'msg'=>'文件上传失败'));
        }
        $res = Tool::uploadImage($fileName,$fileName);
        if(!$res){
            return json(array('status'=>false,'msg'=>'图片重生成错误'));
        }
        $fileName = str_replace(PUBLIC_PATH,'',$fileName);
        return json(array('status'=>true,'fileName'=>$fileName));
    }
    //上传教师证
    public function teacher(){
        $upload = new kuUpload();
        $upload->setFormName('teacherPhoto');
        $result = $upload->exec();
        if(!$result){
            return false;
        }
        $path = $upload->path('/uploads/teacher/idcard/');
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