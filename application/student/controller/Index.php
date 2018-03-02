<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/2/14
 * Time: 21:45
 */
namespace app\student\controller;
use base\Studentbase;
class Index extends Studentbase{

    public function index(){
        echo 'welcome!';
        return $this->fetch('index');
    }
}