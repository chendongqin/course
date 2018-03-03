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
class Teacherbase extends Controller{

    protected function _initialize() {
        $session = new Session();
        $teacher = $session->get('teacher_user');
        if(empty($teacher)){
//            $this->redirect('/');
            $this->error('请先登陆','/');
        }
    }


}