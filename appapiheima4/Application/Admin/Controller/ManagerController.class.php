<?php
namespace Admin\Controller;
use Think\Controller;
class ManagerController extends Controller {
    public function index(){
      //加载页面
        $this->display();
    }
    //音乐动态API
    public function newsApi(){
        $data = M('news')->select();
        echo json_encode($data);
    }
    //验证码生成方法
    public function verify(){
      //实列化验证码类
      $Verify = new \Think\Verify();
      //验证码长度
      $Verify->length = 4;
      //验证码内容
      $Verify->codeSet = '0123456789';
      //调用生成验证码方法
      $Verify->entry();
    }
    // 检测输入的验证码是否正确，$code为用户输入的验证码字符串
    public function check_verify($code, $id = ''){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }
    //添加管理员方法
    public function useradd(){
      //定义数据
      $data = array(
          'username' => 'admin',
          'password' => md5(123456),

        );
      //写入数据
      $rs = M('manager')->add($data);
      //写入成功返回是，主键值
      dump($rs);
    }
    //登录检验方法
    public function login(){
      //接收用户参数
      $data = I('post.');
      $username = $data['username'];
      $password = md5($data['password']);
      $verify = $data['verify'];
      //先进行验证码校验
      if($this->check_verify($verify)){
        //连接数据库进行校验
        $rs = M('manager')->where("username = '".$username."' AND password = '".$password."'")->find();
        if($rs){
          //查看匹配数据
          //验证通过
          $this->success('登录成功',U('Index/index'));
          //写入session
          // $_SESSION['username'] = $username;
          session('username',$username);
        }else{
          $this->error('用户名或者密码错误');
        }
      }else{
        $this->error('验证码错误,请重新输入');
      }
    }
    //退出登录
    public function logout(){
      //退出登录清空session
      session('username',null);
      $this->success('退出登录成功',U('Manager/Index'));
    }
}