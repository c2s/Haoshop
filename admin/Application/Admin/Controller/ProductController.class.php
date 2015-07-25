<?php

namespace Admin\Controller;


use Think\Controller;

class ProductController extends Controller
{
    public function index($goods_id)
    {
        $productModel = D('Product');
        if(IS_POST){
            $result = $productModel->add(I('post.'));
            if($result!==false){
                $this->success('操作成功!',cookie('__forward__'));
            }else{
                $this->success('操作失败!'.showModelError($productModel));
            }
        }else{
            $result = $productModel->getProducts($goods_id);
            /**
             * $result => {
                 'headers'=>
                 'rows'=>
             * }
             */
            $this->assign($result);
            $this->assign('meta_title','产品列表');
            $this->display('index');
        }
    }
}