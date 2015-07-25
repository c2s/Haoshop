<?php

namespace Home\Model;


use Think\Model;

class ArticleModel extends Model{

    /**
     * 得到指定条数的快报内容
     */
    public function getNews($num=8,$is_help=0){
//        $sql  = "select a.id,a.name from article as a join article_category as ac on a.article_category_id=ac.id where ac.is_help = 0 limit $num";

        $this->field('a.id,a.name,a.article_category_id');
        $this->alias('a');
        $this->join('__ARTICLE_CATEGORY__  as ac on a.article_category_id=ac.id');
        $this->where(array('ac.is_help'=>$is_help));
        $this->limit($num);
        $this->order('a.inputtime desc');
        $rows = $this->select();

        return $rows;
    }

}