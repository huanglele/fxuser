<?php
/**
 * Author: huanglele
 * Date: 2016/6/11
 * Time: ���� 11:07
 * Description:
 */

namespace Admin\Controller;


class PayController extends CommonController{

    /**
     * ΢��֧���б�
     */
    public function index(){
        $map = array();
        $uid = I('get.uid');
        if($uid){
            $map['uid'] = $uid;
        }
        $this->assign('uid',$uid);

        $pid = I('get.pid');
        if($pid){
            $map['pid'] = $pid;
        }
        $this->assign('pid',$pid);

        $status = I('get.status',0);
        if($status){
            $map['status'] = $status;
        }
        $this->assign('status',$status);

        $order = 'pid desc';
        $this->getData('pay',$map,$order);
        $this->assign('Status',C('PayStatue'));

        $this->display('index');
    }

    /**
     * վ�ں���б�
     */
    public function reward(){
        $map = array();
        $uid = I('get.uid');
        if($uid){
            $map['uid'] = $uid;
        }
        $this->assign('uid',$uid);

        $type = I('get.type');
        if($type){
            $map['type'] = $type;
        }
        $this->assign('type',$type);

        $status = I('get.status',0);
        if($status){
            $map['status'] = $status;
        }
        $this->assign('status',$status);

        $order = 'rid desc';
        $this->getData('reward',$map,$order);
        $this->assign('Status',C('RewardStatus'));
        $this->assign('Type',C('RewardType'));

        $this->display('reward');
    }

    /**
     * ΢������
     */
    public function pay(){
        $map = array();
        $uid = I('get.uid');
        if($uid){
            $map['uid'] = $uid;
        }
        $this->assign('uid',$uid);

        $pid = I('get.pid');
        if($pid){
            $map['pid'] = $pid;
        }
        $this->assign('pid',$pid);

        $status = I('get.status',0);
        if($status){
            $map['status'] = $status;
        }
        $this->assign('status',$status);

        $order = 'pid desc';
        $this->getData('reward',$map,$order);
        $this->assign('Status',C('PayStatue'));

        $this->display('pay');
    }


}