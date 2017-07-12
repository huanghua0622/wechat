<?php

namespace Admin\Controller;

use Think\Controller;

class NewsController extends CheckController
{

    //显示音乐动态列表
    public function index()
    {
      //查询出数据
      $data = M('news')->select();
      // foreach ($data as $key => &$value) {
      //   foreach ($value as $k => &$v) {
      //     if($k == 'smallimg'){
      //       $v = substr($v, 1);
      //       $v = BASEURL.$v;
      //     }
      //   }
      // }
      // dump($data);die();
      $this->assign('data',$data);
      $this->display();
    }

    //上传图片方法
    public function upload()
    {
        $upload = new \Think\Upload(); // 实例化上传类
        $upload->maxSize = 3145728; // 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg'); // 设置附件上传类型
        // $upload->savePath = './Public/Uploads/'; // 设置附件上传目录    // 上传文件
        $info = $upload->upload();
        if (!$info) {
            // 上传错误提示错误信息
            return $this->error($upload->getError());
        } else {
            // 上传成功
            return $upload->rootPath . $info['image']['savepath'] . $info['image']['savename'];
        }
    }

    //添加音乐动态
    public function add()
    {
        //通过请求方式
        //判断操作
        if (IS_POST) {
        //处理表单数据
            $data = I('post.');
        //处理上传
            $data['smallimg'] = $this->upload();
            $data['addtime'] = time();
            $rs = M('news')->add($data);
            if ($rs) {
                $this->success('添加成功', U('index'));
            } else {
                $this->error('添加失败');
            }
            // dump($data);die();
        } else {
        //加载页面
            $this->display();
        }
    }

}
