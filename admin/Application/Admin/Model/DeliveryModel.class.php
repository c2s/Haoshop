<?php
namespace Admin\Model;


use Think\Model;


class DeliveryModel extends BaseModel{

    protected $_validate = array(
        array('name','require','送货方式名称不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
array('price','require','价格不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
array('is_default','require','是否默认不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),

    );
}