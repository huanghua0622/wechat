<?php
//引入类文件
require './wechat.class.php';
$wechat = new Wechat();
//判断是否来验证
if($_GET["echostr"]){
  //验证
  $wechat->valid();
}else{
  //消息管理方法
  $wechat->responseMsg();
}
