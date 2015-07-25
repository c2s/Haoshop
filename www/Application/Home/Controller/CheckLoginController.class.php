<?php

namespace Home\Controller;


use Think\Controller;

class CheckLoginController extends Controller
{

    public function _initialize(){
        if(!isLogin()){

            //>>1.得到用户正在请求的地址
            $request_url = $_SERVER['REQUEST_URI'];
            session('login_forward',$request_url);

            $this->success('请登录!',U('Member/login'));
            exit;
        }
    }
}