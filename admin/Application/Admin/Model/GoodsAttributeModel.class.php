<?php

namespace Admin\Model;


use Think\Model;

class GoodsAttributeModel extends Model
{


    /**
     * 根据商品的id找到当前商品的属性值
     * @param $id
     *
     * 返回的数据结构
     * array(4) {
            [2] => array(3) {
                [0] => string(6) "红色"
                [1] => string(6) "黑色"
                [2] => string(6) "蓝色"
            }
            [3] => array(1) {
                [0] => string(6) "花裙"
            }
            [4] => array(1) {
                [0] => string(9) "很好看"
            }
            [5] => array(1) {
                [0] => string(6) "广东"
            }
    }
     */
    public function getListByGoodsId($goods_id){
        $rows = $this->field('attribute_id,value')->where(array('goods_id'=>$goods_id))->select();
        $temps = array();

        foreach($rows as $row){
            $temps[$row['attribute_id']][] = $row['value'];
        }

        return json_encode($temps);
    }


    /**
     * 查询出当前商品的多值属性的值
     * @param $goods_id
     */
    public function getMultValueGoodsAttribute($goods_id){
        $sql = "select ga.attribute_id,ga.value from goods_attribute as ga
join attribute as a on ga.attribute_id =a.id
where ga.goods_id = $goods_id and a.attribute_type =2";

        $rows = $this->query($sql);
        /**
         * rows==>
         * array(2) {
            [0] => array(2) {
            ["attribute_id"] => string(1) "2"
            ["value"] => string(6) "红色"
        }
        [1] => array(2) {
            ["attribute_id"] => string(1) "2"
            ["value"] => string(6) "黑色"
        }
        }
         *
         * 转化为:
         * array(1) {
            [2] => array(2) { //attribute_id
                [0] => string(6) "红色"
                [1] => string(6) "黑色"
             }
        }
         */

        $temp = array();
        foreach($rows as $row){
            $temp[$row['attribute_id']][] = $row['value'];
        }
        return $temp;
    }
}