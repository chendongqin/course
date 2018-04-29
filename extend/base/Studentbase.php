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
class Studentbase extends Base {

    private $_user = [];
    protected function _initialize() {
        $student = Session::pull('student_user');
        if(empty($student)){
            $this->redirect('/');
        }
        $this->setUser($student[0]);
        $this->assign('student',$student[0]);
        $this->setUser($student[0]);
        Session::push('student_user',$student[0]);
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