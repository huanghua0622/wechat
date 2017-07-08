<?php
/**
 * Created by PhpStorm.
 * User: 22750
 * Date: 2017/7/7
 * Time: 13:28
 */
require './Wechat.class.php';
$wechat = new Wechat();
echo $wechat->getUserList();