<?php
namespace Admin\Controller;


use Think\Controller;


class AttributeController extends BaseController{
    protected $meta_title = '属性';


    /**
     * 该方法被父类index调用...,为index.html页面分配数据..
     */
    protected function _index_view_before()
    {
        //查询所有类型并且将所有类型分配到页面
        $goodsTypeModel = D('GoodsType');
        $goodsTypes = $goodsTypeModel->getListData();
        $this->assign('goodsTypes',$goodsTypes);
    }


    protected function _edit_view_before(){
        //>>1.查询出所有的类型数据
           $goodsTypeModel = D('GoodsType');
           $goodsTypes = $goodsTypeModel->getListData();
        //>>2.分类到页面上
            $this->assign('goodsTypes',$goodsTypes);
    }

    /**
     * 覆盖父类中的该方法, 向wheres中添加查询条件.
     * @param $wheres
     */
    protected function _setWheres(&$wheres){
        $goods_type_id = I('get.goods_type_id');
        if(!empty($goods_type_id)){
            $wheres['obj.goods_type_id'] = $goods_type_id;
        }
    }

    /**
     * 根据商品类型id查询出该类型下的属性
     */
    public function getListByGoodsTypeId($goods_type_id){
        $rows = $this->model->getListByGoodsTypeId($goods_type_id);
        $this->ajaxReturn($rows);//以json的方式发送给浏览器
    }
}