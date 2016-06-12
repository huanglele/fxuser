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
        $this->display('link');
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
        $this->display('team');
    }
    /**
     * 获取团队信息
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
     * 微信支付
     */
    public function pay(){
        if(isset($_POST['money'])){
            $money = I('post.money',0);
            $uid = session('uid');
            if($money>0){
                $body = '充值';
                $attach = '充值';
                $tag = $uid;
                $trade_no = createTradeNum();
                $openId = session('openid');
                $Pay = A('Wechat');
                $order = $Pay->pay($openId,$body,$attach,$trade_no,$money*100,$tag);
                if($order['result_code']=='SUCCESS'){//生成订单信息成功
                    $data['uid'] = $uid;
                    $data['create_time'] = date('Y-m-d H:i:s');
                    $data['money'] = $money;
                    $data['pid'] = $trade_no;
                    $data['status'] = 1;
                    $data['pay_time'] = 0;
                    if(M('pay')->add($data)){
                        $this->assign('money',$money);
                        $this->display('paySub');die;
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
            $this->getData('pay',array('uid'=>session('uid'),'status'=>2),'pid desc');
            $this->display('pay');
        }
    }

    /**
     *显示提现记录
     */
    public function getCash(){
        if(isset($_POST['money'])){
            $money = I('post.money',0);
            $uid = session('uid');
            if($money>0){
                $Pay = new \Org\Wxpay\WxBizPay();
                $data['openid'] = session('openid');
                $data['amount'] = $money*100;
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
     * 返回个人微信推广二维码地址
     */
    private function getQrCode(){
        $ticket = $this->getTicke();
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
    private function getTicke(){
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

}