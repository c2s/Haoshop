<?php

namespace Home\Model;


use Think\Model;

class GoodsModel extends Model{

    /**
     * 根据状态得到商品信息
     * @param $goodsStatus
     * @return mixed
     */
    public function getGoodsByGoodsStatus($goodsStatus,$num=5){
        $sql  = "select g.id,g.logo,g.name,g.shop_price from goods as g where g.goods_status & {$goodsStatus} >0 and g.status=1 and g.is_on_sale=1 order by g.inputtime limit $num";
        return $this->query($sql);
    }


    public function find($id){
        //select  obj.*,b.name as brand_name  from goods as obj join brand as b on obj.brand_id = b.id where obj.id =
        $this->field('obj.*,b.name as brand_name');
        $this->alias('obj');
        $this->join('__BRAND__ as b on obj.brand_id = b.id');
        $this->where(array('obj.id'=>$id));
        $row = parent::find();
        return $row;
    }

    /**
     * 根据商品得到当前商品的属性
     * @param $goods_id
     */
    public function getGoodsAttribute($goods_id){
        $sql = "select ga.value,ga.id,a.name from goods_attribute as ga
join attribute as a on ga.attribute_id = a.id   where ga.goods_id = {$goods_id} and a.attribute_type=2";
       $rows =  $this->query($sql);

        $temp = array();
        foreach($rows as $row){
            $temp[$row['name']][] =$row;
        }
        return $temp;
    }
}