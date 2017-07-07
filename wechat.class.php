<?php

//引入配置文件
require './wechat.cfg.ph
p';

//定义一个wechat类
//存储接口调用方法
class Wechat
{

    //封装
    // public   公共的  都可使用
    // protected   受保护的  继承类可以调用
    // private   私人   类内调用
    private $appid;
    private $appsecret;
    private $token;

    //实列化会触发
    //构造方法
    public function __construct($appid = APPID, $appsecret = APPSECRET, $token = TOKEN)
    {
        //初始化操作
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
        $this->itemTpl = "<item>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <PicUrl><![CDATA[%s]]></PicUrl>
            <Url><![CDATA[%s]]></Url>
            </item>";
        $this->newsTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[news]]></MsgType>
            <ArticleCount>%s</ArticleCount>
            <Articles>%s
            </Articles>
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
        $this->musicTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[music]]></MsgType>
            <Music>
            <Title><![CDATA[%s]]></Title>
            <Description><![CDATA[%s]]></Description>
            <MusicUrl><![CDATA[%s]]></MusicUrl>
            <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
            <ThumbMediaId><![CDATA[%s]]></ThumbMediaId>
            </Music>
            </xml>";
    }

    //验证方法
    public function valid()
    {
        $echoStr = $_GET["echostr"];

        //valid signature , option
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    //消息管理
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];
        file_put_contents('./text.xml',$postStr);
        //extract post data
        if (!empty($postStr)) {

            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            //判断根据不同的类型，进行不同的方法处理
            switch ($postObj->MsgType) {
                case 'text':
                    $this->doText($postObj);   //处理文本消息
                    break;
                case 'image':
                    $this->doImage($postObj);   //处理图片消息
                    break;
                case 'location':
                    $this->doLocation($postObj);   //处理位置消息
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

    //文本消息方法
    private function doText($postObj)
    {
        $keyword = trim($postObj->Content);
        if (!empty($keyword)) {
            $msgType = "text";
            $contentStr = "Welcome to wechat world!";
            switch ($keyword) {
              case '你是谁':
                $contentStr = '我是黑马4的小秘书！';
                break;
              case '二维码':
                $MediaId = 'ohY-hl4PdIWw0FDCsNFb2UK5FLIRHtOu6MCVnC89AWs0rqd2b-mS_8sekDDsqoCL';
                echo sprintf($this->imgTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $MediaId);
                exit();
                break;
              case '国歌':
                $this->sendMusic($postObj);
                exit();
                break;
              default:
                break;
            }
            $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $msgType, $contentStr);
            // file_put_contents('text1.xml',$resultStr);
            echo $resultStr;
        }
    }

    //图片消息处理
    private function doImage($postObj)
    {
        $picUrl = $postObj->PicUrl;
        $MediaId = $postObj->MediaId;
        // $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $picUrl);
        // file_put_contents('./testimg.xml',$resultStr);
        $resultStr = sprintf($this->imgTpl, $postObj->FromUserName, $postObj->ToUserName, time(), $MediaId);
        echo $resultStr;
    }

    //地址消息处理
    private function doLocation($postObj)
    {
        $contentStr = '所在位置,纬度为:' . $postObj->Location_X . ' 经度为:' . $postObj->Location_Y;
        //拼接xml
        $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $contentStr);
        echo $resultStr;
    }

    //语音消息处理
    private function doVoice($postObj)
    {
        $media_id = $postObj->MediaId;
        $resultStr = sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $media_id);
        echo $resultStr;
    }

    //事件消息处理
    private function doEvent($postObj)
    {
        //判断不同的事件，不同的方式处理
        switch ($postObj->Event) {
            case 'subscribe':
                $this->doSubscribe($postObj);  //关注事件处理
                break;
            case 'unsubscribe':
                $this->doUnsubscribe($postObj);  //取消关注事件处理
                break;
            case 'CLICK':
                $this->doClick($postObj);    //自定义菜单点击事件
                break;
            default:
                break;
        }
    }

    //自定义菜单点击事件处理
    private function doClick($postObj)
    {
        //根据不同的key值，进行不同方法的处理
        switch ($postObj->EventKey) {
            case 'news':
                $this->sendNews($postObj);  //发送资讯方法
                break;
            case 'song':
                $this->sendMusic($postObj);
                break;
              # code...
              break;
            default:
                # code...
                break;
        }
    }

    //发送图文
    private function sendNews($postObj)
    {
      //建议图文消息，最多5个，提高用户体验度
        //数据库取出数据二维数组
        $itemsArray = array(
            array(
                'Title' => '人类历史上最有用的发明，此刻就攥在你的手里',
                'Description' => '从遥远的石器时代到信息化的今天，每个时代各有它的特色。在漫长的时光里，人们用自己的聪明才智创造和丰富了整个世界，而各个时期的发明正是人类文明不可忽视的一部分。',
                'PicUrl' => 'http://imgsize.ph.126.net/?imgurl=http://cms-bucket.nosdn.127.net/bc3b9ae1e4ce4288a6f9aca2b6ed137320170707143705.jpeg_600x250x1x85.jpg',
                'Url' => 'http://data.163.com/17/0707/14/COOGAN6O000181IU.html',
            ),
            array(
                'Title' => '人类历史上最有用的发明，此刻就攥在你的手里',
                'Description' => '从遥远的石器时代到信息化的今天，每个时代各有它的特色。在漫长的时光里，人们用自己的聪明才智创造和丰富了整个世界，而各个时期的发明正是人类文明不可忽视的一部分。',
                'PicUrl' => 'http://imgsize.ph.126.net/?imgurl=http://cms-bucket.nosdn.127.net/bc3b9ae1e4ce4288a6f9aca2b6ed137320170707143705.jpeg_600x250x1x85.jpg',
                'Url' => 'http://data.163.com/17/0707/14/COOGAN6O000181IU.html',
            ),
            array(
                'Title' => '人类历史上最有用的发明，此刻就攥在你的手里',
                'Description' => '从遥远的石器时代到信息化的今天，每个时代各有它的特色。在漫长的时光里，人们用自己的聪明才智创造和丰富了整个世界，而各个时期的发明正是人类文明不可忽视的一部分。',
                'PicUrl' => 'http://imgsize.ph.126.net/?imgurl=http://cms-bucket.nosdn.127.net/bc3b9ae1e4ce4288a6f9aca2b6ed137320170707143705.jpeg_600x250x1x85.jpg',
                'Url' => 'http://data.163.com/17/0707/14/COOGAN6O000181IU.html',
            ),
        );
        //拼接item
        $items = '';
        foreach ($itemsArray as $key => $value) {
            $items .= sprintf($this->itemTpl, $value['Title'], $value['Description'], $value['PicUrl'], $value['Url']);
        }
        //new模板
        echo sprintf($this->newsTpl, $postObj->FromUserName, $postObj->ToUserName, time(), count($itemsArray), $items);
    }

    //关注事件处理
    private function doSubscribe($postObj)
    {
        $text = '欢迎关注我们PHP黑马4期，请随时联系！';
        echo sprintf($this->textTpl, $postObj->FromUserName, $postObj->ToUserName, time(), 'text', $text);
    }

    //取消关注事件处理
    private function doUnsubscribe($postObj)
    {
        //解绑用户信息和删除信息  记录取消关注的用户
    }
    //发送音乐消息
    private function sendMusic($postObj){
      $Title = '义勇军进行曲';
      $Description = '中国共和国国歌';
      $MusicUrl = 'http://wx.baibiannijiang.com/song.mp3';
      $HQMusicUrl = $MusicUrl;
      $ThumbMediaId = 'ohY-hl4PdIWw0FDCsNFb2UK5FLIRHtOu6MCVnC89AWs0rqd2b-mS_8sekDDsqoCL';
      echo sprintf($this->musicTpl, $postObj->FromUserName, $postObj->ToUserName, time(),$Title, $Description, $MusicUrl, $HQMusicUrl, $ThumbMediaId);
    }
    //签名加密校验
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    //请求方法
    //支持http https get  post
    public function request($url, $https = true, $method = 'get', $data = null)
    {
        //1.初始化
        $ch = curl_init($url);
        //2.设置参数
        //使用数据流的方式返回，而不是直接输出
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //判断请求协议
        if ($https === true) {
            //绕过ssl
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        //判断请求方式
        if ($method === 'post') {
            //设置post请求
            curl_setopt($ch, CURLOPT_POST, true);
            //post发送的数据
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        //3.发送请求
        $content = curl_exec($ch);
        //4.关闭链接
        curl_close($ch);
        return $content;
    }

    //获取access_token
    public function getAccessTokenByFile()
    {
        $filename = './accesstoken';
        //判断是否获取到缓存
        //缓存不存在，获取缓存过期
        if (!file_exists($filename) || (time() - filemtime($filename)) > 7000) {
            //缓存是否有效
            //1.url
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appid . '&secret=' . $this->appsecret;
            //2.请求方式
            //3.发送请求
            $content = $this->request($url);
            //4.处理返回值
            // var_dump($content);
            $content = json_decode($content);
            $access_token = $content->access_token;
            //获取新的accesss_token并存储
            file_put_contents($filename, $access_token);
        } else {
            //缓存存在，并且有效
            $access_token = file_get_contents($filename);
        }
        return $access_token;
    }

    //获取access_token 通过memcache
    public function getAccessTokenByMem()
    {
        //判断是否获取到缓存
        $mem = new Memcache();
        $mem->connect('127.0.0.1', 11211);
        $access_token = $mem->get('accesstoken');
        // var_dump($access_token);die();
        //缓存不存在，获取缓存过期
        if ($access_token === false) {
            //缓存是否有效
            //1.url
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appid . '&secret=' . $this->appsecret;
            //2.请求方式
            //3.发送请求
            $content = $this->request($url);
            //4.处理返回值
            // var_dump($content);
            $content = json_decode($content);
            $access_token = $content->access_token;
            //存到memcache
            $mem->set('accesstoken', $access_token, 0, 8);
        }
        echo $access_token;
    }

    //获取access_token 通过redis
    public function getAccessTokenByRedis()
    {
        //判断是否获取到缓存
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);
        $access_token = $redis->get('accesstoken');
        // var_dump($access_token);die();
        //缓存不存在，获取缓存过期
        if ($access_token === false) {
            //缓存是否有效
            //1.url
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appid . '&secret=' . $this->appsecret;
            //2.请求方式
            //3.发送请求
            $content = $this->request($url);
            //4.处理返回值
            // var_dump($content);
            $content = json_decode($content);
            $access_token = $content->access_token;
            //存到redis
            $redis->set('accesstoken', $access_token);
            $redis->setTimeout('accesstoken', 8);
        }
        echo $access_token;
    }

    //获取二维码ticket
    public function getTicket($scene_id, $expire_seconds = 604800, $tmp = true)
    {
        //1.url
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $this->getAccessTokenByFile();
        //2.请求方式
        //判断生成永久还是临时的
        if ($tmp === true) {
            //临时
            $data = '{"expire_seconds": ' . $expire_seconds . ', "action_name": "QR_SCENE", "action_info": {"scene": {"scene_id": ' . $scene_id . '}}}';
        } else {
            //永久
            $data = '{"action_name": "QR_LIMIT_SCENE", "action_info": {"scene": {"scene_id": ' . $scene_id . '}}}';
        }
        //3.发送请求
        //public function request($url,$https=true,$method='get',$data=null)
        $content = $this->request($url, true, 'post', $data);
        //4.处理返回值
        $content = json_decode($content);
        return $content->ticket;
    }

    //通过ticket获取二维码
    public function getQRCode($ticket)
    {
        $ticket = 'gQE78DwAAAAAAAAAAS5odHRwOi8vd2VpeGluLnFxLmNvbS9xLzAyenVXUU1vbzVkQjMxaThSRGhwMTEAAgQI_11ZAwSAOgkA';
        //1.url
        $url = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=' . $ticket;
        //2.判断请求
        //3.发送请求
        $content = $this->request($url);
        //4.处理返回值
        // header('Content-Type:image/jpg');
        // echo $content;
        //保存文件
        return file_put_contents('qrcode.jpg', $content);
    }

    //一步获取保存二维码
    public function getQRToFile($scene_id)
    {
        //1.获取ticket
        $ticket = $this->getTicket($scene_id);
        //2.ticket获取二维码并保存文件
        $rs = $this->getQRCode($ticket);
        //判断是否写入成功
        if ($rs) {
            echo '生成二维码成功,并已经保存为文件';
        } else {
            echo '生成失败！';
        }
    }

    //创建菜单
    public function createMenu()
    {
        //1.url
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token=' . $this->getAccessTokenByFile();
        //2.请求方式
        $data = '{
               "button":[
               {
                    "type":"click",
                    "name":"资讯信息",
                    "key":"news"
                },
                {
                     "name":"itheima4",
                     "sub_button":[
                      {
                           "type":"click",
                           "name":"国歌",
                           "key":"song"
                      }]
                 }]
           }';
        //3.发送请求
        $content = $this->request($url, true, 'post', $data);
        //4.处理返回值
        // {"errcode":0,"errmsg":"ok"}
        $content = json_decode($content);
        if ($content->errmsg == 'ok') {
            echo '创建菜单成功';
        } else {
            echo '创建菜单失败' . '<br />';
            echo '错误码为' . $content->errcode . '<br />';
            echo '错误信息为' . $content->errmsg . '<br />';
        }
    }

    //查看菜单
    public function showMenu()
    {
        //1.url
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/get?access_token=' . $this->getAccessTokenByFile();
        //2.判断请求方式
        //3.发送请求
        $content = $this->request($url);
        //4.处理返回值
        var_dump($content);
    }

    //删除菜单
    public function delMenu()
    {
        //1.url
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=' . $this->getAccessTokenByFile();
        //2.判断请求方式
        //3.发送请求
        $content = $this->request($url);
        //4.处理返回值
        $content = json_decode($content);
        if ($content->errmsg == 'ok') {
            echo '删除菜单成功';
        } else {
            echo '删除菜单失败' . '<br />';
            echo '错误码为' . $content->errcode . '<br />';
            echo '错误信息为' . $content->errmsg . '<br />';
        }
    }

    //获取用户的openID列表
    public function getUserList()
    {
        //1.url
        $url = 'https://api.weixin.qq.com/cgi-bin/user/get?access_token=' . $this->getAccessTokenByFile();
        //2.请求方式
        //3.发送请求
        $content = $this->request($url);
        //4.处理返回值
        $content = json_decode($content);
        // echo '<pre>';
        // var_dump($content);
        echo '关注人数为:' . $content->total . '<br />';
        echo '本次获取为:' . $content->count . '<br />';
        echo '用户列表<br />';
        foreach ($content->data->openid as $key => $value) {
            echo ($key + 1) . '####<a href="http://localhost/wechatheima4/do2.php?openid=' . $value . '">' . $value . '</a><br />';
        }
    }

    //获取用户的基本信息
    public function getUserInfo($openid)
    {
        // $openid = 'oGMVlw1EVD33CHImXP8hWCxJURvM';
        //1.url
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token=' . $this->getAccessTokenByFile() . '&openid=' . $openid . '&lang=zh_CN';
        //2.判断请求
        //3.发送请求
        $content = $this->request($url);
        //4.处理返回值
        $content = json_decode($content);
        echo '昵称:' . $content->nickname . '<br />';
        echo '性别:' . $content->sex . '<br />';
        echo '省份:' . $content->province . '<br />';
        echo '<img src="' . $content->headimgurl . '" style="width:100px;" /><br />';
    }

    //上传临时素材
    public function uploadFile()
    {
        //1.url
        $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token=' . $this->getAccessTokenByFile() . '&type=image';
        //2.请求方式数据格式
        $data = array('file' => '@D:\phpStudy\WWW\wechatheima4\qrcode.jpg');
        //3.发送请求
        $content = $this->request($url, true, 'post', $data);
        //4.处理返回值
        var_dump($content);
    }

    //下载素材
    public function getFile()
    {
        $media_id = 'ohY-hl4PdIWw0FDCsNFb2UK5FLIRHtOu6MCVnC89AWs0rqd2b-mS_8sekDDsqoCL';
        //1.url
        $url = 'https://api.weixin.qq.com/cgi-bin/media/get?access_token=' . $this->getAccessTokenByFile() . '&media_id=' . $media_id;
        //2.请求方式
        //3.发送请求
        $content = $this->request($url);
        //4.处理返回值
        echo file_put_contents(time() . '.jpg', $content);
    }

}
