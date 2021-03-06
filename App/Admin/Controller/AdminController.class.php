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
        $this->getData('Admin',$map,$order,'aid,time,name');
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
        $this->getData('Admin',$map,$order,'aid,time,name,city');
        $this->display('agent');
    }

    /**
     * 添加地区代理
     */
    public function addAgent(){
        $this->display('addAgent');
    }

    /**
     * 查看代理的详细信息
     */
    public function detail(){
        $id = I('get.id');
        $info = M('admin')->find($id);
        if($info && $info['role']==2){
            $this->assign('info',$info);
            $this->assign('CityCode',C('CityCode'));
            $this->display('detail');
        }
    }

    /**
     * 更新地区代理
     */
    public function update(){
        if(isset($_POST['submit'])){
            $aid = I('post.aid');
            $map['aid'] = $aid;
            $pwd = I('post.password','');
            if($pwd){$map['password'] = md5($pwd);}
            $map['city'] = I('post.city');
            M('admin')->save($map);
            $this->success('更新成功');
        }
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
            $data['city'] = $city;
            if($M->add($data)){
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }else{
            $this->display('adduser');
        }
    }

    /**
     * 设置前台用户升级代理的条件
     */
    public function applyTeam(){
        if(isset($_POST['submit'])){
            S('ApplyTeam',$_POST);
        }
        $info = S('ApplyTeam');
        $this->assign('info',$info);
        $this->display('applyTeam');
    }

    /**
     * 设置微信提现手续费
     */
    public function getCashRate(){
        if(isset($_POST['submit'])){
            S('getCashRate',$_POST);
        }
        $info = S('getCashRate');
        $this->assign('info',$info);
        $this->display('getCashRate');
    }

}