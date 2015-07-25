<?php

namespace Home\Model;


use Think\Model;

class RegionModel extends Model
{

    /**
     * 根据parent_id 查询出子节点的数据
     * @param $parent_id
     */
    public function getChildren($parent_id){
        $rows = $this->where(array('parent_id'=>$parent_id))->select();
        return $rows;
    }
}