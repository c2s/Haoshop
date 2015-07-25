<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/7/24
 * Time: 15:45
 */

namespace Home\Service;


class StockAndPriceService
{
    /**
     * 当前商品:
     *  单库存单价格===>goods(stock,shop_price)
     *  多库存多价格===>product()
     * @param $goods_id
     */
    public function getStockAndPrice($goods_id){
       $row =  M('Goods')->field('stock_type,stock,shop_price')->find($goods_id);
       if($row['stock_type']==2){
            //多库存和多价格
           $row = M('Product')->field('concat(max(price),"-",min(price)) as shop_price,sum(stock) as stock')->where(array('goods_id'=>$goods_id))->find();
       }
        return $row;
    }
}