<?php
namespace app\index\controller;
use think\Controller;
use think\Db;
use ku\Tool;
use ku\Verify;
use think\Session;
use think\Cache;
class Index extends Controller
{
    //首页
    public function index()
    {
        return $this->fetch('index');
    }

    //登陆
    public function login(){
        $request = $this->request;
        $userName = $request->param('userName','','string');
        $password = $request->param('password','','string');
        $code = strtolower($request->param('code','','string'));
        $sission = new Session();
        $virefyCode = strtolower($sission->get('login_virefy_code'));
        if(strcmp($virefyCode,$code)!==0){
            return $this->error('验证码错误');
        }
        $type = $request->param('type',0,'int');
        if($type === 0){
            $userVirefy = $this->studentLogin($userName,$password);
        }else{
            $userVirefy = $this->teacherLogin($userName,$password);
        }
        if($userVirefy['status'] === false){
            return $this->error($userVirefy['msg']);
        }
        if($type === 0){
            return $this->success('登陆成功','/student');
        }else{
            return $this->success('登陆成功','/teacher');
        }
    }

    //学生登陆
    public function studentLogin($userName,$password){
        $where = array('stu_no'=>$userName);
        $user = Db::name('students')->where($where)->find();
        $password = sha1($password.substr($user['stu_no'],-4));
        if(empty($user)){
            return array('status'=>false,'msg'=>'用户不存在');
        }
        if(strcmp($user['password'],$password)!==0){
            return array('status'=>false,'msg'=>'密码错误');
        }
        $session = new Session();
        $session->push('student_user',$user);
        return array('status'=>true,'msg'=>'登陆成功');
    }
    //教师登陆
    public function teacherLogin($userName,$password){

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
    //学生注册执行
    public function doregist(){
        $request = $this->request;
        $code = strtolower($request->param('code','','string'));
        if(empty($code)){
            return $this->error('验证码不能为空');
        }
        $session = new Session();
        $virefyCode = strtolower($session->get('regist_virefy_code'));
        if(!$virefyCode){
            return $this->error('验证码系统错误');
        }
        if($virefyCode!=$code){
            return $this->error('验证码不正确');
        }
        $email = $request->param('email','','string');
        $emailCode = $request->param('emailCode','','string');
        if(empty($emailCode)){
            return $this->error('邮箱验证码不为空');
        }
        $virefyEmailCode = Cache::get($email);
        if($virefyEmailCode===false){
            return $this->error('请发送邮箱验证码');
        }
        if(strcasecmp($emailCode,$virefyEmailCode)!==0){
            return $this->error('邮箱验证码不匹配');
        }
        $virefyExist1 = Db::name('students')->where(array('email'=>$email))->find();
        $virefyExist2 = Db::name('teachers')->where(array('email'=>$email))->find();
        if(!empty($virefyExist1) or !empty($virefyExist2)){
            return $this->error('该邮箱已注册');
        }
        $school = $request->param('school','','string');
        $stuNo = $request->param('stu_no','','string');
        $virefy = array('school'=>$school,'stu_no'=>$stuNo);
        $virefyRes = Db::name('virefy_students')->where($virefy)->find();
        if(empty($virefyRes)){
            return $this->error('该校此学号未审核通过，重新输入或联系管理员');
        }
        $virefyExist = Db::name('students')->where(array('stu_no'=>$stuNo,'school'=>$school))->find();
        if(!empty($virefyExist)){
            return $this->error('该学生已注册');
        }
        $password = $request->param('password','','string');
        if(strlen($password)<6){
            return $this->error('密码长度需要大于6位');
        }
        $secondPwd = $request->param('secondPwd','','string');
        if($password!=$secondPwd){
            return $this->error('两次输入密码不正确');
        }
        $college = $request->param('college','','string');
        $class = $request->param('class','','string');
        $address = $request->param('address','','string');
        $data = $virefy;
        $data['stu_no'] = $stuNo;
        $data['school'] = $school;
        $data['name'] = $virefyRes['name'];
        $data['password'] = sha1($password.substr($stuNo,-4));
        $data['sex'] = $virefyRes['sex'];
        $data['birthday'] = substr($virefyRes['ID_no'],6,4).'-'.substr($virefyRes['ID_no'],10,2).'-'.substr($virefyRes['ID_no'],12,2);
        $data['major'] = $virefyRes['major'];
        $data['college'] = $college;
        $data['class'] = $class;
        $data['address'] = $address;
        $data['create_time'] = time();
        $data['update_time'] = time();
        $data['email'] = $email;
        $res = Db::name('students')->insert($data);
        if(!$res){
            return $this->error('注册失败，请重试！');
        }
        Cache::rm($email);
        $session = new Session();
        $user = Db::name('students')->where(array('stu_no'=>$stuNo))->find();
        $session->push('student_user',$user);
        return $this->success('注册成功','/student/');
    }

    //教师注册
    public function doapply(){
        $request = $this->request;
        $data['school'] = $request->param('school','','string');
        $data['job_no'] = $request->param('job_no','','string');
        $data['name'] = $request->param('name','','string');
        $data['card_id'] = $request->param('card_id','','string');
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
            return $this->error('验证码不能为空');
        }
        $session = new Session();
        $virefyCode = $session->get('apply_virefy_code');
        if(!$virefyCode){
            return $this->error('验证码系统错误');
        }
        if(strcasecmp($virefyCode,$code)!==0){
            return $this->error('验证码不正确');
        }
        if(empty($emailCode)){
            return $this->error('邮箱验证码不为空');
        }
        $virefyEmailCode = Cache::get($data['email']);
        if($virefyEmailCode===false){
            return $this->error('请发送邮箱验证码');
        }
        if(strcasecmp($emailCode,$virefyEmailCode)!==0){
            return $this->error('邮箱验证码不匹配');
        }
        $teacherVirefy = Db::name('virefy_teacher');
        $res = $teacherVirefy->insert($data);
        if(!$res){
            unlink(PUBLIC_PATH.$data['virefy_card_id']);//删除不成功文件
            unlink(PUBLIC_PATH.$data['virefy_photo']);//删除不成功文件
            return $this->error('注册失败');
        }
        return $this->success('申请成功','/');
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
            return $this->error('邮箱验证码不能为空');
        }
        $virefy = Cache::get('findback_'.trim($email));
        if(strcasecmp($virefy,$code)!==0){
            return $this->error('邮箱验证不正确');
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
            return $this->error($res['msg']);
        }
        return $this->success($res['msg'],'/');

    }

    public function updatePasswordOfStudent($where,$password){
        $userModel =  Db::name('students');
        $user =$userModel->where($where)->find();
        if(empty($user)){
            return array('status'=>false,'msg'=>'用户信息不存在');
        }
        $password =sha1($password.substr($user['stu_no'],-4));
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
        $password = sha1($password.substr($user['stu_no'],-4));
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
                $sission->delete('student_user');
                $this->success('登出成功','/');
                break;
            //教师登出
            case 'teacher':
                $sission->delete('teacher_user');
                $this->success('登出成功','/');
                break;
            default:
                break;
        }
    }


}
