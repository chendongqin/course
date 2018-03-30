<?php
/**
 * Created by PhpStorm.
 * User: Viter
 * Date: 2018/3/25
 * Time: 12:12
 */
namespace app\admin\controller;
use base\Adminbase;
use think\Db;
class Student extends Adminbase
{

    public function index(){
        $request = $this->request;
        $page =  (int)$request->param('page',1,'int');
        $name =$request->param('name','','string');
        $where = [];
        if(!empty($name))
            $where['name'] = array('like','%'.$name.'%');
        $pager = Db::name('students')
            ->where($where)
            ->paginate(10,false,['page'=>$page])
            ->toArray();
        $this->assign('pager',$pager);
        return $this->fetch();
    }

    public function ban(){
        $id = $this->request->param('id','','int');
        $student = Db::name('students')->where('Id',$id)->find();
        if(empty($student))
            return $this->returnJson('学生不存在');
        $update = ['Id'=>$student['Id'],'ban'=>1];
        $res = Db::name('students')->update($update);
        if(!$res)
            return $this->returnJson('操作失败，请重试');
        return $this->returnJson('操作成功',true,1);
    }



}