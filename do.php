<meta charset="utf-8">
<?php
//测试调用文件
//引入类文件
require './wechat.class.php';
//实列化
$wechat = new Wechat();
//方法调用
// $wechat->getAccessTokenByFile();
// $wechat->getAccessTokenByMem();
// $wechat->getAccessTokenByRedis();
// $wechat->getTicket(666);
// $wechat->getQRCode();
// $wechat->getQRToFile(666);
$wechat->createMenu();
// $wechat->showMenu();
// $wechat->delMenu();
// $wechat->getUserList();
// $wechat->getUserInfo();
// $wechat->uploadFile();
// $wechat->getFile();