<?php

namespace Home\Controller;


use Think\Controller;

class ProductController extends Controller
{

    public function  getStockAndPrice($goods_attribute_ids,$goods_id){
       $row =  M('Product')->field('stock,price as shop_price')->where(array('goods_id'=>$goods_id,'goods_attribute_ids'=>$goods_attribute_ids))->find();
       $this->ajaxReturn($row);
    }
}