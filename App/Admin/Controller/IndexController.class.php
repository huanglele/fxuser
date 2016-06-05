<?php
namespace Admin\Controller;

class IndexController extends CommonController {

    /**
     * 显示自己的信息
     */
    public function index(){
        $info = M('admin')->find(session('aid'));
        if($info['role']==1){
            $info['place'] = '全部地区';
        }else{
            $CityCode = C('CityCode');
            if(in_array($info['city'],$CityCode)){
                $info['place'] = $CityCode[$info['city']];
            }else{
                $info['place'] = '';
            }
        }
        $this->assign('info',$info);
        $this->assign('AdminRole',C('AdminRole'));
        $this->display('index');
    }

    public function t(){
        echo 'http://'.$_SERVER['HTTP_HOST'].__ROOT__.'/Public/images/hb.jpg';
    }

    /**
     * 修改自己的密码
     */
    public function pwd(){
        if(isset($_POST['submit'])){
            $pwd = I('post.pwd');
            $newpwd = I('post.newpwd');
            $repwd = I('post.repwd');
            if((!$pwd || !$newpwd || !$repwd)) $this->error('请把表单填写完整');
            if($pwd == $newpwd) $this->error('新旧密码不能相同');
            if($newpwd != $repwd)   $this->error('两次新密码不同');
            $M = M('Admin');
            $map['aid'] = $this->aid;
            $map['password'] = md5($pwd);
            $id = $M->where($map)->getField('aid');
            if(!$id) $this->error('原密码错误');
            $data['aid'] = $this->aid;
            $data['password'] = md5($newpwd);
            if($M->save($data)){
                $this->success('修改成功',U('index'));
            }else{
                $this->error('修改失败');
            }
        }else{
            $this->display('pwd');
        }
    }

}