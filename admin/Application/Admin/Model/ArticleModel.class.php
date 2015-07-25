<?php
namespace Admin\Model;


use Think\Model;


class ArticleModel extends BaseModel{

    protected $_validate = array(
        array('name','require','新闻名称不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('article_category_id','require','不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('status','require','状态不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('sort','require','排序不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('inputtime','require','添加时间不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('clicknum','require','不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),

    );

    protected $_auto = array(
        array('inputtime',NOW_TIME)
    );



    public function add($requestData){
        //>>1.将请求数据(除了内容)保存到article表中
        $id = parent::add();
        if($id===false){
            return false;
        }
        //>>2.将内容保存到article_content表中
         $articleContent =  array('article_id'=>$id,'content'=>$requestData['content']);
         $result = M('ArticleContent')->add($articleContent);
         if($result===false){
             return false;
         }
        return $id;
    }


    public function find($id){
        $row = parent::find($id);
        if($row){
            $content = M('ArticleContent')->getFieldByArticle_id($id,'content');
            $row['content'] = $content;
        }
        return $row;
    }

    public function save($requestData){
        //>>1.将请求数据(除了内容)保存到article表中
        $result = parent::save();
        if($result===false){
            return false;
        }
        //>>2.将内容保存到article_content表中
        $result = M('ArticleContent')->where(array('article_id'=>$requestData['id']))->setField('content',$requestData['content']);
        if($result===false){
            return false;
        }
        return $result;
    }

    public function searchByKeyworkd($keyword){
        $wheres = array();
        $wheres['name']= array('LIKE',"%$keyword%");
        $wheres['status']=array('GT',-1);
        return $this->field('id,name')->where($wheres)->select();
    }
}