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
     * 购买商品
     */
    public function buy(){
        $gid = I('post.gid');
        $uid = session('uid');
        $gInfo = M('goods')->field('gid,price,self,up1,up2,leader,status')->find($gid);
        if($gInfo && $gInfo['status']==1) {
            $data = $_POST;
            //查询账户里面的钱够不够
            $uInfo = M('user')->where(array('uid'=>$uid))->field('up1,up2,leader,agent,money')->find();
            if($uInfo['money']<$gInfo['price']){$this->error('账户余额不足',U('user/pay'));die;}
            //扣钱 添加订单记录  发送红包
            $da1['uid'] = $uid;
            $da1['money'] = $uInfo['money']-$gInfo['price'];
            if($da1['vip']<$gInfo['price']) $da1['vip'] = $gInfo['price'];
            M('user')->save($da1);

            $data['uid'] = $uid;
            $data['time'] = time();
            $data['money'] = $gInfo['price'];
            $data['status'] = 2;
            $oid = M('order')->add($data);

            $allData = array();
            //给自己发红包
            $d1['money'] = $gInfo['self'];
            $d1['note'] = '升级红包';
            $d1['type'] = 1;
            $d1['time'] = time();
            $d1['uid'] = $uid;
            $d1['status'] = 1;
            $d1['price'] = $gInfo['price'];
            $allData[] = $d1;
            if($uInfo['up1']){
                //给直接上级
                $d1['money'] = $gInfo['up1'];
                $d1['note'] = '来自'.$uid.'的升级红包';
                $d1['type'] = 2;
                $d1['time'] = time();
                $d1['uid'] = $uInfo['up1'];
                $d1['status'] = 1;
                $d1['price'] = $gInfo['price'];
                $allData[] = $d1;
            }
            if($uInfo['up2']){
                //给直接上级
                $d1['money'] = $gInfo['up2'];
                $d1['note'] = '来自'.$uid.'的升级红包';
                $d1['type'] = 3;
                $d1['time'] = time();
                $d1['uid'] = $uInfo['up2'];
                $d1['status'] = 1;
                $d1['price'] = $gInfo['price'];
                $allData[] = $d1;
            }
            if($uInfo['agent']){
                //给leader发红包
                $d1['money'] = $gInfo['leader'];
                $d1['note'] = '来自'.$uid.'的升级红包';
                $d1['type'] = 4;
                $d1['time'] = time();
                $d1['uid'] = $uInfo['agent'];
                $d1['status'] = 1;
                $d1['price'] = $gInfo['price'];
                $allData[] = $d1;
            }
            M('reward')->addAll($allData);
            sendOrderTempMsg($oid);
            $this->success('购买成功',U('user/index'));
        }else{
            $this->error('商品不存在');
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