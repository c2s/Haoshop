<?php

namespace Admin\Model;


use Think\Model;

class ProductModel extends Model
{

    public function add($requestData){
        //>>1.取出用户选择的商品组合
        $goods_attribute_idses =  $requestData['goods_attribute_ids'];

        //保存之前先删除
        $this->where(array('goods_id'=>$requestData['goods_id']))->delete();

        $rows = array();
        foreach($goods_attribute_idses as $goods_attribute_ids){
            $rows[] = array('goods_id'=>$requestData['goods_id'],
                'goods_attribute_ids'=>$goods_attribute_ids,
                'price'=>$requestData['price'][$goods_attribute_ids],
                'stock'=>$requestData['stock'][$goods_attribute_ids]);
        }
        if(!empty($rows)){
            $result = parent::addAll($rows);
            if($result===false){
                $this->error = '保存产品出错!';
                return false;
            }
        }
    }
    /**
     * 该方法的返回值为一下格式
     * $result => {
     * '    headers'=>  //提供列表的头
     * '    rows'=>     //列表的内容(当前商品的属性的自由组合)
     * }
     *
     *
     *当前商品的属性值的值自由组合的sql
     * select concat(t0.id,'#',t1.id) as goods_attribute_ids,t0.value as value0,t1.value as value1 from
        (select id,value from goods_attribute where goods_id = 16 and attribute_id =2) as t0,
        (select id,value from goods_attribute where goods_id = 16 and attribute_id = 6) as t1
     order by t0.id,t1.id
     *
     */
    public function  getProducts($goods_id){
        //>>1.准备headers的数据(当前商品的多值属性)
        $headers = $this->getMultValueAttribute($goods_id);
        //>>2.准备rows的数据
            //>>2.1 拼装自由组合的sql
                $sql = "select concat(";

                $goods_attribute_ids = array(); //存放id
                $values = array();//存放值
                $selects = array();

                foreach($headers as $k=>$header){
                    $goods_attribute_ids[] = "t{$k}.id";
                    $values[] = "t{$k}.value as value{$k}";
                    $selects[] = "(select id,value from goods_attribute where goods_id = {$goods_id} and attribute_id ={$header['id']}) as t{$k}";
                }

                $sql.=arr2str($goods_attribute_ids,",'#',").' ) as goods_attribute_ids,';
                $sql.=arr2str($values,',') .' from ';
                $sql.=arr2str($selects,',').' order by ';
                $sql.=arr2str($goods_attribute_ids,',');

            //>>2.2 再执行查询
                $rows = $this->query($sql);
                foreach($rows as &$row){
                    //>>这两步保证下的在前面
                    $goods_attribute_ids = str2arr($row['goods_attribute_ids'],'#');
                    sort($goods_attribute_ids);

                    $row['goods_attribute_ids'] = arr2str($goods_attribute_ids,'#');
                }

            //>>2.3.准备当前商品对应的产品
                $products = $this->where(array('goods_id'=>$goods_id))->select();
                $goods_attribute_ids = array_column($products,'goods_attribute_ids');
                $products = array_combine($goods_attribute_ids,$products);


        return  array('headers'=>$headers,'rows'=>$rows,'products'=>$products);
    }



    public function getMultValueAttribute($goods_id){
        $sql = "SELECT DISTINCT a.name,a.id FROM goods_attribute as ga join attribute as a on ga.attribute_id=a.id
where ga.goods_id = $goods_id  and a.attribute_type=2";

        $rows=  $this->query($sql);
        return $rows;
    }
}