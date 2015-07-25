<?php

namespace Admin\Controller;


use Think\Controller;

/**
 * 封装了基本的增删改查功能
 * @package Admin\Controller
 */
abstract class BaseController extends Controller{

    protected $model;
    /**
     * 是否使用请求中所有数据
     * @var bool
     */
    protected $usePostParams = false;

    public function _initialize()
    {
        $this->model = D(CONTROLLER_NAME);
    }

    public function index()
    {
        //>>1.接受查询参数
        $wheres = array();
        $keyword = I('get.keyword');
        if (!empty($keyword)) {
            $wheres['obj.name'] = array('like', "%{$keyword}%");
        }

        $this->_setWheres($wheres);

        /**
         * pageResult关联数组:
         * array(
         * 'rows'=>'当前页列表数据',
         *  'pageHTML'=>'分页工具条数据'
         * )
         */
        $pageResult = $this->model->getPageResult($wheres);
        $this->assign($pageResult);

        //将列表的url地址保存起来, 方法操作完修改或者编辑时调回当前列表页面
        cookie('__forward__', $_SERVER['REQUEST_URI']);

        $this->assign('meta_title',$this->meta_title);
        $this->_index_view_before();
        $this->display('index');
    }

    /**
     * 主要被子类覆盖为页面上提供数据
     */
    protected function _index_view_before(){

    }

    /**
     * 主要是被子类覆盖,向wheres条件中添加查询参数.
     * @param $wheres
     */
    protected  function  _setWheres(&$wheres){

    }

    /**
     * 根据id将改行的状态修改为status
     * @param $id
     * @param $status   -1:默认值, 表示删除一行记录
     */
    public function changeStatus($id, $status = -1)
    {
        $result = $this->model->changeStatus($id, $status);
        if ($result !== false) {
            $this->success('操作成功!', cookie('__forward__'));
        } else {
            $this->error('操作失败!' . showModelError($this->model));
        }
    }

    public function edit($id)
    {
        if (IS_POST) {
            if ($this->model->create() !== false) {
                if ($this->model->save($this->usePostParams?I('post.'):'') !== false) {
                    $this->success('更新成功!', cookie('__forward__'));
                    return;
                }
            }
            $this->error('更新失败!' . showModelError($this->model));
        } else {
            $row = $this->model->find($id);
            $this->assign($row);
            $this->_edit_view_before();
            $this->assign('meta_title','编辑'.$this->meta_title);
            $this->display('edit');
        }
    }

    public function add()
    {
        if (IS_POST) {
            if ($this->model->create() !== false) {
                if ($this->model->add($this->usePostParams?I('post.'):'') !== false) {
                    $this->success('添加成功!', cookie('__forward__'));
                    return;
                }
            }
            $this->error('添加失败!' . showModelError($this->model));
        } else {
            $this->assign('meta_title','添加'.$this->meta_title);
            $this->_edit_view_before();  //如果子类覆盖_edit_view_before时, 就是调用子类的该方法..
            $this->display('edit');
        }
    }

    /**
     * 该方法主要是用来被子类覆盖.. 编辑页面执行展示之前 执行一些业务逻辑
     */
    protected function _edit_view_before(){

    }

}