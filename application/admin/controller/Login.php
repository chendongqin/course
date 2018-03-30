<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/2/25
 * Time: 21:30
 */
namespace app\admin\controller;
//use think\Controller;
use think\Db;
use think\Session;
use base\Adminbase;
class Login extends Adminbase{
    //登陆主页
    public function index(){
        return $this->fetch('index');
    }
    //登陆
    public function login(){
        $request = $this->request;
        $userName = $request->param('userName','','string');
        $password = $request->param('password','','string');
        $code = strtolower($request->param('code','','string'));
        $sission = new Session();
        $virefyCode = strtolower($sission->get('adminLogin_virefy_code'));
        if(strcmp($virefyCode,$code)!==0){
            return $this->error('验证码错误');
        }
        $admin = Db::name('admin')->where(array('name'=>$userName))->find();
        if (empty($admin)){
            $admin = Db::name('admin')->where(array('mobile'=>$userName))->find();
        }
        if(empty($admin)){
            return $this->error('账号不存在,请重新确认');
        }
        $password = sha1($password.$admin['name']);
        if(strcmp($password,$admin['password'])!==0){
            return $this->error('密码不正确');
        }
        $sission->push('admin_user',$admin);
        $this->redirect('/admin');
    }
    //退出登陆
    public function logout(){
        $sission = new Session();
        $sission->delete('admin_user');
        return $this->redirect('/admin/login');
    }
}