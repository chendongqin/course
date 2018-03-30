<?php
namespace app\index\controller;
use think\Db;
use ku\Tool;
use ku\Verify;
use think\Session;
use think\Cache;
use base\Base;
class Index extends Base
{
    //首页
    public function index()
    {
        $student = Session::get('student_user')[0];
        if(!empty($student))
            $this->redirect('/student');
        $teacher = Session::get('teacher_user')[0];
        if(!empty($teacher))
            $this->redirect('/teacher');
        return $this->fetch('index');
    }

    //登陆
    public function login(){
        $request = $this->request;
        $userName = $request->param('userName','','string');
        $password = $request->param('password','','string');
        $code = $request->param('code','','string');
        $sission = new Session();
        $virefyCode = $sission->get('login_virefy_code');
        if(strcasecmp($virefyCode,$code)!==0){
            return $this->returnJson('验证码错误');
        }
        $type = $request->param('type',0,'int');
        if($type === 0){
            $userVirefy = $this->studentLogin($userName,$password);
        }else{
            $userVirefy = $this->teacherLogin($userName,$password);
        }
        if($userVirefy['status'] === false){
            return $this->returnJson($userVirefy['msg']);
        }
        if($type === 0){
            return $this->returnJson('登陆成功',true,1);
        }else{
            return $this->returnJson('登陆成功',true,2);
        }
    }

    //学生登陆
    public function studentLogin($userName,$password){
        $where = array('stu_no'=>$userName);
        $orWhere = array('email'=>$userName);
        $user = Db::name('students')->where($where)->whereOr($orWhere)->find();
        if(empty($user)){
            return array('status'=>false,'msg'=>'用户不存在');
        }
        $password = sha1($password.substr($user['idcard'],-4));
        if(strcasecmp($user['password'],$password)!==0){
            return array('status'=>false,'msg'=>'密码错误');
        }
        if($user['ban']==1)
            return  array('status'=>false,'msg'=>'用户被禁用');
        $session = new Session();
        $session->push('student_user',$user);
        $login = ['Id'=>$user['Id'],'login_time'=>time(),'online'=>time()];
        Db::name('students')->update($login);
        return array('status'=>true,'msg'=>'登陆成功');
    }
    //教师登陆
    public function teacherLogin($userName,$password){
        $where = array('job_no'=>$userName);
        $orWhere = array('email'=>$userName);
        $user = Db::name('teachers')->where($where)->whereOr($orWhere)->find();
        if(empty($user)){
            return array('status'=>false,'msg'=>'用户不存在');
        }
        $password = sha1($password.substr($user['idcard'],-4));
        if(strcasecmp($user['password'],$password)!==0){
            return array('status'=>false,'msg'=>'密码错误');
        }
        if($user['ban']==1)
            return  array('status'=>false,'msg'=>'用户被禁用');
        $session = new Session();
        $session->push('teacher_user',$user);
        $login = ['Id'=>$user['Id'],'login_time'=>time(),'online'=>time()];
        Db::name('teachers')->update($login);
        return array('status'=>true,'msg'=>'登陆成功');
    }

    //验证码
    public function virefy(){
        $request = $this->request;
        $channel = $request->param('channel','','string');
        Tool::virefyCode(4,100,40,$channel);
    }
    //学生注册
    public function regist(){
        return $this->fetch('regist');
    }
    //教师注册
    public function apply(){
        return $this->fetch('apply');
    }

    //教师注册
    public function doapply(){
        $request = $this->request;
        $data['school'] = $request->param('school','','string');
        $data['job_no'] = $request->param('job_no','','string');
        $data['name'] = $request->param('name','','string');
        $data['idcard'] = $request->param('card_id','','string');
        $data['sex'] = $request->param('sex','','string');
        $data['college'] = $request->param('college','','string');
        $data['job_type'] = $request->param('job_type','','string');
        $data['address'] = $request->param('address','','string');
        $data['virefy_card_id'] = $request->param('virefy_card_id','','string');
        $data['virefy_photo'] = $request->param('virefy_photo','','string');
        $data['email'] = $request->param('email','','string');
        $code = $request->param('code','','string');
        $emailCode = $request->param('emailCode','','string');
        if(empty($code)){
            return $this->returnJson('验证码不能为空');
        }
        $session = new Session();
        $virefyCode = $session->get('apply_virefy_code');
        if(!$virefyCode){
            return $this->returnJson('验证码系统错误');
        }
        if(strcasecmp($virefyCode,$code)!==0){
            return $this->returnJson('验证码不正确');
        }
        if(empty($emailCode)){
            return $this->returnJson('邮箱验证码不为空');
        }
        $virefyEmailCode = Cache::get($data['email']);
        if($virefyEmailCode===false){
            return $this->returnJson('请发送邮箱验证码');
        }
        if(strcasecmp($emailCode,$virefyEmailCode)!==0){
            return $this->returnJson('邮箱验证码不匹配');
        }
        $teacherVirefy = Db::name('virefy_teacher');
        $res = $teacherVirefy->insert($data);
        if(!$res){
            unlink(PUBLIC_PATH.$data['virefy_card_id']);//删除不成功文件
            unlink(PUBLIC_PATH.$data['virefy_photo']);//删除不成功文件
            return $this->returnJson('注册失败');
        }
        return $this->returnJson('申请成功',true,1);
    }

    public function forget(){
        return $this->fetch('forget');
    }

    public function findback(){
        $email = $this->request->param('email','','string');
        if(!Verify::isEmail($email)){
            return $this->error('邮箱格式不正确');
        }
        $user = Db::name('students')->where(array('email'=>$email))->find();
        if(empty($user)){
            $user = Db::name('teachers')->where(array('email'=>$email))->find();
            if(empty($user)){
                return $this->error('该邮箱未注册');
            }
            $this->assign('user_no',$user['job_no']);
            $this->assign('userType',1);
        }else{
            $this->assign('user_no',$user['stu_no']);
            $this->assign('userType',0);
        }
        $this->assign('email',$email);
        return $this->fetch('findback');
    }

    public function dofindback(){
        $request = $this->request;
        $email = $request->param('email','','string');
        $password = $request->param('password','','string');
        $secondPassword = $request->param('secondPassword','','string');
        $userName = $request->param('userName','','string');
        $userType = (int)$request->param('userType','','int');
        $code = $request->param('emailCode','','string');
        if(empty($code)){
            return $this->returnJson('邮箱验证码不能为空');
        }
        $virefy = Cache::get('findback_'.trim($email));
        if(strcasecmp($virefy,$code)!==0){
            return $this->returnJson('邮箱验证不正确');
        }
        $where = array('email'=>$email);
        if(strcmp($password,$secondPassword)!==0){
            return $this->error('两次密码输入不正确');
        }
        if($userType===0){
            $where['stu_no'] = $userName;
            $res = $this->updatePasswordOfStudent($where,$password);
        }else{
            $where['job_no'] = $userName;
            $res = $this->updatePasswordOfTeacher($where,$password);
        }
        if($res['status'] === false){
            return $this->returnJson($res['msg']);
        }
        return $this->returnJson($res['msg'],true,1);

    }

    public function updatePasswordOfStudent($where,$password){
        $userModel =  Db::name('students');
        $user =$userModel->where($where)->find();
        if(empty($user)){
            return array('status'=>false,'msg'=>'用户信息不存在');
        }
        $password =sha1($password.substr($user['idcard'],-4));
        $upData = array('Id'=>$user['Id'],'password'=>$password);
        $res = $userModel->update($upData);
        if(!$res){
            return array('status'=>false,'msg'=>'修改失败');
        }
        return array('status'=>true,'msg'=>'修改成功');
    }

    public function updatePasswordOfTeacher($where,$password){
        $userModel =  Db::name('teachers');
        $user =$userModel->where($where)->find();
        if(empty($user)){
            return array('status'=>false,'msg'=>'用户信息不存在');
        }
        $password = sha1($password.substr($user['job_no'],-4));
        $upData = array('Id'=>$user['Id'],'password'=>$password);
        $res = $userModel->update($upData);
        if(!$res){
            return array('status'=>false,'msg'=>'修改失败');
        }
        return array('status'=>true,'msg'=>'修改成功');
    }

    //登出
    public function logout(){
        $sission = new Session();
        $request = $this->request;
        $channel = $request->param('channel','','string');
        switch ($channel){
            //学生登出
            case 'student':
                $user = Session::get('student_user');
                $user = isset($user[0])?$user[0]:['Id'=>0];
                $sission->delete('student_user');
                Db::name('students')->update(['Id'=>$user['Id'],'online'=>time()-2*3600]);
                $this->redirect('/');
                break;
            //教师登出
            case 'teacher':
                $user = Session::get('teacher_user');
                $user = isset($user[0])?$user[0]:['Id'=>0];
                $sission->delete('teacher_user');
                Db::name('teachers')->update(['Id'=>$user['Id'],'online'=>time()-2*3600]);
                $this->redirect('/');
                break;
            default:
                break;
        }
    }

}
