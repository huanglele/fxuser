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