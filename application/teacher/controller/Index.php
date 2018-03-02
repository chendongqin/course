<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/2/14
 * Time: 21:45
 */
namespace app\student\controller;
use base\Teacherbase;
class Index extends Teacherbase{

    public function index(){
        echo 'welcome!';
    }
}