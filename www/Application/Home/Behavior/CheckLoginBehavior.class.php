<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/13
 * Time: 14:04
 */

namespace Home\Behavior;


use Think\Behavior;

class CheckLoginBehavior extends Behavior{
    public function run(&$params){
        if(isLogin()){
            $userinfo = login();
            defined('UID') or define('UID',$userinfo['id']);
        }
    }
}