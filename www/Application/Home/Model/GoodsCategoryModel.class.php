<?php

namespace Home\Model;


use Think\Model;

class GoodsCategoryModel extends Model{

    public function getGoodsCategoryTreeData(){
        //>>1.先查询缓存
        $treeData = S('GoodsCategoryTreeData');
        if(!$treeData){
            //>>1.找到所有的商品分类
            $rows = $this->field('id,name,parent_id')->where(array('status'=>1))->select();
            //>>2.将商品分类组装为 树结构的数据
            $treeData = $this->getChildren($rows,0);
            //>>3.去掉顶级分类
            $treeData = $treeData[0]['children'];

            //将数据放到缓存中,为了下次使用..
            S('GoodsCategoryTreeData',$treeData);
        }

        return  $treeData;
    }

    /**
     * 从$rows中找到 parent_id 为指定值得子分类
     * @param $rows
     * @param int $parent_id
     */
    private function getChildren(&$rows,$parent_id){
            //static的变量,在内存中只有一份
            $treeData = array();
            foreach($rows as $row){
                if($row['parent_id']==$parent_id){
                    //让自己作为父分类找自己的子分类
                    $row['children'] = $this->getChildren($rows,$row['id']);
                    $treeData[] = $row;
                }
            }

        return $treeData;
    }



    /**
     * 根据分类找到自己以及父分类
     * @param $goods_category_id
     */
    public function getParent($goods_category_id){
        $sql = "select parent.id,parent.name from goods_category as parent,goods_category as node
where parent.lft<=node.lft and parent.rght>=node.rght and parent.level<>1 and node.id = {$goods_category_id} order by node.lft";

        return $this->query($sql);
    }


}