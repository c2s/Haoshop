<?php
defined('SRC_URL') or define('SRC_URL','http://admin.shop.com');
return array(
    //定义替换字符串
    'TMPL_PARSE_STRING'  =>array(
        '__CSS__' => SRC_URL.'/Public/Admin/css',
        '__JS__' => SRC_URL.'/Public/Admin/js',
        '__IMG__' => SRC_URL.'/Public/Admin/images',
        '__UPLOADIFY__'=>SRC_URL.'/Public/Admin/uploadify',
        '__BRAND__'=> 'http://itsource-brand.b0.upaiyun.com/',
        '__GOODS__'=> 'http://itsource-goods.b0.upaiyun.com/',
        '__TREEGRID__'=> SRC_URL.'/Public/Admin/treegrid',
        '__ZTREE__'=> SRC_URL.'/Public/Admin/zTree_v3',
        '__UEDITOR__'=> SRC_URL.'/Public/Admin/ueditor',
    )
);