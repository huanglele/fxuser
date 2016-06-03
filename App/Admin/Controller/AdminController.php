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
        $user = I('get.user');
        if($user){
            $map['user'] = array('like','%'.$user.'%');
        }
        $this->assign('user',$user);
        $map['role'] = 0;
        $M = M('Admin');
        $order = 'aid desc';
        $this->getData($M,$map,$order);
        $this->display('index');
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
            if((!$name || !$pwd)) $this->error('请把表单填写完整');
            $M = M('Admin');
            if($M->where(array('name'=>$name))->find()) $this->error('用户名已存在');
            $data['name'] = $name;
            $data['password'] = md5($pwd);
            $data['time'] = time();
            $data['role'] = 0;
            $data['status'] = 0;
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