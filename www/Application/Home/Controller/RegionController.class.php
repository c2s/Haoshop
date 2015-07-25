<?php

namespace Home\Controller;


use Think\Controller;

class RegionController extends Controller
{
    /**
     * 根据父地区,得到子地区
     * @param $parent_id
     */
    public function getChildren($parent_id){
        $regionModel = D('Region');
        $rows = $regionModel->getChildren($parent_id);
        $this->ajaxReturn($rows);
    }
}