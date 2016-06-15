<?php
/**
 * Created by PhpStorm.
 * author: huanglele
 * Date: 2016/6/4
 * Time: 11:14
 * Description:
 */

/**
 * @param string $timestr 需要格式化的时间戳
 * @return bool|string 格式化后时间字符串
 */
function Mydate($timestr=''){
    if(''==$timestr){
        $timestr = time();
    }
    if($timestr==0){
        return '';
    }else {
        return date('Y-m-d H:i', $timestr);
    }
}

/**
 * @param $openId
 */
function getWxUserInfo($openId){
    $access = getWxAccessToken();
    $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=$access&openid=$openId&lang=zh_CN";
    $res = myCurl($url);
    $info = json_decode($res,true);
    return $info;
}


/**
 * @return mixed 微信凭证
 */
function getWxAccessToken(){
    /*$token = S('Wx-access_token');
    if(!$token){
        $Wx = C('Wx');
        $appId = $Wx['AppID'];
        $appSec = $Wx['AppSecret'];
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSec";
        $res = myCurl($url);
        $data = json_decode($res,true);
        $token = $data['access_token'];
        S('Wx-access_token',$token,$data['expires_in']-1000);
    }
    */
    $Wx = C('Wx');
    $appId = $Wx['AppID'];
    $appSec = $Wx['AppSecret'];
    $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=$appId&secret=$appSec";
    $res = myCurl($url);
    $data = json_decode($res,true);
    $token = $data['access_token'];
    return $token;
}


function myCurl($url,$data=false){
    $ch = curl_init();
    //设置超时
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,FALSE);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,FALSE);
    if($data){
        curl_setopt_array($ch,$data);
    }
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    //运行curl，结果以jason形式返回
    $res = curl_exec($ch);
    curl_close($ch);
    return $res;
}

/**
 * @param $tid 任务ID
 */
function sendZhongJiangTempMsg($tid){
    $taskInfo = M('task')->field('name,times,market_price,price,end_time,winner')->find($tid);
    if($taskInfo){
        $guessInfo = M('guess')->field('uid,time')->find($taskInfo['winner']);
        //查询用户openid
        $userInfo = M('user')->field('openid,subscribe')->find($guessInfo['uid']);
        if($userInfo['subscribe']){
            $data['touser'] = $userInfo['openid'];
            $data['template_id'] = '4Sn1KVnKk_O-_1zLcSM2YMUh5EGBgZyxPraIdDQU2EY';
            $data['url'] = U('user/mywin','',true,true);
            $arr['result'] = array('value'=>'恭喜您，中奖啦！','color'=>'#173177');
            $arr['totalWinMoney'] = array('value'=>'价值'.$taskInfo['market_price'].'元','color'=>'#173177');
            $arr['issueInfo'] = array('value'=>$taskInfo['name'].'第'.$taskInfo['times'].'期','color'=>'#173177');
            $arr['fee'] = array('value'=>$taskInfo['price'].'元','color'=>'#173177');
            $arr['betTime'] = array('value'=>Mydate($guessInfo['time']),'color'=>'#173177');
            $arr['remark'] = array('value'=>'详情请登陆官网查看','color'=>'#173177');
            $data['data'] = $arr;
            $post = json_encode($data,true);
            $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.getWxAccessToken();
            $res = myCurl($url,array(CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$post));
            return $res;
        }else{//没有关注
            return '没有关注';
        }
    }else{//查询中奖信息失败
        return '查询中奖信息失败';
    }
}

function sendTaskTempMsg($id,$type){
    if($type=='task'){
        $first = '你发布的任务有新进度';
        $Status = C('TaskStatus');
    }elseif($type=='goods'){
        $first = '你的商品状态更新了';
        $Status = C('GoodsStatus');
    }
    $info = M($type)->field('aid,name,status')->find($id);
    if($info){
        $user = M('admin')->field('wx_openid,user')->find($info['aid']);
        if($user['wx_openid']){
            $data['touser'] = $user['wx_openid'];
            $data['template_id'] = 'C3QfusfneaqNt4mvteI1t9YUvLEl9Ol-RfZ3BJTDALg';
            $data['url'] = U('../admin.php/common/login','',true,true);
            $arr['first'] = array('value'=>$first,'color'=>'#173177');
            $arr['keyword1'] = array('value'=>$info['name'],'color'=>'#173177');
            $arr['keyword2'] = array('value'=>$Status[$info['status']],'color'=>'#173177');
            $arr['keyword3'] = array('value'=>$Status[$info['status']],'color'=>'#173177');
            $arr['remark'] = array('value'=>'详情请登陆官网查看','color'=>'#173177');
            $data['data'] = $arr;
            $post = json_encode($data,true);
            $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.getWxAccessToken();
            $res = myCurl($url,array(CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$post));
            return $res;
        }
    }else{
        return false;
    }
}

function sendOrderTempMsg($oid){
    $info = M('order')->field('time,uid,money')->find($oid);
    if($info){
        $gInfo = M('goods')->field('name,openid')->find($info['gid']);
        if($gInfo['openid']){
            $data['touser'] = $gInfo['openid'];
            $data['template_id'] = 'xi33bic9in_xajYNvNChvOsWy7zMDDuRULX5xDHNhg0';
            $data['url'] = '';
            $arr['first'] = array('value'=>'您收到了一条新的订单','color'=>'#173177');
            $arr['tradeDateTime'] = array('value'=>date('Y-m-d H:i',$info['time']),'color'=>'#173177');
            $arr['orderType'] = array('value'=>$gInfo['name'],'color'=>'#173177');
            $arr['customerInfo'] = array('value'=>$info['uid'].'号会员','color'=>'#173177');
            $arr['remark'] = array('value'=>'详情请登陆官网查看','color'=>'#173177');
            $data['data'] = $arr;
            $post = json_encode($data,true);
            $url = 'https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.getWxAccessToken();
            $res = myCurl($url,array(CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>$post));
            return $res;
        }
    }
}