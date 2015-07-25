<?php

namespace Home\Controller;


use Think\Controller;

class MemberController extends Controller{

    public function register(){
        if(IS_POST){
            $memberModel =  D('Member');
            if($memberModel->create()){
                if($memberModel->add()!==false){
//                    $this->success('注册成功!',U('login'));
                    echo 'success';
                    return;
                }
            }
            $this->error('注册失败!'.showModelError($memberModel));
        }else{
            $this->display('register');
        }
    }

    public function login(){
        if(IS_POST){
            $memberModel = D('Member');
            if($memberModel->create()){
                $result = $memberModel->login();
                if(is_array($result)){
                    //将用户的信息保持到session中
                    login($result);

                    //将cookie中的购物信息同步到购物车表中
                    $shoppingCarModel = D('ShoppingCar');
                    $shoppingCarModel->cookie2db();

                    if(session('login_forward')){
                        $this->success('登录成功!',session('login_forward'));
                    }else{
                        $this->success('登录成功!',U('Index/index'));
                    }
                    return;
                }else{
                    $this->error('登录失败!'.showModelError($memberModel));
                }
            }

        }else{
            $this->display('login');
        }
    }

    public  function logout(){
        logout();
        $this->success("注销成功!");
    }


    public function check(){

        //>>1.接受验证时传递过来的参数
        $params = I('get.');
        $memberModel = D('Member');
        $result = $memberModel->checkField($params);
        //只要是js需要的数据都要变成json字符串
        echo json_encode($result);
    }


    public function fire($id,$key){
        $memberModel = D('Member');
        $result = $memberModel->fire($id,$key);
        if($result!==false){
            $this->success('激活成功!',U('login'));
        }else{
            $this->error('激活失败!'.showModelError($memberModel),U('register'));
        }
    }



    public function test(){
        //>>1.php默认的情况下是单线程..
        header('Content-Type: text/html;charset=utf-8');

//        $start = microtime(true);
        G('begin');

        //>>2.开启一个线程执行 下面 两行代码
        $oneThread = new OneThread();
        $oneThread->start();


        //>>3.开启一个线程执行 下面 两行代码
        $twoThread = new TwoThread();
        $twoThread->start();


        //>>3.开启一个线程执行 下面 两行代码
        $threeThread = new ThreeThread();
        $threeThread->start();


//        $end = microtime(true);
//        echo '执行时间:'.($end-$start);

        $oneThread->join();
        $twoThread->join();
        $threeThread->join();
        G('end');
        echo G('begin','end').'s';
    }





    public function getUserInfo(){
        if(isLogin()){
            $userinfo = login();
             echo $userinfo['username'];
        }
    }
}


class  OneThread extends \Thread{
    public function run()  //必须是run方法..
    {
        //code: 在当前线程中需要执行的代码
        sleep(1);
        echo  '完成打地基!<br/>';

    }
}

class  TwoThread extends \Thread{
    public function run()  //必须是run方法..
    {
        sleep(1);
        echo  '完成和水泥!<br/>';
    }
}

class  ThreeThread extends \Thread{
    public function run()  //必须是run方法..
    {
        sleep(1);
        echo  '完成买砖!<br/>';
    }
}