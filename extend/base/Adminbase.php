<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/2/14
 * Time: 13:15
 */
namespace base;
use think\Controller;
use think\Session;
class Adminbase extends Controller{
    protected $_ec = array(
        'login',
    );
    protected $_ac = array(
        'login'=>'*',
    );
    protected function _initialize() {
        if($this->isFilter()===false){
            $session = new Session();
            $admin = $session->get('admin_user');
            if(empty($admin)){
                $this->error('请先登陆','/admin/login');
            }
        }
    }

    protected function isFilter(){
        $request = $this->request;
        $controller = strtolower($request->controller());
        $action = strtolower($request->action());
        if(!in_array($controller,$this->_ec)){
            return false;
        }
        if($this->_ac[$controller]== '*'){
            return true;
        }elseif($this->_ac[$controller]==$action){
            return true;
        }else{
            return false;
        }
    }


}