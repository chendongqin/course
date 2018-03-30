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
class Teacherbase extends Base{

    private $_user = [];

    protected function _initialize() {
        $session = new Session();
        $teacher = $session->get('teacher_user');
        if(empty($teacher)){
            $this->redirect('/');
//            $this->error('请先登陆','/');
        }
        $this->setUser($teacher[0]);
        $this->assign('teacher',$teacher[0]);
    }

    public function setUser($user){
        $this->_user = (array)$user;
        return $this;
    }

    public function getUser()
    {
        return $this->_user;
    }

}