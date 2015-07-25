<?php

namespace Home\Controller;


use Think\Controller;

class OrderInfoController extends CheckLoginController
{


    public function add(){
        if(IS_POST){
            $orderInfoModel = D('orderInfo');
            $result = $orderInfoModel->add(I('post.'));
            if($result!==false){

//                    $result;//订单号
                session('sn',$result);
                $this->success('下单成功!',U('pay'));
            }else{
                exit('failed');
                $this->error('下单失败!');
            }
        }else{
            //>>1.找到当前登录用户的收货信息
            $addressModel = D('Address');
            $addresses = $addressModel->getListData();
            $this->assign('addresses',$addresses);

            //>>2.准备送货方式
            $deliveryModel = M('Delivery');
            $deliveryes = $deliveryModel->select();
            $this->assign('deliveryes',$deliveryes);

            //>>3. 需要购物车中的数据
            $shoppingCarModel = D('ShoppingCar');
            $shoppingCar = $shoppingCarModel->getListData();
            $this->assign('shoppingCar',$shoppingCar);

            $this->display('edit');
        }
    }

    public function pay(){
        $row = D('OrderInfo')->field('price,pay_type')->where(array('sn'=>session('sn')))->find();
        $this->assign($row);
        $this->display('pay');
    }

    /**
     * 需要使用自付宝进行在线支付
     */
    public function doPay(){
        $orderInfoModel = D('orderInfo');
        $result = $orderInfoModel->doPay();

        header('Content-Type: text/html;charset=utf-8'); //确定发送给客户的浏览器的html代码不是乱码...(如果是乱码就会出错.)
        echo $result;
    }


    public function paySuccess(){
        //跳转到订单的列表页面
    }

    public function notify(){
        //跳转到订单的列表页面
        $orderInfoModel = D('orderInfo');
        //更改订单的状态(需要订单的参数)
        $orderInfoModel->notify();
    }


    public function timeout(){
        $orderInfoModel = D('OrderInfo');
        $orderInfoModel->timeout();
    }
}