<?php

namespace Admin\Model;


class DbMysqlInterfaceImplModel implements  DbMysqlInterfaceModel{
    /**
     * DB connect
     *
     * @access public
     *
     * @return resource connection link
     */
    public function connect()
    {
        // TODO: Implement connect() method.
        dump('connect...');
        exit;
    }

    /**
     * Disconnect from DB
     *
     * @access public
     *
     * @return viod
     */
    public function disconnect()
    {
        // TODO: Implement disconnect() method.
        dump('disconnect...');
        exit;
    }

    /**
     * Free result
     *
     * @access public
     * @param resource $result query resourse
     *
     * @return viod
     */
    public function free($result)
    {
        // TODO: Implement free() method.
        dump('free...');
        exit;
    }

    /**
     * Execute simple query
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return resource|bool query result
     */
    public function query($sql, array $args = array())
    {
        $sql = $this->buildSQL(func_get_args());
        return M()->execute($sql);
    }

    /**
     * Insert query method
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return int|false last insert id
     */
    public function insert($sql, array $args = array())
    {
        //>>1.得到所有的实际参数
        $args = func_get_args();
        //>>2.得到传递过来的sql模板
        $sql = array_shift($args);
        //>>3.使用args中的第一个值替换 $sql中的?T
        $sql = str_replace('?T',$args[0],$sql);
        //>>4.准备set后面的参数
        $params = $args[1];
        $set = '';
        foreach($params as $k=>$v){
            $set.="`$k`='$v',";
        }
        $set = rtrim($set,',');

        //>>5.使用set拼接好的参数替换 ?%
        $sql = str_replace('?%',$set,$sql);
        $result = M()->execute($sql);
        if($result===false){  //失败失败返回false
            return false;
        }else{ //执行成功返回插入的id
            return M()->getLastInsID();
        }
    }

    /**
     * Update query method
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return int|false affected rows
     */
    public function update($sql, array $args = array())
    {
        // TODO: Implement update() method.
        dump('update...');
        exit;
    }

    /**
     * Get all query result rows as associated array
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return array associated data array (two level array)
     */
    public function getAll($sql, array $args = array())
    {
        // TODO: Implement getAll() method.
        dump('getAll...');
        exit;
    }

    /**
     * Get all query result rows as associated array with first field as row key
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return array associated data array (two level array)
     */
    public function getAssoc($sql, array $args = array())
    {
        // TODO: Implement getAssoc() method.
        dump('getAssoc...');
        exit;
    }

    /**
     * Get only first row from query
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return array associated data array
     */
    public function getRow($sql, array $args = array())
    {
        $sql = $this->buildSQL(func_get_args()); //根据实际传递过来的参数让buildSQL拼好sql
        $rows = M()->query($sql);
        return $rows[0]; //因为该方法需要返回一条数据
    }


    /**
     * sql分割出来的效果
     * array(8) {
        [0] => string(7) "SELECT "
        [1] => string(2) ", "
        [2] => string(2) ", "
        [3] => string(2) ", "
        [4] => string(6) " FROM "
        [5] => string(7) " WHERE "
        [6] => string(3) " = "
        [7] => string(0) ""
        }
        需要放到sql中的参数
        array(7) {
        [0] => string(9) "parent_id"
        [1] => string(3) "lft"
        [2] => string(4) "rght"
        [3] => string(5) "level"
        [4] => string(14) "goods_category"
        [5] => string(2) "id"
        [6] => int(9)
    }
     */
    private function buildSQL($args){
        $sql = array_shift($args); //得到实际参数中的sql
        $sqls = preg_split('/\?[FTN]/',$sql);

        $sql =  '';
        foreach($sqls as $k=>$v){  //将sql模板和模板中的参数拼接起来 成为一个完整的sql
            $sql.=$v.$args[$k];
        }
        return $sql;
    }

    /**
     * Get first column of query result
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return array one level data array
     */
    public function getCol($sql, array $args = array())
    {
        // TODO: Implement getCol() method.
        dump('getCol...');
        exit;
    }

    /**
     * Get one first field value from query result
     *
     * @access public
     * @param string $sql SQL query
     * @param array $args query arguments
     *
     * @return string field value
     */
    public function getOne($sql, array $args = array())
    {
        // TODO: Implement getOne() method.
        dump('getOne...');
        exit;
    }

}