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