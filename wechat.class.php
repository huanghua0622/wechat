<?php
/**
 * Created by PhpStorm.
 * User: 22750
 * Date: 2017/7/6
 * Time: 11:56
 */
//定义一个wechat类
//存储接口的调用方法
require './config.php';
class Wechat{

    private $appid;
    private $appsecret;
    private $token;
    //构造方法
    public function __construct($appid=APPID,$appsecret=APPSECRET,$token = TOKEN)
    {
         $this->appid = $appid;
         $this->appsecret = $appsecret;
         $this->token = $token;
         $this->textTpl = "<xml>
              <ToUserName><![CDATA[%s]]></ToUserName>
              <FromUserName><![CDATA[%s]]></FromUserName>
              <CreateTime>%s</CreateTime>
              <MsgType><![CDATA[%s]]></MsgType>
              <Content><![CDATA[%s]]></Content>
              <FuncFlag>0</FuncFlag>
              </xml>";
        $this->imgTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[image]]></MsgType>
            <Image>
            <MediaId><![CDATA[%s]]></MediaId>
            </Image>
            </xml>";
    }
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];

        //extract post data
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
//            if($postObj->MsgType == 'text'){
//                $this->doText($postObj);
//            }
            $type = $postObj->MsgTyope;
            switch($type){
                case 'text':
                    $this->doText($postObj);
                    break;
                case 'image':
                    $this->doImage($postObj);
                    break;
                case 'location':
                    $this->doLocation($postObj);
                    break;
                case 'voice':
                    $this->doVoice($postObj);  //处理语音消息
                    break;
                case 'event':
                    $this->doEvent($postObj);   //处理文本消息
                    break;
                default:
                    # code...
                    break;
            }
        }
    }
    public function doText($postObj)
    {
        $keyword = trim($postObj->Content);
        if(!empty($keyword)){
            $msgType = "text";
            $contentStr = "welcome to wechat world";
            switch($keyword){
                case '你是谁':
                    $contentStr = "我是帅哥!!!";
                    break;
                case '二维码':
                    $MediaId = 'SgqqSqh-WgFHKWcGNrP0EOhi3OTfI_bIfvJAh4zRHZwUz8ozLZIEWHHttJP3VP7h';
                     echo sprintf($this->imgTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $MediaId);
                     exit();
                     break;
            }
//            if($keyword == '你是谁'){
//                $contentStr = "我是帅哥!!!";
//            }elseif($keyword == "二维码"){
//                $MediaId = 'SgqqSqh-WgFHKWcGNrP0EOhi3OTfI_bIfvJAh4zRHZwUz8ozLZIEWHHttJP3VP7h';
//                echo sprintf($this->imgTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $MediaId);
//                exit();
//            }
        }
        $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $msgType, $contentStr);
        // file_put_contents('text1.xml',$resultStr);
        echo $resultStr;
    }
    public function doImage($postObj)
    {
        $MediaId = $postObj->MediaId;
        $resultStr = sprintf($this->imgTpl,$postObj->FromUserName, $postObj->ToUserName,time(),$MediaId);
        echo $resultStr;
    }
    public function doLocation($postObj)
    {
        $contentStr = '所在位置，纬度为:'.$postObj->Location_X.'经度为:'.$postObj->Location_Y;
        $resultStr = sprintf($this->textTpl,$postObj->FromUserName,$postObj->ToUserName,time,$contentStr);
        echo $resultStr;
    }
    public function request($url,$https=true,$method = 'get',$data=null)
    {
        $ch = curl_init($url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
        if($https === true){
            curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
            curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);
        }
        if($method === 'post'){
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
        }
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
    public function getAccessToken()
    {
        //url
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret;
        //请求方式
        //发送请求
        $content = $this->request($url);
        //处理返回数据
        $content = json_decode($content);
        echo $content->access_token;
    }
    public function getAccessTokenByFile()
    {
        $filename = './accesstoken';
        if(!file_exists($filename) || (time() - filemtime($filename)) >7200 ){
            //url
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret;
            //请求方式判断
            //发送请求
            $content = $this->request($url);
           $content = json_decode($content);
           $access_token = $content->access_token;
           file_put_contents($filename,$access_token);
        }else{
            $access_token = file_get_contents($filename);
        }
        return $access_token;
    }
    public function getAccessTokenByMem()
    {
        $mem = new Memcache();
        $mem->connect('127.0.0.1',11211);
        $access_token = $mem->get('accesstoken');
        if($access_token === false){
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret;
            //发送请求
            $content = $this->request($url);
            $content = json_decode($content);
            $access_token = $content->access_token;
            //保存在缓存中 并且设置过期的时间
            $mem->set('accesstoken',$access_token,0,8);
        }
        echo $access_token;
    }
    public function getAccessTokenByRedis()
    {
        $redis = new Redis();
        $redis->connect('127.0.0.1',6379);
        $access_token = $redis->get('accesstoken');
        if($access_token === false){
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->appid.'&secret='.$this->appsecret;
            //发送请求
            //返回的是json数据  先转化格式再进行取值
            $content = $this->request($url);
            $content = json_decode($content);
            $access_token = $content->access_token;
            $redis->set('accesstoken',$access_token);
            $redis->setTimeout('accesstoken',8);
        }
        return $access_token;
    }
    //tmp是来判断是永久的还是暂时的
    public function getTicket($scene_id,$expire_second=604800,$tmp=true)
    {
        $access_token = $this->getAccessTokenByFile();
        $url='https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.$access_token;
        if($tmp === true){
            $data = '{"expire_seconds": '.$expire_second.', "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
        }else{
            $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": '.$scene_id.'}}}';
        }
        //发送请求
        $content = $this->request($url,true,'post',$data);
        $content = json_decode($content);
//        echo $content;
        return $content->ticket;
        //这个只需要获取对应的ticket   下面再写方法  针对ticket码来进行操作 获取对于的二维码
    }
    public function getQRcode()
    {
        $ticket = 'gQG07jwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyWnRNVVlxZl9kRG0xclpqRk5wMXMAAgR9WWBZAwSAOgkA';
//        echo $ticket;die;
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$ticket;
//        echo $url;die;
        //发送请求
        $content = $this->request($url);
//        header('Content-Type:image/jpg');
//        echo $content;
        return file_put_contents('qrcode.jpg',$content);
    }
    //以上是分两步来获取二维码的
    //一步来获取二维码
    public function getQRcondeByFile($scene_id)
    {
       $ticket =  $this->getTicket($scene_id);
       $rc = $this->getQRcode($ticket);
       if($rc){
           echo "生成二维码成功";
       }else{
            echo "生成二维码失败";
       }
    }
    public function createMenu()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getAccessTokenByFile();
        $data = '{
               "button":[
               {
                    "type":"click",
                    "name":"资讯信息",
                    "key":"news"
                },
                {
                     "name":"php",
                     "sub_button":[
                     {
                         "type":"view",
                         "name":"百度",
                         "url":"http://www.baidu.com/"
                      },
                      {
                           "type":"view",
                           "name":"H5",
                           "url":"https://panteng.github.io/wechat-h5-boilerplate/",
                       },
                      {
                           "name": "发送位置",
                           "type": "location_select",
                           "key": "rselfmenu_2_0"
                      },]
                 }]
           }';
        $content = $this->request($url,true,'post',$data);
//        echo $content;die;
        $content = json_decode($content);
        if($content->errmsg == 'ok'){
            echo "创建菜单成功";
        }else{
            echo "创建菜单失败";
            echo "错误码为".$content->errcode;
            echo "错误信息".$content->errmsg;
        }
    }
    public function showMenu()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->getAccessTokenByFile();
        $content = $this->request($url);
//        $content = json_decode($content);
        var_dump($content);
    }
    //删除菜单
    public function delMenu()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=' . $this->getAccessTokenByFile();
        $content = $this->request($url);
        $content = json_decode($content);
        if($content->errmsg == 'ok'){
            echo "删除成功";
        }else{
            echo "删除失败";
            echo "错误代码".$content->errcode;
            echo "错误信息".$content->errmsg;
        }
    }
    public function getUserList()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token='.$this->getAccessTokenByFile();
        $content = $this->request($url);
//        echo '<pre>';
//        echo $content;
        $content = json_decode($content);
        echo "关注总人数为:".$content->total;
        echo "本次获取的人数为:".$content->total;
        echo "用户列表为<br>";
        foreach ($content->data->openid as $key => $value){
            echo "<table>";
            echo "<tr>";
            echo ($key+1).'<a href="http://localhost/wechat/do.php?openid='.$value.'">'.$value.'<a/>';
            echo "<tr/>";
            echo "<table/>";
        }
    }
    public function getUserInfo($openid)
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->getAccessTokenByFile().'&openid='.$openid.'&lang=zh_CN';
        $content = $this->request($url);
        $content = json_decode($content);
        echo "昵称:".$content->nickname.'<br/>';
        echo "城市:".$content->city.'<br/>';
        echo "省份:".$content->province.'<br/>';
        echo "性别:".$content->sex.'<br/>';
        echo '<img src='.$content->headimgurl.' style="width:100px;"/>'.'<br/>';
    }
    public function uploadFile()
    {
        $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.$this->getAccessTokenByFile().'&type=image';
        $data = array('file' => '@D:\phpstudy\WWW\wechat\qrcode.jpg');
        $content = $this->request($url,true,'post',$data);
        var_dump($content);
    }
    public function getFile()
    {
        $media_id = '3t--D2tXKhNcUyufkFqo6FawCjRuzRjWs1YZDnkRKdqtwlKUlUbQb_TicSq7fyxo';
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token='.$this->getAccessTokenByFile().'&media_id='.$media_id;
        $content = $this->request($url);
        //获取输出值 并保存
        echo file_put_contents(time().'.jpg',$content);
    }
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

}