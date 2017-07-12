<?php
namespace Admin\Controller;
use Think\Controller;
class CheckController extends Controller {
    public function _initialize(){
      if(session('username')){
        //有session内容,把用户名称传到页面
        $this->assign('username',session('username'));
      }else{
        //没有session,就是没有登录
        $this->error('没有权限访问，请登录！');
      }
    }
    public function __construct(){
        parent::__construct();
        if(session('username')){
          //有session内容,把用户名称传到页面
          $this->assign('username',session('username'));
        }else{
          //没有session,就是没有登录
          $this->error('没有权限访问，请登录！');
        }
    }
}