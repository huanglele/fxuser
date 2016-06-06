<?php
/**
 * Created by PhpStorm.
 * author: huanglele
 * Date: 2016/6/6
 * Time: 15:17
 * Description:
 */

namespace Home\Controller;
use Think\Controller;

class UserController extends Controller
{
    public function _initialize(){
        if(!isLogin()){
            if(strtolower(ACTION_NAME)!='login'){
                $this->login();
            }
        }
    }

    public function index(){
        $uid = session('uid');
        $info = M('user')->find($uid);
        $this->assign('info',$info);
        $this->assign('VipMap',S('VipMap'));
        $this->display('index');
    }

    /**
     * 微信登录
     */
    public function login(){
        $tools = new \Org\Wxpay\UserApi();
        $openId = $tools->GetOpenid();
        $wxInfo = $tools->getInfo();
        if(!$wxInfo || isset($wxInfo['errcode'])){
            $this->error('微信授权出错',U('index/index'));
        }
        $info = getWxUserInfo($openId);
        if(!$info || isset($info['errcode'])){
            var_dump($info);die;
            $this->error('登录出了点状况',U('index/index'));
        }

        //判断之前是否存储过用户资料
        $M = M('user');
        $data = array_merge($info,$wxInfo);

        session('openid',$openId);

        if(isset($data['headimgurl'])){
            $data['headimgurl'] = trim($data['headimgurl'],'0').'64';
        }
        $uInfo = $M->where(array('openid'=>$openId))->field('uid')->find();
        $uid = $uInfo['uid'];

        if($uid){
            session('uid',$uid);
            $this->redirect('user/index');
        }else{
            //第一次登录 添加到用户表里面
            $data['money'] = $data['vip'] = $data['leader'] = $data['agent'] = $data['up1'] = $data['up2'] = 0;
            $r = $M->add($data);
            if($r){
                session('uid',$r);
                $this->redirect('user/index');
            }
        }
    }

    /**
     * 我的资料
     */
    public function self(){

    }

    /**
     * 我的余额
     */
    public function money(){

    }

    /**
     * 我的链接
     */
    public function link(){

    }

    /**
     * 代理
     */
    public function agent(){

    }

    /**
     * 我的团队
     */
    public function team(){

    }

    /**
     * 我的订单
     */
    public function order(){

    }

}