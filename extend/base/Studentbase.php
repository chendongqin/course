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
class Studentbase extends Controller{

    protected function _initialize() {
        $session = new Session();
        $student = $session->get('student_user');
        if(empty($student)){
//            $this->redirect('/');
            $this->error('请先登陆','/');
        }
    }


}