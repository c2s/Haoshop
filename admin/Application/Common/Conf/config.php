<?php
return array(
    'DB_TYPE'               =>  'mysql',     // 数据库类型
    'DB_HOST'               =>  'localhost', // 服务器地址
    'DB_NAME'               =>  'shop',          // 数据库名
    'DB_USER'               =>  'root',      // 用户名
    'DB_PWD'                =>  'admin',          // 密码
    'DB_PORT'               =>  '3306',        // 端口
    'DB_PREFIX'             =>  '',    // 数据库表前缀
    'PAGE_SIZE'             =>  5,  //每页多少条
    'DB_PARAMS'    =>          array(\PDO::ATTR_CASE => \PDO::CASE_NATURAL),

    'SHOW_PAGE_TRACE' =>true,  //开启调试小工具,
    'URL_MODEL'    =>1
);