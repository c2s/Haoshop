<?php

namespace Admin\Controller;


use Think\Controller;

class GenerateCodeController extends Controller{

    public function index(){
        if(IS_POST){
            //>>定义模板目录的常量
            defined('TPL_PATH') or define('TPL_PATH',ROOT_PATH.'template/');


            //>>1.接受请求参数的 全表名
                $table_name = I('post.table_name');
            //>>2.根据表名生成thinkphp中规范的名称
            /**
             * 例如:
             * itsource_brand==>Brand
             * itsource_goods_type=>GoodsType
             */
            //>>2.1 去掉前缀
                $db_prefix = C('DB_PREFIX');
                $name  = str_replace($db_prefix,'',$table_name);
            //>>2.2 根据表名生成规范的名称
                $name  = parse_name($name,1);  //第二个参数: 1表示表名生成规范的名字, 0:相反.


            //>>3.根据表名查询出表的注解
                $dbname = C('DB_NAME');
//                $rows = M()->query("select TABLE_COMMENT from information_schema.TABLES where TABLE_NAME='$table_name' and TABLE_SCHEMA='$dbname';");
                 $rows = M()->query("show table status like '$table_name'");
                 $meta_title = $rows[0]['Comment'];


            /**
             * mysql> show full columns from supplier;
            +--------+----------------------+-----------------+------+-----+---------+----------------+---------------------------------+----------------------+
            | Field  | Type                 | Collation       | Null | Key | Default | Extra          | Privileges                      | Comment              |
            +--------+----------------------+-----------------+------+-----+---------+----------------+---------------------------------+----------------------+
            | id     | smallint(5) unsigned | NULL            | NO   | PRI | NULL    | auto_increment | select,insert,update,references |                      |
            | name   | varchar(30)          | utf8_general_ci | NO   |     |         |                | select,insert,update,references | 供货商名称@text      |
            | intro  | text                 | utf8_general_ci | YES  |     | NULL    |                | select,insert,update,references | 描述@textarea        |
            | status | tinyint(4)           | NULL            | NO   |     | 1       |                | select,insert,update,references | 状态@radio|1=是,0=否 |
            | sort   | tinyint(4)           | NULL            | NO   |     | 20      |                | select,insert,update,references | 排序@text            |
            +--------+----------------------+-----------------+------+-----+---------+----------------+---------------------------------+----------------------+
             */
              //表中所有的列的详细信息
             $fields = M()->query("show full columns from {$table_name}");
             foreach($fields as &$field){
                 $field['name'] = strstr($field['Comment'],'@',true);
             }
            //>>4.生成控制代码文件
                //>>4.1加载模板文件再从ob缓存中获取去
                ob_start();
                require TPL_PATH.'controller.tpl';
                $content = "<?php\r\n".ob_get_clean();
                //>>4.2 将获取到的缓存内容输出到文件中..
                $controllerDir  = APP_PATH.'Admin/Controller/';
                if(!is_dir($controllerDir)){
                    mkdir($controllerDir,'777');
                }
                $controller_path = $controllerDir."{$name}Controller.class.php";
                file_put_contents($controller_path,$content);
            //>>5.生成模型
                ob_start();
                require TPL_PATH.'model.tpl';
                $content = "<?php\r\n".ob_get_clean();
                $modelDir  = APP_PATH.'Admin/Model/';
                $modelPath = $modelDir.$name.'Model.class.php';
                file_put_contents($modelPath,$content);
            //>>6.生成列表视图页面index.html
                ob_start();

                require  TPL_PATH.'index.tpl';
                $content = ob_get_clean();
                $viewDir = APP_PATH.'Admin/View/'.$name.'/';
                if(!is_dir($viewDir)){
                    mkdir($viewDir,'777');
                }
                $indexPath = $viewDir.'index.html';
                file_put_contents($indexPath,$content);

            //>>7. 生成表单页面edit.html
                ob_start();
                require  TPL_PATH.'edit.tpl';
                $content = ob_get_clean();
                $editDir = $viewDir.'edit.html';
                file_put_contents($editDir,$content);


                $this->success('生成成功!');


        }else{
            $this->display('index');
        }
    }
}