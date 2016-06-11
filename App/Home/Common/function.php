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