<?php

namespace Home\Model;


use Think\Model;

class ArticleCategoryModel extends Model{

    public function getHelpArticleCategory(){
        $sql = "select id,name from article_category where is_help = 1";
        $rows=  $this->query($sql);
        return $rows;
    }
}