<?php
/**
 * Created by PhpStorm.
 * User: 22750
 * Date: 2017/7/6
 * Time: 13:19
 */
require './Wechat.class.php';
$wechat = new Wechat();
//echo $wechat->getAccessToken();
//echo $wechat->getAccessTokenByFile();
//echo $wechat->getAccessTokenByMem();
//echo $wechat->getAccessTokenByRedis();
//echo  $wechat->getTicket(666);
//echo $wechat->getQRcode();
//echo $wechat->getQRcondeByFile(666);
//echo $wechat->createMenu();
//echo $wechat->delMenu();
//echo $wechat->getUserList();
//echo $wechat->getUserInfo($_GET['openid']);
echo $wechat->uploadFile();
//echo $wechat->getFile();