<?php
namespace Admin\Model;


use Think\Model;


class ArticleCategoryModel extends BaseModel{

    protected $_validate = array(
        array('name','require','分类名称不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
array('is_help','require','是否为帮助的分类不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
array('status','require','状态不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
array('sort','require','排序不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),

    );
}