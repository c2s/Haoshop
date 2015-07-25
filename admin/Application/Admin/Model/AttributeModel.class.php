<?php
namespace Admin\Model;


use Think\Model;
use Think\Page;


class AttributeModel extends BaseModel{

    protected $_validate = array(
        array('name','require','属性名称不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('goods_type_id','require','不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('attribute_type','require','类型不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('input_type','require','录入方式不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('status','require','状态不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('sort','require','排序不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
    );

    /**
     * 多列表数据进一步处理
     * @param $rows
     */
    protected function _handlerRows(&$rows)
    {
        foreach($rows as &$row){
            //多类型进行处理
            switch($row['attribute_type']){
                case 1:
                    $row['attribute_type'] = '单值';break;
                case 2:
                    $row['attribute_type'] = '多值';break;
            }

            //对录入方式进行处理
            switch($row['input_type']){
                case 1:
                    $row['input_type'] = '手工录入';break;
                case 2:
                    $row['input_type'] = '从下面列表中选择';break;
                case 3:
                    $row['input_type'] = '多行文本';break;
            }
        }
    }

    /**
     * 为父类的model添加查询内容和连接
     */
    protected function _setModel()
    {
        $this->field('obj.*,gt.name as goods_type_name');
        $this->join('__GOODS_TYPE__  as gt on obj.goods_type_id=gt.id');
    }


    public function getListByGoodsTypeId($goods_type_id){
        $rows  =  $this->field('id,name,input_type,attribute_type,option_values')->where("goods_type_id=$goods_type_id and status>-1")->select();
        //如果说每个属性上有option_values属性,将其变为数组
        $this->hanlderRow($rows);
        return $rows;
    }

    private function hanlderRow(&$rows){
          foreach($rows as &$row){
                    if(!empty($row['option_values'])){
                        $row['option_values'] = str2arr($row['option_values'],"\r\n");
           }
        }
    }

    /**
     * 根据商品的id找到该商品类型下的属性
     * @param $goods_id
     */
    public function getListByGoodsId($goods_id){
        $sql = "select  a.id,a.name,a.attribute_type,a.input_type,a.option_values from attribute as a join goods as g  on  a.goods_type_id =g.goods_type_id   where a.status>0 and  g.id = $goods_id";
        $rows = $this->query($sql);
        $this->hanlderRow($rows);
        return json_encode($rows);
    }

}