<?php

//>>1.检测环境
version_compare(PHP_VERSION,'5.3.0','>')  or  exit('PHP版本太低了...');
//>>2.web项目的根目录绝对路径
define('ROOT_PATH',dirname($_SERVER['SCRIPT_FILENAME']).'/');
//>>3.定义一个应用(Application)的目录
define('APP_PATH',ROOT_PATH.'Application/');
//>>4.定义运行时目录
define('RUNTIME_PATH',ROOT_PATH.'Runtime/');
//>>5.定义ThinkPHP框架所在的目录
define('THINK_PATH',dirname(ROOT_PATH).'/ThinkPHP/');
//>>6.开启提示模式
define('APP_DEBUG',True);
//绑定模块
define('BIND_MODULE','Home');

define('HTML_PATH',      ROOT_PATH.'Html/'); // 应用静态目录

//>>7.引入ThinkPHP的框架代码
require THINK_PATH.'ThinkPHP.php';