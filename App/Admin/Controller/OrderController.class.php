<?php
/**
 * Created by PhpStorm.
 * author: huanglele
 * Date: 2016/6/6
 * Time: 9:44
 * Description:
 */

namespace Admin\Controller;

class OrderController extends CommonController
{

    /**
     * 查看所有的订单
     */
    public function index(){
        $map = array();
        $oid = I('get.oid');
        if($oid) $map['oid'] = $oid;
        $this->assign('oid',$oid);

        $uid = I('get.uid');
        if($uid) $map['uid'] = $uid;
        $this->assign('uid',$uid);

        $gid = I('get.gid',0,'number_int');
        if($gid) $map['gid'] = $gid;
        $this->assign('gid',$gid);

        $status = I('get.status',0,'number_int');
        if($status) $map['status'] = $status;
        $this->assign('status',$status);

        $this->assign('OrderStatus',C('OrderStatus'));
        $this->assign('CityCode',C('CityCode'));
        $this->getData('order',$map,'oid desc');
        $this->display('index');
    }

    /**
     * 更新订单
     */
    public function update(){
        $oid = I('post.m_oid');
        $data['oid'] = $oid;
        $s = I('post.m_status');
        $data['status'] = $s;
        M('order')->save($data);
        $this->success('更新成功');
    }

}