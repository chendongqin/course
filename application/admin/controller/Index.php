<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/2/11
 * Time: 22:40
 */
namespace app\admin\controller;

use think\Db;
use base\Adminbase;
use think\Loader;


class Index extends Adminbase{

    private $_excles = array('xls','xlsx','xlsb',);
    private $_studentClu = array('stu_no'=>'学号','school'=>'学校','name'=>'姓名','major'=>'专业','sex'=>'性别','email'=>'邮箱','class'=>'班级','idcard'=>'身份证');

    public function index(){
        return $this->fetch('index');
    }


    public function enter(){
        return $this->fetch('index/enter');
    }

    public function doenter(){
        $request = $this->request;
        $fileInfo = $request ->file('studentExcel')->getInfo();
        if (empty($fileInfo))
            return $this->returnJson('没有文件上传！');
        $name=explode('.',$_FILES['studentExcel']['name']);
        $lastName=$name[count($name)-1];
        if(!in_array(strtolower($lastName),$this->_excles))
            return $this->returnJSon('上传文件格式必须为'.implode(',',$this->_excles));
        if ($_FILES['studentExcel']['error'] > 0)
            return $this->returnJson('上传错误！');
        $basePath = PUBLIC_PATH.'/phpexcel/';
        Loader::import('PHPExcel',$basePath);
        Loader::import('PHPExcel/IOFactory.PHPExcel_IOFactory',$basePath);
        $read = \PHPExcel_IOFactory::createReader('Excel2007');
        $obj = $read->load($fileInfo['tmp_name']);
        $dataArray =$obj->getActiveSheet()->toArray();
        foreach ($dataArray as $key=> $item){
            $dataArray[$key] = array_filter($item);
        }
        $datas = array_filter($dataArray);
        $virefy = [];
        foreach ($this->_studentClu as $key=>$clu){
            $cluKey =  array_search($clu,$datas[0]);
            if($cluKey === false){
                return $this->returnJson('导入表格不包含'.$clu.'的数据');
            }
            $virefy[$key] = $cluKey;
        }
        unset($datas[0]);
        $res = true;
        $studentModel = Db::name('students');
        foreach ($datas as $data){
            $add = ['create_time'=>time()];
            foreach ($virefy as $key =>$keyName){
                $value = isset($data[$keyName])?$data[$keyName]:'';
                $add[$key] = $value;
            }
            $virefyStudentt = $studentModel
                ->where(array('stu_no'=>$add['stu_no'],'school'=>$add['school']))
                ->find();
            if(!empty($virefyStudentt))
                continue;
            $password = substr($add['idcard'],0,15);
            $add['password'] = sha1($password.substr($add['idcard'],-4));
            $add['birthday'] = substr($add['idcard'],6,4).'-'.substr($add['idcard'],10,2).'-'.substr($add['idcard'],12,2);
            $res = $res && $studentModel->insert($add);
        }
        if($res)
            return $this->returnJson('导入学生数据成功',true,1);
        return $this->returnJson('导入数据存在错误，请重试');
    }

    //获取在线人数
    public function online(){
        $stuOnline = Db::name('students')->where('online','>=',time()-3600*2)->count();
        $teaOnline = Db::name('teachers')->where('online','>=',time()-3600*2)->count();
        $online = $stuOnline + $teaOnline;
        return $this->returnJson('获取成功',true,1,['online'=>(int)$online]);
    }

}