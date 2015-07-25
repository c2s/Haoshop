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


    /* 数据缓存设置 */
    'DATA_CACHE_TIME'       =>  0,      // 数据缓存有效期 0表示永久缓存
    'DATA_CACHE_COMPRESS'   =>  false,   // 数据缓存是否压缩缓存
    'DATA_CACHE_CHECK'      =>  false,   // 数据缓存是否校验缓存
    'DATA_CACHE_PREFIX'     =>  '',     // 缓存前缀
    'DATA_CACHE_TYPE'       =>  'Redis',  // 数据缓存类型,支持:File|Db|Apc|Memcache|Shmop|Sqlite|Xcache|Apachenote|Eaccelerator
    'DATA_CACHE_PATH'       =>  TEMP_PATH,// 缓存路径设置 (仅对File方式缓存有效)
    'DATA_CACHE_KEY'        =>  '',	// 缓存文件KEY (仅对File方式缓存有效)
    'DATA_CACHE_SUBDIR'     =>  false,    // 使用子目录缓存 (自动根据缓存标识的哈希创建子目录)
    'DATA_PATH_LEVEL'       =>  1,        // 子目录缓存级别

    #指定redis服务器的配置
    'REDIS_HOST'            => '127.0.0.1',
    'REDIS_PORT'            => 6379,



    'HTML_CACHE_ON'     =>    false, // 开启静态缓存
    'HTML_CACHE_TIME'   =>    60,   // 全局静态缓存有效期（秒）
    'HTML_FILE_SUFFIX'  =>    '.shtml', // 设置静态缓存文件后缀
    'HTML_CACHE_RULES'  =>     array(  // 定义静态缓存规则
        // 定义格式1 数组方式
        'Index:index'    =>     array('index', 3600*24),
        'Index:goods'    =>     array('Goods/{id}', 3600*24*30)
    ),
    'ALIPAY_CONFIG'=>array(
        //合作身份者id，以2088开头的16位纯数字
        'partner'		=> '2088002155956432',
        //安全检验码，以数字和字母组成的32位字符
        'key'			=> 'a0csaesgzhpmiiguif2j6elkyhlvf4t9',
        'seller_email'	=> 'guoguanzhao520@163.com',
        //签名方式 不需修改
        'sign_type'    => strtoupper('MD5'),
        //字符编码格式 目前支持 gbk 或 utf-8
        'input_charset'=> strtolower('utf-8'),
        //ca证书路径地址，用于curl中ssl校验
        //请保证cacert.pem文件在当前文件夹目录中
        'cacert'    => getcwd().'\\cacert.pem',
        //访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
        'transport'    => 'http'
    )
);