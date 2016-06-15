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

        //查询推荐人
        if($info['up1']){
            $info['tuijian'] = M('user')->where(array('uid'=>$info['up1']))->getField('nickname');
        }else{
            $info['tuijian'] = '无';
        }

        $mapUser['up1|up2'] = session('uid');
        $this->assign('team',$this->getCount('user',$mapUser));

        $mapR['uid'] = session('uid');
        $this->assign('Pack',$this->getCount('reward',$mapR));
        $mapR['status'] = 1;
        $this->assign('PackL',$this->getCount('reward',$mapR));

        $this->display('index');
    }

    /**
     * 微信登录
     */
    public function login(){
        $this->checkJump();
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
        //写入关注时间
        session('subscribe_time',$data['subscribe_time']);

        if(isset($data['headimgurl'])){
            $data['headimgurl'] = trim($data['headimgurl'],'0').'64';
        }
        $uInfo = $M->where(array('openid'=>$openId))->field('uid,agent')->find();
        $uid = $uInfo['uid'];
        $jump = session('jump');
        if(!$jump){
            $jump = U('user/index');
        }
        session('jump',null);
        if($uid){
            session('uid',$uid);
            session('agent',$uInfo['agent']);
            header("Location:$jump");
        }else{
            //第一次登录 添加到用户表里面
            $data['money'] = $data['vip'] = $data['leader'] = $data['agent'] = $data['up1'] = $data['up2'] = 0;
            $r = $M->add($data);
            if($r){
                session('uid',$r);
                session('agent',0);
                header("Location:$jump");
            }
        }
    }

    public function checkJump(){
        $referer = $_SERVER['HTTP_REFERER'];
        $host = $_SERVER['HTTP_HOST'];
        $patten = "/^http:\/\/$host(\/index.php)?(.*)$/i";
        if(preg_match($patten,$referer,$arr)){
            $uri = $arr[2];
            if(!preg_match("/user\/login/i",$uri)){
                session('jump',$referer);
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
            $uInfo = M('user')->where(array('uid'=>$uid))->field('up1,up2,leader,agent,money,openid')->find();
            $data['uid'] = $uid;
            $data['time'] = time();
            $data['money'] = $gInfo['price'];

            //判断支付方式
            $type = I('post.type');
            if($type=='money'){     //余额支付
                if($uInfo['money']<$gInfo['price']){$this->error('账户余额不足',U('user/pay'));die;}
                //扣钱 添加订单记录  发送红包
                $da1['uid'] = $uid;
                $da1['money'] = $uInfo['money']-$gInfo['price'];
                if($da1['vip']<$gInfo['price']) $da1['vip'] = $gInfo['price'];
                M('user')->save($da1);
                $data['status'] = 2;
                $oid = M('order')->add($data);      //添加订单记录
                onBuyEvent($oid);   //发送红包
                sendOrderTempMsg($oid);
                $this->success('购买成功',U('user/index'));
            }else{      //微信支付
                $data['uid'] = $uid;
                $data['body'] = '充值';
                $data['attach'] = '充值';
                $data['money'] = $gInfo['price'];
                $data['status'] = 1;
                $oid = M('order')->add($data);
                $data['oid'] = $oid;
                $this->sendPayData($data);
            }
        }else{
            $this->error('商品不存在');
        }
    }


    /**
     * 我的资料
     */
    public function self(){
        $uid = session('uid');
        //个体信息
        $info = M('user')->find($uid);
        $this->assign('info',$info);
        $this->assign('VipMap',S('VipMap'));

        //查看自己的leader
        $mapUser['up1|up2'] = session('uid');
        $this->assign('team',$this->getCount('user',$mapUser));

        //统计红包
        $mapR['uid'] = session('uid');
        $this->assign('Pack',$this->getCount('reward',$mapR));
        $mapR['status'] = 1;
        $this->assign('PackL',$this->getCount('reward',$mapR));

        //统计下属
        $mapUp1['up1'] = session('uid');
        $this->assign('up1',$this->getCount('user',$mapUp1));
        $mapUp2['up2'] = session('uid');
        $this->assign('up2',$this->getCount('user',$mapUp2));

        $this->display('self');
    }


    /**
     * 我的链接
     */
    public function link(){
        $this->display('link');
    }
    /**
     * 生成我的关注推广链接
     */
    public function myLinkPic(){
        layout(false);
        C('SHOW_PAGE_TRACE',false);
        $qrImgPath = THINK_PATH.'../qrCodeImg/'.session('uid').'.jpg';
        if(!is_file($qrImgPath)){
            //没有自己的推广二维码
            if(!$this->getQrCode()){
                die('服务器出错');
            }
        }

        header('Content-Type: image/jpeg');
        $qr = imagecreatefromjpeg($qrImgPath);
        $r = imagejpeg($qr);
        imagedestroy($qr);
    }

    /**
     * 代理
     */
    public function agent(){
        //检查是不是代理
        $uInfo = M('user')->where(array('uid'=>session('uid')))->field('agent,leader')->find();
        if($uInfo['agent']==1){//自己是代理
            //统计自己团队有多少人
            $map['leader'] = session('uid');
            $num = $this->getCount('user',$map);
            $this->assign('num',$num);
            $this->display('agentOk');
        }else{//自己不是代理
            $con = S('ApplyTeam');
            $map['vip'] = array('egt',$con['price']);
            $map['up1'] = session('uid');
            $num = $this->getCount('user',$map);
            $this->assign('con',$con);
            $this->assign('num',$num);
            $this->display('agentNo');
        }
    }

    /**
     * 升级代理申请
     */
    public function setApply(){
        $uid = session('uid');
        $con = S('ApplyTeam');
        $map['vip'] = array('egt',$con['price']);
        $map['up1'] = $uid;
        $num = $this->getCount('user',$map);
        if($num<$con['num']){
            $this->error('你的推荐会员数量不够');die;
        }else{
            $uInfo = M('user')->where(array('uid'=>$uid))->field('nickname,money,agent,leader')->find();
            if($uInfo['agent']){$this->error('你已经是代理了',U('user/index'));die;}
            //查询账户余额
            $leftMoney = $uInfo['money'];
            if($leftMoney<$con['money']){$this->error('账户余额不足',U('user/pay'));die;}
            $data['uid'] = $data['leader'] = $uid;
            $data['agent'] = 1;
            $data['money'] = $leftMoney-$con['money'];
            session('agent',1);
            M('user')->save($data);
            if($uInfo['leader']){   //给之前的代理发红包
                $d1['money'] = $con['leader'];
                $d1['note'] = '来自'.$uInfo['nickname'].'的升级代理红包';
                $d1['type'] = 5;
                $d1['time'] = time();
                $d1['uid'] = $uInfo['leader'];
                $d1['status'] = 1;
                $d1['price'] = 0;
                $rid = M('reward')->add($d1);

                $d1['openid'] = M('user')->where(array('uid'=>$uInfo['leader']))->getField('openid');
                $dl['rid'] = $rid;
                sendWxPackMsg($d1);
            }
            $this->success('操作成功');
        }
    }

    /**
     * 我的下属
     */
    public function team(){
        $this->display('team');
    }
    /**
     * 获取我的下属信息
     */
    public function getTeamList(){
        $p = I('p',1,'number_int');
        $type = I('get.type');
        if($type=='up2'){
            $map['up2'] = session('uid');
        }else{
            $map['up1'] = session('uid');
        }
        $list = $this->getData('user',$map,'uid desc','nickname,headimgurl,vip');
        $num = count($list);
        if($num){
            $VipMap = S('VipMap');
            for($i=0;$i<$num;$i++){
                $list[$i]['vip'] = $VipMap[$list[$i]['vip']];
            }
        }
        $ret['status'] = 'success';
        $ret['num'] = $num;
        $ret['list'] = $list;
        if($num==10)  $p++;
        $ret['page'] = $p;
        $this->ajaxReturn($ret);
    }

    /**
     * 我的团队
     */
    public function group(){
        //先判断自己是不是leader
        $agent = session('agent');
        if($agent){
            $this->display('group');
        }else{
            $this->error('你还不是代理',U('user/agent'));
        }
    }
    /**
     * 获取我的下属信息
     */
    public function getGroupList(){
        $p = I('p',1,'number_int');
        $map['leader'] = session('uid');
        $list = $this->getData('user',$map,'uid desc','nickname,headimgurl,vip');
        $num = count($list);
        if($num){
            $VipMap = S('VipMap');
            for($i=0;$i<$num;$i++){
                $list[$i]['vip'] = $VipMap[$list[$i]['vip']];
            }
        }
        $ret['status'] = 'success';
        $ret['num'] = $num;
        $ret['list'] = $list;
        if($num==10)  $p++;
        $ret['page'] = $p;
        $this->ajaxReturn($ret);
    }


    /**
     * 我的订单
     */
    public function order(){
        $map['uid'] = session('uid');
        $list = $this->getData('order',$map,'oid desc');
        $gids[] = 0;
        if(count($list)){
            foreach($list as $v){
                $gids[] = $v['gid'];
            }
            $gInfo = M('goods')->where(array('gid'=>array('in',$gids)))->getField('gid,name,img');
            $this->assign('gInfo',$gInfo);
            $this->assign('CityCode',C('CityCode'));
        }
        $this->display('order');
    }

    /**
     * 我的红包
     */
    public function redpack(){
        $map['uid'] = session('uid');
        $status = I('get.status');
        if($status){
            $map['status'] = $status;
        }

        $this->getData('reward',$map,'rid desc');
        $this->assign('Status',C('RewardStatus'));
        $this->assign('Type',C('RewardType'));
        $this->assign('VipMap',S('VipMap'));
        $this->display('redpack');
    }

    /**
     * 红包内页
     */
    public function packDetail(){
        $id = I('get.id');
        $map['rid'] = $id;
        $map['uid'] = session('uid');
        $info = M('reward')->where($map)->find();
        if($info){
            if($info['status']=='1') {
                $uInfo = M('user')->field('nickname,headimgurl')->find(session('uid'));
                $this->assign('uInfo',$uInfo);
                $this->assign('Status', C('RewardStatus'));
                $this->assign('info', $info);
                $this->assign('Type', C('RewardType'));
                $this->assign('VipMap', S('VipMap'));
                $this->display('packDetail');
            }else{
                $this->error('红包已领取过',U('user/index'));
            }
        }else{
            $this->error('红包不存在',U('user/index'));
        }
    }

    /**
     * 领取红包
     */
    public function openRedPack(){
        $id = I('get.id');
        $map['rid'] = $id;
        $map['uid'] = session('uid');
        $info = M('reward')->where($map)->find();
        if($info){
            if($info['status']=='1'){
                //判断自己的等级
                $mapU['uid'] = session('uid');
                $myVip = M('user')->where($mapU)->getField('vip');
                if($info['price']>$myVip){$this->error('你的等级不够',U('index/index'));die;}
                M('user')->where($map)->setInc('money',$info['money']);
                M('reward')->where($map)->setField('status',2);
                $this->success('领取成功',U('user/index'));
            }else{
                $this->error('红包已领取',U('user/index'));
            }
        }else{
            $this->error('红包不存在',U('user/index'));
        }
    }


    /**
     * 微信支付
     */
    public function pay(){
        if(isset($_POST['money'])){
            $money = I('post.money',0);
            $uid = session('uid');
            if($money>0){
                $data['uid'] = $uid;
                $data['body'] = '充值';
                $data['attach'] = '充值';
                $data['money'] = $money;
                $data['oid'] = 0;
                $this->sendPayData($data);
            }else{
                $this->error('输入金额有误');
            }
        }else{
            $this->getData('pay',array('uid'=>session('uid'),'status'=>2),'pid desc');
            $this->display('pay');
        }
    }

    private function sendPayData($da){
        $body = $da['body'];
        $attach = $da['attach'];
        $tag = $da['uid'];
        $trade_no = createTradeNum();
        $openId = session('openid');
        $Pay = A('Wechat');
        $order = $Pay->pay($openId,$body,$attach,$trade_no,intval($da['money']*100),$tag);
        if($order['result_code']=='SUCCESS'){//生成订单信息成功
            $data['uid'] = $da['uid'];
            $data['oid'] = $da['oid'];
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['money'] = $da['money'];
            $data['pid'] = $trade_no;
            $data['status'] = 1;
            $data['pay_time'] = 0;
            if(M('pay')->add($data)){
                $this->assign('money',$da['money']);
                $this->display('user/paySub');die;
            }else{
                $this->error('操作失败请重试');die;
            }
        }else{
            $this->error('操作失败请重试');die;
        }
    }

    /**
     *显示提现记录
     */
    public function getCash(){
        $uid = session('uid');
        $uMoney = M('user')->where(array('uid'=>$uid))->getField('money');
        $CashRate = S('getCashRate');
        if(!$CashRate) $CashRate = array('rate'=>10,'money'=>'100');
        if(isset($_POST['money'])){
            $money = I('post.money',0);
            if($money>0){
                if($uMoney<$CashRate['money']){$this->error('低于提现最低金额');die;}
                $Pay = new \Org\Wxpay\WxBizPay();
                $data['openid'] = session('openid');
                $data['amount'] = intval($money*(100-$CashRate['rate']));
                $data['partner_trade_no'] = createBizPayNum();
                $data['desc'] = '提现操作';
                $res = $Pay->send($data);
                if($res['result_code']=='SUCCESS'){//生成订单信息成功
                    $data['uid'] = $uid;
                    $data['time'] = date('Y-m-d H:i:s');
                    $data['money'] = $money;
                    $data['trade'] = $data['partner_trade_no'];
                    $data['status'] = 2;
                    if(M('pack')->add($data)){
                        M('user')->where(array('uid'=>$uid))->setDec('money',$money);
                        $this->success('提现成功',U('user'));
                    }else{
                        $this->error('操作失败请重试');die;
                    }
                }else{
                    $this->error('操作失败请重试');die;
                }
            }else{
                $this->error('输入金额有误');
            }
        }else{
            $this->assign('uMoney',$uMoney);
            $this->assign('CashRate',$CashRate);
            $this->getData('pack',array('uid'=>session('uid'),'status'=>2),'pid desc');
            $this->display('getCash');
        }
    }

    protected function getData($table,$map,$order,$field=false){
        $M = M($table);
        $count = $M->where($map)->count();
        $Page = new \Think\Page($count,10);
        $show = $Page->show();
        if($field){
            $list = $M->where($map)->field($field)->order($order)->limit($Page->firstRow,$Page->listRows)->select();
        }else{
            $list = $M->where($map)->order($order)->limit($Page->firstRow,$Page->listRows)->select();
        }
        $this->assign('list',$list);
        $this->assign('page',$show);
        return $list;
    }


    /**
     * 返回个人微信推广二维码地址
     */
    private function getQrCode(){
        $ticket = $this->getTicket();
        if($ticket){
            $qrUrl = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.urldecode($ticket);
            $pic = myCurl($qrUrl);
            $filePath = THINK_PATH.'../qrCodeImg/'.session('uid').'.jpg';
            file_put_contents($filePath,$pic);
            return true;
        }else{
            die('没有获取到了ticket');
            return false;
        }
    }

    /**
     * http请求方式: POST
     *   URL: https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=TOKENPOST数据格式：json
     *   POST数据例子：{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": 123}}}
     * 或者也可以使用以下POST数据创建字符串形式的二维码参数：
     * {"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "123"}}}
     */
    private function getTicket(){
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.getWxAccessToken();
        $data = '{"action_name": "QR_LIMIT_STR_SCENE", "action_info": {"scene": {"scene_str": "'.session('uid').'"}}}';
        $curlArr = array(CURLOPT_POSTFIELDS=>$data);
        $res = json_decode(myCurl($url,$curlArr),true);
        if(isset($res['ticket'])){
            return $res['ticket'];
        }else{
            return false;
        }
    }

    /**
     * @param $table 表名
     * @param $map 条件
     * @return number 统计数据
     */
    private function getCount($table,$map){
        return M($table)->where($map)->count();
    }

}