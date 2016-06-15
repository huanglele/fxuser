<?php
/**
 * Created by PhpStorm.
 * author: huanglele
 * Date: 2016/6/6
 * Time: 14:46
 * Description:
 */
function isLogin(){
    $uid = session('uid');
    if($uid)return true;
    else return false;
}

/**
 *返回微信支付订单号
 * @return string 订单号
 */
function createTradeNum(){
    $trade = date('YmdHis').rand(0,9).rand(0,9).rand(0,9);
    if(M('Pay')->find($trade)){
        $trade = createTradeNum();
    }
    return $trade;
}

/**
 *返回微信付款订单号
 * @return string 订单号
 */
function createBizPayNum(){
    $trade = date('YmdHis').rand(0,9).rand(0,9).rand(0,9);
    $map['trade'] = $trade;
    if(M('pack')->where($map)->find()){
        $trade = createTradeNum();
    }
    return $trade;
}


/**
 * 发送微信红包提现模板消息
 * @param $oid number 订单ID
 */
function onBuyEvent($oid){
    $oInfo = M('order')->where(array('oid'=>$oid))->field('uid,gid')->find();
    $uInfo = M('user')->field('nickname,up1,up2,leader,agent,openid')->find($oInfo['uid']);
    $gInfo = M('goods')->field('gid,title,price,self,up1,up2,leader,status')->find($oInfo['gid']);
    $M = M('reward');

    //给自己发红包
    $d1['money'] = $gInfo['self'];
    $d1['note'] = $uInfo['nickname'].'购买'.$gInfo['title'];
    $d1['type'] = 1;
    $d1['time'] = time();
    $d1['uid'] = $oInfo['uid'];
    $d1['status'] = 1;
    $d1['price'] = $gInfo['price'];

    $d1['openid'] = $uInfo['openid'];
    $rid = $M->add($d1);
    $d1['rid'] = $rid;
    sendWxPackMsg($d1);
    if($uInfo['up1']){
        //给直接上级
        unset($d1['rid']);  //清除上级的发送红包
        $d1['money'] = $gInfo['up1'];
        $d1['note'] = $uInfo['nickname'].'购买'.$gInfo['title'];
        $d1['type'] = 2;
        $d1['time'] = time();
        $d1['uid'] = $uInfo['up1'];
        $d1['status'] = 1;
        $d1['price'] = $gInfo['price'];
        $rid = $M->add($d1);

        //获取用户的openid
        $d1['openid'] = M('user')->where(array('uid'=>$uInfo['up1']))->getField('openid');
        $d1['rid'] = $rid;
        sendWxPackMsg($d1);
    }
    if($uInfo['up2']){
        //给直接上级
        unset($d1['rid']);  //清除上级的发送红包
        $d1['money'] = $gInfo['up2'];
        $d1['note'] = $uInfo['nickname'].'购买'.$gInfo['title'];
        $d1['type'] = 3;
        $d1['time'] = time();
        $d1['uid'] = $uInfo['up2'];
        $d1['status'] = 1;
        $d1['price'] = $gInfo['price'];
        $rid = $M->add($d1);

        $d1['openid'] = M('user')->where(array('uid'=>$uInfo['up2']))->getField('openid');
        $d1['rid'] = $rid;
        sendWxPackMsg($d1);
    }
    if($uInfo['agent']){
        //给leader发红包
        unset($d1['rid']);  //清除上级的发送红包
        $d1['money'] = $gInfo['leader'];
        $d1['note'] = $uInfo['nickname'].'购买'.$gInfo['title'];
        $d1['type'] = 4;
        $d1['time'] = time();
        $d1['uid'] = $uInfo['leader'];
        $d1['status'] = 1;
        $d1['price'] = $gInfo['price'];
        $rid = $M->add($d1);

        $d1['openid'] = M('user')->where(array('uid'=>$uInfo['leader']))->getField('openid');
        $d1['rid'] = $rid;
        sendWxPackMsg($d1);
    }
}

function sendWxPackMsg($data){
    $Type = C('RewardType');
    $data['type'] = $Type[$data['type']];
    sendPackTempMsg($data);
}

/**
 * 发送领取红包微信模板消息
 * @return mixed
 */
function sendPackTempMsg($r){
    $data['touser'] = $r['openid'];
    $data['template_id'] = 'xi33bic9in_xajYNvNChvOsWy7zMDDuRULX5xDHNhg0';
    $data['url'] = U("user/packDetail?id=".$r['rid'],'',true,true);
    $arr['first'] = array('value'=>'您好,您收到了一个红包','color'=>'#173177');
    $arr['tradeDateTime'] = array('value'=>date('Y-m-d H:i',$r['time']),'color'=>'#173177');
    $arr['orderType'] = array('value'=>$r['type'],'color'=>'#173177');
    $arr['customerInfo'] = array('value'=>$r['note'],'color'=>'#173177');
    $arr['orderItemName'] = array('value'=>'红包金额','color'=>'#173177');
    $arr['orderItemData'] = array('value'=>$r['money'].'￥','color'=>'#173177');
    $arr['remark'] = array('value'=>'详情请登陆官网查看','color'=>'#173177');
    $data['data'] = $arr;
    $post = json_encode($data,true);
    $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.getWxAccessToken();
    $res = myCurl($url,array(CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$post));
    return $res;
}