<?php

namespace Home\Model;


use Think\Model;

class GoodsGalleryModel extends Model{

    /**
     * 根据商品id得到当前上面的相册数据
     * @param $goods_id
     */
    public function getGalleryPath($goods_id){
        $rows = $this->field('path')->where(array('goods_id'=>$goods_id))->select();
        return array_column($rows,'path');
    }



}