<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/4
 * Time: 19:38
 */
namespace base;
use think\Controller;
class Base extends Controller{

    public function returnJson($msg='',$status = false,$code=0,$data=array()){
        $jsonData = array('status'=>$status,'msg'=>$msg,'code'=>$code,'data'=>$data);
        return json($jsonData);
    }

}