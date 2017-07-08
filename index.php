<?php
/**
 * Created by PhpStorm.
 * User: 22750
 * Date: 2017/7/7
 * Time: 20:13
 */
require './Wechat.class.php';
$wechat = new Wechat();
if($_GET["echostr"]){
    //验证
    $wechat->valid();
}else{
    //消息管理方法
    $wechat->responseMsg();
}
