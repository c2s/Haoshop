<?php


namespace Admin\Model;


use Think\Model;


class BrandModel extends BaseModel{

    protected $_validate = array(
        array('name','require','品牌名称不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('url','require','品牌网址不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
    );

}