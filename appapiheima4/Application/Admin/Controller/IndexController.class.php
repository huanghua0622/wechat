<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends CheckController {
    public function index(){
        $this->display();
    }
}