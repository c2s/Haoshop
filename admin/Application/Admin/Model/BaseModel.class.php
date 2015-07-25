<?php

namespace Admin\Model;


use Think\Model;
use Think\Page;

class BaseModel extends Model{
    // 是否批处理验证
    protected $patchValidate    =   true;

    /**
     * 查询出status>-1的数据
     * @return mixed
     */
    public function getListData()
    {
        return $this->where("status>-1")->select();
    }

    /**
     * 根据id更新status的值为-1
     * @param $id
     * @params $status 状态
     * @return bool
     *
     */
    public function changeStatus($id, $status)
    {
        $this->where(array('id' => array('in',$id)));
        return parent::save(array('status'=>$status));
    }

    /**
     * 得到分页数据:
     *   * pageResult关联数组:
     * array(
         * 'rows'=>'当前页列表数据',
     *   'pageHTML'=>'分页工具条数据'
     * )
     *
     */
    public function getPageResult($wheres = array())
    {

        //>>准备查询条件
        if(in_array('status',$this->getDbFields())){
            $wheres['obj.status'] = array('egt', 0);
        }
        //设置查询时的别名
        $this->alias('obj');
        //>>1.准备分页工具条代码
        //>>1.1 准备总条数
        $count = $this->where($wheres)->count();
        //>>1.2 准备每页多少条
        $pageSize = C('PAGE_SIZE');
        $page = new Page($count, $pageSize);
        $pageHTML = $page->show();


        /**
         * 解决:  直接修改页码的问题,导致最后一页没有数据.
         * 可以不考虑.
         */
        if ($page->firstRow >= $count && $count - $page->listRows >= 0) {
            $page->firstRow = $count - $page->listRows;
        }

        $this->alias('obj');
        /**
         * 当有status和sort列时才会排序
         */
        $orders = array();
        if(in_array('status',$this->getDbFields())){
            $orders['obj.status'] = 'desc';
        }

        if(in_array('sort',$this->getDbFields())){
            $orders['obj.sort'] = 'asc';
        }

        //向model中添加额外的拼接sql的方法
        $this->_setModel();

        //>>2.准备分页列表数据
        $rows = $this->order($orders)->where($wheres)->limit($page->firstRow, $page->listRows)->select();

        //多rows进行处理
        $this->_handlerRows($rows);

        return array('pageHTML' => $pageHTML, 'rows' => $rows);
    }

    /**
     * 该方法是被子类覆盖. 为当前Model添加其他的属性
     */
    protected function _setModel(){

    }

    /**
     * 被子类覆盖处理rows中的数据
     * @param $rows
     */
    protected function _handlerRows(&$rows){

    }





}