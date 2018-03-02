<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/2/11
 * Time: 22:40
 */
namespace app\admin\controller;
use think\Db;
use ku\Tool;
use base\Adminbase;
//use PHPExcel\IOFactory;
//use PHPExcel\Reader_Excel2007;
//use PHPExcel;

class Index extends Adminbase{

    public function index(){
        return $this->fetch('index');
    }


    public function enter(){
        return $this->fetch('index/enter');
    }

    public function doenter(){
        if (!empty($_FILES['studentExcel'])) {
            $name=explode('.',$_FILES['studentExcel']['name']);
            $lastName=$name[count($name)-1];
//            if(strtolower($lastName) != 'csv' and strtolower($lastName) != 'xls' and strtolower($lastName) !='xlsx' and strtolower($lastName) !='xlsb'){
//                return $this->returnData('上传文件格式必须为csv/xls/xlsx/xlsb等文件！', 28101);
//            }
            if(strtolower($lastName) != 'csv'){
                return $this->returnData('上传文件格式必须为csv文件！', 28101);
            }
            if ($_FILES['studentExcel']['error'] > 0) {
                return $this->error('上传错误！','/admin/index/enter');
            } else {
                $d = date("YymdHis");
                $randNum = rand((int)50000000, (int)10000000000);
                $filesname = $d . $randNum . $_FILES['studentExcel']['name'];
                $dir = PUBLIC_PATH . '/uploads/tmp/';
                if(!file_exists($dir)){
                    Tool::makeDir($dir);
                }
                if (!copy($_FILES['studentExcel']['tmp_name'], $dir. $filesname)) {
                    return $this->error('文件上传失败！','/admin/index/enter');
                } else {
                    $res = $this->intoSql($dir. $filesname);
                    if(!$res){
                        unlink($dir. $filesname);
                        return $this->error('操作失败！','/admin/index/enter');
                    }
                    unlink($dir. $filesname);
                    return $this->success('操作成功！','/admin/index/index');
                }
            }
        }
        else {
            return $this->error('没有文件上传！','/admin/index/enter');
        }
    }

    public function intoSql($filesname)
    {
        if ($filesname) {
            $name = explode('.', $filesname);
            $lastName = $name[count($name) - 1];
            if ($lastName === 'csv') {
                $fp = fopen($filesname, "r");
                $students = array();
                while ($line = fgetcsv($fp, 0,',')) {
                    $students['stu_no'][] = iconv('gb2312', 'utf-8', $line[0]);
                    $students['school'][] = iconv('gb2312', 'utf-8', $line[1]);
                    $students['name'][] = iconv('gb2312', 'utf-8', $line[2]);
                    $students['major'][] = iconv('gb2312', 'utf-8', $line[3]);
                    $students['sex'][] = iconv('gb2312', 'utf-8', $line[4]);
                    $students['ID_no'][] = iconv('gb2312', 'utf-8', $line[5]);
                }
                //处理标题
                foreach ($students as $key => $student){
                    unset($students[$key][0]);
                }
                Db::startTrans();
                $virefyStudents = Db::name('virefy_students');
                foreach ($students as $student){
                    foreach ($student as $key =>$stu_no){
                        $data['stu_no'] = $stu_no;
                        $data['school'] = $students['school'][$key];
                        $virefy =$virefyStudents->where($data)->find();
                        if(!empty($virefy)){
                            continue;
                        }
                        $data['name'] = $students['name'][$key];
                        $data['major'] = $students['major'][$key];
                        $data['sex'] = $students['sex'][$key]=='男'?0:1;
                        $data['ID_no'] = $students['ID_no'][$key];
                        $res = $virefyStudents->insert($data);
                        if(!$res){
                            Db::rollback();
                            return false;
                        }
                    }
                    Db::commit();
                    break;
                }
                return true;
            }
            return false;
        }
        return false;
    }

}