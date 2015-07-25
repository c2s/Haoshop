<?php

namespace Home\Controller;


use Think\Controller;

class BuyController extends Controller{

    public function index(){
        $shoppingCarModel = D('ShoppingCar');
        $rows = $shoppingCarModel->getListData();
        $this->assign('rows',$rows);
        $this->display('index');
    }


    public function  addShoppingCar(){
        $requestData = I('post.');
        $shoppingCarModel = D('ShoppingCar');
        $shoppingCarModel->toShoppingCar($requestData);
        $this->success('添加成功!',U('index'));
    }
}