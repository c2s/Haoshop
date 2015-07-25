<?php

namespace Admin\Controller;


use Think\Controller;
use Think\Upload;

class UploadController extends Controller{

    public function index(){
       //>>1.接受上传的文件到指定的文件夹中

        $dir = I('post.dir'); //上传的目录由浏览器上的上传插件 传递过来.

        $config = array(
            'exts'          =>  array('jpg', 'gif', 'png', 'jpeg'), //允许上传的文件后缀
            'rootPath'      =>  './', // 必须写./表示根
            'savePath'      =>  $dir.'/', //保存路径
            'driver'        =>  'Upyun', // 文件上传驱动
            'driverConfig'  =>  array(
                'host'     => 'v1.api.upyun.com', //又拍云服务器
                'username' => 'itsource', //又拍云操作员的用户
                'password' => 'itsource', //又拍云操作员的密码
                'bucket'   => 'itsource-'.$dir, //空间名称
                'timeout'  => 90, //超时时间
            ), // 上传驱动配置
        );
        $uploader = new Upload($config);
        $info = $uploader->uploadOne($_FILES['Filedata']);  //$_FILES['Filedata']
        if($info!==false){
            echo $info['savepath'].$info['savename'];
        }else{
            dump($uploader->getError());
        }

        exit;
    }
}