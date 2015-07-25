<?php

namespace Home\Model;


use Think\Model;

class ShoppingCarModel extends Model
{

    /**
     * 将请求数据添加到购物车中
     * @param $requestData
     */
    public function toShoppingCar($requestData){
        $this->rebuildRequestData($requestData);
        dump($requestData);
        //>>1.没有登录的情况下
        if(!isLogin()){
            $this->toCookie($requestData);
        }else{
            //>>2.登录的情况下
            $this->toDB($requestData);
        }
    }

    /**
     * 重构请求参数
     * 将xxx数据重构为yyy数据
     *
     * array(4) {
        ["goods_attribute_ids"] => array(2) {
                ["颜色"] => string(1) "8"
                ["尺码"] => string(2) "10"
            }
            ["goods_attribute_strs"] => array(2) {
                ["颜色"] => string(6) "红色"
                ["尺码"] => string(1) "M"
            }
            ["amount"] => string(1) "1"
            ["goods_id"] => string(2) "16"
            }
         *
     *
     * array(4) {
        ["goods_attribute_ids"] => string(4) "8#10"
        ["goods_attribute_strs"] => string(26) "颜色:红色<br/>尺码:M"
        ["amount"] => string(1) "1"
        ["goods_id"] => string(2) "16"
        }
     * @param $requestData
     */
    private function rebuildRequestData(&$requestData){
        if(!empty($requestData['goods_attribute_ids'])){
            //说明当前购买的商品是 多库存多价格

            //组合商品属性的id
            $goods_attribute_ids = array_values($requestData['goods_attribute_ids']);
            sort($goods_attribute_ids);
            $requestData['goods_attribute_ids'] = arr2str($goods_attribute_ids,'#');

            //组合商品属性的值
            $goods_attribute_strs = array();
            foreach($requestData['goods_attribute_strs'] as $k=>$v){
                $goods_attribute_strs[]=$k.':'.$v;
            }
            $goods_attribute_strs = arr2str($goods_attribute_strs,'<br/>');
            $requestData['goods_attribute_strs'] = $goods_attribute_strs;
        }
    }

    /**
     * 没有登录
     * @param $requestData
     */
    public function toCookie($requestData){
        $shoppingCar = cookie('shopping_car');
        if(empty($shoppingCar)){
            //第一次购物
            $shoppingCar = array();//大的购物车
            //将请求中的数据封装到一个购物明细中
            $shoppingCarItem = array('goods_id'=>$requestData['goods_id'],'num'=>$requestData['amount']);
            if(!empty($requestData['goods_attribute_ids'])){
                $shoppingCarItem['goods_attribute_ids'] = $requestData['goods_attribute_ids'];
                $shoppingCarItem['goods_attribute_strs'] = $requestData['goods_attribute_strs'];
            }
            $shoppingCar[] = $shoppingCarItem;
        }else{
            $shoppingCar = unserialize($shoppingCar);

            $tag = false;  //没有
            foreach($shoppingCar as &$item){
               if(!empty($requestData['goods_attribute_ids'])){
                   //如果是多库存和多价格
                    if($item['goods_id']==$requestData['goods_id']  && $item['goods_attribute_ids'] ==$requestData['goods_attribute_ids']){
                        $item['num'] = $item['num']+ $requestData['amount'];
                        $tag = true;  //有了
                        break;
                    }
                }elseif($item['goods_id']==$requestData['goods_id']){
                   //是单库存和单价格
                    $item['num'] = $item['num']+ $requestData['amount'];
                    $tag = true;  //有了
                    break;
                }
            }
            if(!$tag){ //没有的情况下添加到购物车中
                $shoppingCarItem = array('goods_id'=>$requestData['goods_id'],'num'=>$requestData['amount']);
                if(!empty($requestData['goods_attribute_ids'])){
                    //多库存和多价格
                    $shoppingCarItem['goods_attribute_ids'] = $requestData['goods_attribute_ids'];
                    $shoppingCarItem['goods_attribute_strs'] = $requestData['goods_attribute_strs'];
                }
                $shoppingCar[] = $shoppingCarItem;
            }
        }
        cookie('shopping_car',serialize($shoppingCar),60*60*24*30);
    }

    /**
     * 登录的情况下
     * @param $requestData
     */
    public function toDB($data){
        //>>1.先判断该用户是否购买过, 如果购买过需要更新数量
        $goods_id = $data['goods_id'];
        $num = $data['amount'];

        $wheres = array('goods_id'=>$goods_id,'member_id'=>UID);
        if(!empty($data['goods_attribute_ids'])){
            //如果是多库存多价格的话, 需要将属性组合作为条件
            $wheres['goods_attribute_ids'] = $data['goods_attribute_ids'];
        }
        $count = $this->where($wheres)->count();
        if($count==0){
            //如果不存在,就添加一个购物数据
            $row = array('goods_id'=>$goods_id,'member_id'=>UID,'num'=>$num);

            if(!empty($data['goods_attribute_ids'])){
                $row['goods_attribute_ids'] = $data['goods_attribute_ids'];
                $row['goods_attribute_strs'] = $data['goods_attribute_strs'];
            }
            $result = $this->add($row);
            if($result===false){
                $this->error = '购物失败!';
                return false;
            }
        }else{
            //存在的情况下,将购物的数量更新到数据库表中
            $result = $this->where($wheres)->setInc('num',$num);
            if($result===false){
                $this->error = '购物失败!';
                return false;
            }
        }
    }


    public function getListData(){
        if(!isLogin()){
            //没有登录的情况下
            $shoppingCar = cookie('shopping_car');
            if($shoppingCar){
                $shoppingCar = unserialize($shoppingCar);
                //重构购物车
                $this->rebuildShoppingCar($shoppingCar);
                return $shoppingCar;
            }
        }else{
            //登录的情况下
            $shoppingCar = $this->where(array('member_id'=>UID))->select();
            $this->rebuildShoppingCar($shoppingCar);

            return $shoppingCar;
        }
    }

    /**
     * 根据商品的id,找到商品的相关信息...
     * @param $shoppingCar
     */
    public function rebuildShoppingCar(&$shoppingCar){
        $userinfo = login();
        $member_level_id = $userinfo['member_level_id'];
        $discount = $userinfo['discount'];


        $goodsModel = M('Goods');
        foreach($shoppingCar as &$item){
            //根据订单明细中的商品id找到goods表中的商品名称, logo,shop_price
            $row = $goodsModel->field('id,name,logo,shop_price')->find($item['goods_id']);

            //单独计算价格
            if(empty($item['goods_attribute_ids'])){
                //>>1.单库存和单价格
                  //>>1.1 判断当前商品是否有会员价格
                   $goodsMemberPrice = M('GoodsMemberPrice')->field('price')->where(array('goods_id'=>$item['goods_id'],'member_level_id'=>$member_level_id))->find();
                   if($goodsMemberPrice){
                       $price = $goodsMemberPrice['price'];
                   }else{
                       //1.2 在原有的本店价格上打折
                       $price = $row['shop_price'] * $discount;
                   }
                $row['shop_price'] = $price;

            }else{
                //多库存和多价格
                $price  = m('Product')->where(array('goods_id'=>$item['goods_id'],'goods_attribute_ids'=>$item['goods_attribute_ids']))->getField('price');
                //在原有价格上打折
                $price = $price * $discount;
                $row['shop_price'] = $price;
            }

            $item = array_merge($item,$row);
        }

    }


    /**
     * 该方法就是登录成功之后执行
     * 将cookie中的数据同步到购物车表(shopping_car)中
     */
    public function cookie2db(){
        $userinfo    = login();
        defined('UID') or define('UID',$userinfo['id']);

        //>>1.得到cookie中的购物数据
        $shoppingCar = cookie('shopping_car');
        if($shoppingCar){
            $shoppingCar = unserialize($shoppingCar);

            //>>2.将cookie中的购物数据保持到shopping_car表中
            foreach($shoppingCar as $item){
                //$item中是goods_id,num
                $item['amount'] = $item['num'];  //为了使用toDB的方法.. 因为toDB的方法中的商品数量为amount
                $this->toDB($item);
            }

            //>>3.删除cookie中的购物数据
            cookie('shopping_car',null);

        }
    }

}