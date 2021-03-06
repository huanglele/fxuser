<?php
/**
 * Created by PhpStorm.
 * author: huanglele
 * Date: 2016/6/4
 * Time: 10:18
 * Description:
 */

namespace Admin\Controller;


class GoodsController extends CommonController
{
    /**
     * 列出所有商品
     */
    public function index(){
        $map = array();

        $gid = I('get.gid','','number_int');
        if($gid){
            $map['gid'] = $gid;
        }
        $this->assign('gid',$gid);

        $name = I('get.name');
        if($name){
            $map['name'] = array('like','%'.$name.'%');
        }
        $this->assign('name',$name);

        $status = I('get.status',0,'number_int');
        if($status){
            $map['status'] = $status;
        }
        $this->assign('status',$status);

        $M = M('goods');
        $count = $M->where($map)->count();
        $Page = new \Think\Page($count,25);
        $show = $Page->show();
        $list = $M->where($map)->field('gid,title,name,price,status')->order('gid desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->assign('page',$show);
        $this->assign('GoodsStatus',C('GoodsStatus'));
        $this->assign('list',$list);
        $this->display('index');
    }

    /**
     * 添加
     */
    public function add(){
        $this->assign('GoodsStatus',C('GoodsStatus'));
        $this->display();
    }

    /**
     * 修改一个商品
     */
    public function editor(){
        $id = I('get.id');
        $info = M('goods')->find($id);
        if($info){
            $this->assign('info',$info);
            $this->assign('GoodsStatus',C('GoodsStatus'));
            $this->display();
        }else{
            $this->error('参数错误',U('index'));
        }
    }

    /**
     * 添加商品 或者修改商品 处理表单
     */
    public function update(){
        if(isset($_POST['submit'])){
            $ac = I('post.submit');
            $data = $_POST;
            $M = D('Goods');
//            if(!$M->create($data))  $this->error($M->getError());
            //判断是否有文件上传
            if($_FILES['img']['error']==0){
                //处理图片
                $upload = new \Think\Upload(C('UploadConfig'));
                $info   =   $upload->upload();
                if($info) {
                    $data['img'] = $info['img']['savepath'].$info['img']['savename'];
                }else{
                    $this->error($upload->getError());
                }
            }

            if($ac == 'add'){
                $data['create_time'] = time();
                if($M->add($data)){
                    $this->updateVipMap();
                    $this->success('添加成功',U('index'));
                }else{
                    $this->error('添加失败请重试');
                }
            }elseif($ac == 'update'){
                $gid = I('post.gid');
                if(!$gid)   $this->error('参数错误',U('index'));
                if($M->save($data)){
                    $this->updateVipMap();
                    $this->success('更新成功',U('index'));
                }else{
                    $this->error('更新失败请重试');
                }
            }
        }else{
            $this->error('页面不存在',U('index'));
        }
    }

    private function updateVipMap(){
        $date = M('goods')->getField('price,name');
        $date['0.00'] = '普通会员';
        S('VipMap',$date);
    }

}