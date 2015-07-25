<?php
namespace Admin\Controller;


use Think\Controller;


class GoodsCategoryController extends BaseController{
    protected $meta_title = '商品分类';


    public function index(){
        $rows = $this->model->getListData(false);
        $this->assign('rows',$rows);

        $this->assign('meta_title',$this->meta_title);

        cookie('__forward__',$_SERVER['REQUEST_URI']);
        $this->display('index');
    }

    protected function _edit_view_before()
    {
        //>>1.准备添加页面上需要的数据
        $rows = $this->model->getListData(true);
        $this->assign('rows',json_encode($rows));
    }


}