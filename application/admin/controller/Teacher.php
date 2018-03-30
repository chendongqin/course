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
class Teacher extends Adminbase
{

    public function index(){
        $request = $this->request;
        $page =  (int)$request->param('page',1,'int');
        $name =$request->param('name','','string');
        $where = [];
        if(!empty($name))
            $where['name'] = array('like','%'.$name.'%');
        $pager = Db::name('teachers')
            ->where($where)
            ->paginate(10,false,['page'=>$page])
            ->toArray();
        $this->assign('pager',$pager);
        return $this->fetch();
    }

    public function data(){
        $id = $this->request->param('id','','int');
        $teacher = Db::name('teachers')->where('Id',$id)->find();
        if(empty($teacher))
            return $this->returnJson('教师不存在');
        return $this->returnJson('获取成功',true,1,$teacher);
    }

    public function ban(){
        $id = $this->request->param('id','','int');
        $teacher = Db::name('teachers')->where('Id',$id)->find();
        if(empty($teacher))
            return $this->returnJson('教师不存在');
        $update = ['Id'=>$teacher['Id'],'ban'=>1];
        $res = Db::name('teachers')->update($update);
        if(!$res)
            return $this->returnJson('操作失败，请重试');
        return $this->returnJson('操作成功',true,1);
    }

    public function audit(){
        $request = $this->request;
        $page =  (int)$request->param('page',1,'int');
        $name =$request->param('name','','string');
        $where = ['audit_status'=>0];
        if(!empty($name))
            $where['name'] = array('like','%'.$name.'%');
        $pager = Db::name('virefy_teacher')
            ->where($where)
            ->paginate(10,false,['page'=>$page])
            ->toArray();
        $this->assign('pager',$pager);
        return $this->fetch();
    }



}