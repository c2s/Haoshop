<?php
defined('SRC_URL') or define('SRC_URL','http://www.shop.com');
return array(

    //定义替换字符串
    'TMPL_PARSE_STRING'  =>array(
        '__CSS__' => SRC_URL.'/Public/Home/css',
        '__JS__' => SRC_URL.'/Public/Home/js',
        '__IMG__' => SRC_URL.'/Public/Home/images',
        '__UPLOADIFY__'=>SRC_URL.'/Public/Home/uploadify',
        '__BRAND__'=> 'http://itsource-brand.b0.upaiyun.com/',
        '__GOODS__'=> 'http://itsource-goods.b0.upaiyun.com/',
    ),
);