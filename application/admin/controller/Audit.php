<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/23
 * Time: 23:45
 */
namespace app\admin\controller;
use base\Adminbase;
use think\Db;
use ku\Email;

class Audit extends Adminbase{

    public function teacher(){
        $id = (int)$this->request->param('id','','int');
        $auditStatus = (int)$this->request->param('audit','','int');
        if($auditStatus !==1 and $auditStatus!==2)
            return $this->returnJson('审核传入参数错误');
        $virefyModel = Db::name('virefy_teacher');
        $teacherModel = Db::name('teachers');
        $teacherVirefy = $virefyModel->where(array('Id'=>$id,'audit_status'=>0))->find();
        if(empty($teacherVirefy))
            return $this->returnJson('审核数据不存在');
        $update = ['Id'=>$id,'audit_status'=>$auditStatus];
        Db::startTrans();
        $res = $virefyModel->update($update);
        if(!$res)
            return $this->returnJson('更新审核状态失败，请重试');
        if($auditStatus !==1){
            $body = $teacherVirefy['name'].'您好，您的教师认证未通过审核，请确认信息的真实性、完整性。点击进入 www.course.com';
            $emailRes = Email::sendEmail($teacherVirefy['email'],'教师审核',$body);
            if(!$emailRes)
                return $this->returnJson('审核邮箱发送失败');
            return $this->returnJson('审核成功',true,1);
        }
        unset($teacherVirefy['Id']);
        unset($teacherVirefy['audit_status']);
        unset($teacherVirefy['virefy_photo']);
        unset($teacherVirefy['virefy_card_id']);
        $teacher = $teacherVirefy;
        $teacher['create_time'] = time();
        $teacher['update_time'] = time();
        $password = substr($teacher['idcard'],0,15);
        $teacher['password'] = sha1($password.substr($teacher['idcard'],-4));
        $res = $teacherModel->insert($teacher);
        if($res){
            Db::commit();
            $body = $teacher['name'].'您好，您的教师认证已通过审核。点击进入 www.course.com';
            $emailRes = Email::sendEmail($teacher['email'],'教师审核',$body);
            if(!$emailRes)
                return $this->returnJson('审核成功，但邮箱发送失败');
            return $this->returnJson('审核成功',true,2);
        }
        Db::rollback();
        $this->returnJson('审核失败');
    }

}