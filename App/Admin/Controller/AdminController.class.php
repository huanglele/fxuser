<?php
/**
 * Created by PhpStorm.
 * author: huanglele
 * Date: 2016/6/3
 * Time: 17:02
 * Description:
 */

namespace Admin\Controller;


class AdminController extends CommonController
{

    /**
     * 查看所有管理员
     */
    public function index(){
        $map = array();
        $name = I('get.name');
        if($name){
            $map['user'] = array('like','%'.$name.'%');
        }
        $this->assign('name',$name);
        $map['role'] = 1;
        $M = M('Admin');
        $order = 'aid desc';
        $this->getData($M,$map,$order,'aid,time,name');
        $this->display('index');
    }

    /**
     * 添加管理员
     */
    public function addAdmin(){
        $this->display('addAdmin');
    }

    /**
     * 查看代理列表
     */
    public function agent(){
        $map = array();
        $name = I('get.name');
        if($name){
            $map['user'] = array('like','%'.$name.'%');
        }
        $this->assign('name',$name);
        $map['role'] = 2;
        $M = M('Admin');
        $this->assign('CityCode',C('CityCode'));
        $order = 'aid desc';
        $this->getData($M,$map,$order,'aid,time,name,city');
        $this->display('agent');
    }

    /**
     * 添加地区代理
     */
    public function addAgent(){
        $this->display('addAgent');
    }

    /**
     * 删除一个用户，不能删除自己
     */
    public function deluser(){
        $id = I('get.id');
        if($id == $this->aid) $this->error('不能删除自己');
        $M = M('Admin');
        if($M->delete($id)){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }


    /**
     * 添加一个用户
     */
    public function adduser(){
        if(isset($_POST['submit'])){
            $name = I('post.name');
            $pwd = I('post.pwd');
            $role = I('post.role','0','number_int');
            $city = I('post.city','0','number_int');
            if((!$name || !$pwd)) $this->error('请把表单填写完整');
            $M = M('Admin');
            if($M->where(array('name'=>$name))->find()) $this->error('用户名已存在');
            $data['name'] = $name;
            $data['password'] = md5($pwd);
            $data['time'] = time();
            $data['role'] = $role;
            if($M->add($data)){
                $this->success('添加成功',U('index'));
            }else{
                $this->error('添加失败');
            }
        }else{
            $this->display('adduser');
        }
    }



}