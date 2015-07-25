<?php
namespace Admin\Model;


use Think\Model;


class GoodsModel extends BaseModel{

    protected $_validate_1 = array(
        array('name','require','属性名称不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('logo','require','LOGO不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('goods_category_id','require','分类不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('supplier_id','require','供货商不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('brand_id','require','品牌不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('market_price','require','市场价不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('shop_price','require','本店价格不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('is_on_sale','require','是否上架不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('goods_type_id','require','商品类型不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('goods_status','require','商品状态不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('status','require','状态不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('sort','require','排序不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
    );


    protected  $_auto = array(
        array('inputtime',NOW_TIME),
        array('admin_id',UID),
    );


    public function add($requestData){
        $this->startTrans();

//        $this->data; 是create收集的数据
//        $requestData; 是请求中的所有数据
        //>>1.处理商品的状态
        $this->handlerGoodsStatus();
        //>>2.调用父类中的add方法将this->data(create收集到的请求数据) 保存到数据库中
        $id = parent::add();
        if($id===false){
            $this->rollback();
            return false;
        }
        //>>3.把商品描述的数据保存到goods_intro表中
        $result = $this->handleGoodsIntro($requestData,$id);
        if($result===false){
            return false;
        }

        //>>4.处理会员价格
        $result = $this->handleGoodsMemberPrice($requestData,$id);
        if($result===false){
            return false;
        }
        //>>5.处理商品相册
        $result = $this->handleGoodsGallery($requestData,$id);
        if($result===false){
            return false;
        }
        //>>6.处理商品相关文章
        $result = $this->handleGoodsArticle($requestData,$id);
        if($result===false){
            return false;
        }

        //>>7.处理商品属性
        $result = $this->handleGoodsAttribute($requestData,$id);
        if($result===false){
            return false;
        }

        $this->commit();
        return $id;
    }

    /**
     * 处理商品属性
     * @param $requestData
     * @param $id
     */
    private function handleGoodsAttribute($requestData,$id){
       $attributes =  $requestData['attribute'];

       $rows = array();
       foreach($attributes as $attribute_id=>$value){
            if(is_array($value)){
                //处理单值属性(因为一个属性对应多个值,需要保存到多行记录中)
                foreach($value as $v){
                    $rows[] = array('goods_id'=>$id,'attribute_id'=>$attribute_id,'value'=>$v);
                }
            }else{
                //处理单值的属性
                $rows[] = array('goods_id'=>$id,'attribute_id'=>$attribute_id,'value'=>$value);
            }
       }

        if(!empty($rows)){
            //将商品属性保存
            $result = M('GoodsAttribute')->addAll($rows);
            if($result===false){
                $this->rollback();
                $this->error = '保持属性失败!';
                return false;
            }
        }
    }



    /**
     * 处理商品相关的文章
     * @param $requestData
     * @param $id
     */
    private function handleGoodsArticle($requestData,$id){
        $article_ids = $requestData['article_id'];
        $rows = array();
        foreach($article_ids as $article_id){
             $rows[] = array('goods_id'=>$id,'article_id'=>$article_id);
        }
        if(!empty($rows)){
            $goodsArticleModel = M('GoodsArticle');
            //先删除
            $goodsArticleModel->where(array('goods_id'=>$id))->delete();
            //再添加
            $result = $goodsArticleModel->addAll($rows);
            if($result===false){
                $this->rollback();
                return false;
            }
        }
    }


    private function handleGoodsGallery($requestData,$id){
        //>>1.得到请求中的新的图片
        $gallerys =  $requestData['gallery'];
        if(!empty($gallerys)){
                $rows = array();
                foreach($gallerys as $path){
                    $rows[] = array('goods_id'=>$id,'path'=>$path);
                }
            //2.保存到goods_gallery表中
            $result = M('GoodsGallery')->addAll($rows);
            if($result===false){
                $this->rollback();
                return false;
            }
        }
    }

    private function handleGoodsMemberPrice($requestData,$id){
        //删除当前商品的会员价格
        $goodsMemberModel = M('GoodsMemberPrice');

        $goodsMemberModel->where(array('goods_id'=>$id))->delete();

        /**
         *  ["goodsMemberPrice"] => array(3) {
            [1] => string(2) "30"
            [2] => string(2) "20"
            [3] => string(2) "10"
        }
         */
        //>>1.从请求参数中获取会员价格
        $goodsMemberPrices = $requestData['goodsMemberPrice'];
        $rows = array();
        foreach($goodsMemberPrices as $member_level_id=>$price){
            //当填写了price并且是数字
            if($price && is_numeric($price)){
                $rows[] = array('member_level_id'=>$member_level_id,'price'=>$price,'goods_id'=>$id);
            }
        }
        if(!empty($rows)){
            //一次性添加所有数据到数据表中
            $result = $goodsMemberModel->addAll($rows);
            if($result===false){
                $this->rollback();
                return false;
            }
        }


    }

    private function handleGoodsIntro($requestData,$id){

        $goodsIntroModel = M('GoodsIntro');
        $goodsIntroModel->delete($id); //删除当前商品对应的描述

        $intro  = $requestData['intro'];
        $result = $goodsIntroModel->add(array('goods_id'=>$id,'intro'=>$intro));
        if($result===false){
            $this->rollback();
            return false;
        }
    }

    /**
     * 处理请求中的商品状态
     */
    private function handlerGoodsStatus(){
        //>>1.商品的初始状态
        $goodsStatus = 0;
        //>>2.通过用户选中的状态计算出 该商品的状态
        foreach($this->data['goods_status'] as $v){
            $goodsStatus = $goodsStatus | $v;
        }
        //>>3.将计算出的结果赋值给goods_status
        $this->data['goods_status'] = $goodsStatus;
    }



    protected function _setModel()
    {
        $this->field('obj.*,gc.name as goods_category_name,s.name as supplier_name,b.name as brand_name');
        //>>1.连接商品分类表: 查询出分类的名字
        $this->join('__GOODS_CATEGORY__ as gc on obj.goods_category_id = gc.id');
        //>>2.连接供货商表,查询出供货商的名称
        $this->join('__SUPPLIER__ as s on  obj.supplier_id = s.id');
        //>>3.连接品牌表,查询出品牌的名称
        $this->join('__BRAND__ as b on  obj.brand_id = b.id');
    }

    /**
     * 单独处理每行数据..
     * @param $rows
     */
    protected function _handlerRows(&$rows)
    {
        foreach($rows as &$row){
            //将一个状态值变成多个状态值方便在页面上显示
            $goods_status = $row['goods_status'];
            $row['is_best'] = $goods_status & 1 ? 1:0;
            $row['is_new'] = $goods_status  & 2 ? 1:0;
            $row['is_hot'] = $goods_status  & 4 ? 1:0;
        }
    }


    /**
     * 覆盖find方法, 增强他的功能...
     * @param array|mixed $id
     * @return mixed
     */
    public function find($id){
        //>>1.获取goods表中的一行数据, 该数据中不包含intro
        $row = parent::find($id);
        if(!$row){
            return $row;
        }
        //>>2.从goods_intro表中查询出intro放到row中..
        $intro  =  M('GoodsIntro')->getFieldByGoods_id($id,'intro');
        $row['intro'] = $intro;


        //>>2.从会员价格表中找到当前商品的会员价格
        $goodsMemberPrices = M('GoodsMemberPrice')->field('member_level_id,price')->where(array('goods_id'=>$id))->select();
        if(!empty($goodsMemberPrices)){

            /**
             * array(3) {
                [0] => array(2) {
                ["member_level_id"] => string(1) "1"
                ["price"] => string(5) "30.00"
                }
                [1] => array(2) {
                ["member_level_id"] => string(1) "2"
                ["price"] => string(5) "20.00"
                }
                [2] => array(2) {
                ["member_level_id"] => string(1) "3"
                ["price"] => string(5) "10.00"
             }
            }
            一个商品的级别id==>对应的价格

            array('1'=>30,'2'=>20,'3'=>30)
             */

            $member_level_ids = array_column($goodsMemberPrices,'member_level_id');
            $prices = array_column($goodsMemberPrices,'price');
            $goodsMemberPrices = array_combine($member_level_ids,$prices);
            /**
             * ["goodsMemberPrices"] => array(3) {
                [1] => string(5) "30.00"
                [2] => string(5) "20.00"
                [3] => string(5) "10.00"
            }
             */
            $row['goodsMemberPrices'] = $goodsMemberPrices;
        }




        //查询出商品的相册
        $goodsGalleryes = M('GoodsGallery')->field('id,path')->where(array('goods_id'=>$id))->select();
        $row['goodsGalleryes'] = $goodsGalleryes;


        //找到当前商品对应的文章
        $goodsArticleModel = M('GoodsArticle');
        $goodsArticleModel->field('ga.article_id,a.name as article_name');
        $goodsArticleModel->alias('ga');
        $goodsArticleModel->where(' ga.goods_id = '.$id);
        $articles = $goodsArticleModel->join('__ARTICLE__ as a on ga.article_id=a.id')->select();
        $row['articles']  =$articles;

        return $row;
    }


    /**
     * 接受请求中的所有数据更新到goods和goods_intro表中
     * @param mixed|string $requestData
     */
    public function save($requestData){
        $this->startTrans();
        $id = $requestData['id'];//编辑商品的id
        /**
         * $this->data; create收集到的当前表中需要的数据
         * requestData请求中的所有数据
         */
        //>>1.将请求数据更新到goods表中
        $this->handlerGoodsStatus();
        $result = parent::save();
        if($result===false){
            $this->rollback();
            return false;
        }
        //>>2.将intro的数据更新到goods_intro表中
        $result = $this->handleGoodsIntro($requestData,$id);
        if($result===false){
            return false;
        }
        //>>3.处理会员价格
        $result = $this->handleGoodsMemberPrice($requestData,$id);
        if($result===false){
            return false;
        }

        //>>4.处理商品相册
        $result = $this->handleGoodsGallery($requestData,$id);
        if($result===false){
            return false;
        }
        //>>5.处理商品文章
        $result = $this->handleGoodsArticle($requestData,$id);
        if($result===false){
            return false;
        }

        //>>6.处理商品属性值的更新
        $this->handleUpdateGoodsAttribute($requestData,$id);

        //>>3.提交事务
       return  $this->commit();
    }


    /**
     * 修改商品属性值
     *
     * 单值属性:
     *    根据商品goods_id和属性id(attribute_id) 将表单中的值更新到value上
     *
     * 多值属性:
     *   请求中有,数据库中没有, 需要添加到数据库中
     *   请求中有,数据库中也有, 不需要处理
     *   请求中有,数据库中没有, 需要将其删除
     *   请求中没有,数据库中没有, 不需要处理
     *
     */
    private function handleUpdateGoodsAttribute($requestData,$goods_id){
        $goodsAttributeModel = D('GoodsAttribute'); //操作商品属性表
        //>>请求中的属性值
        $requestAttribute = $requestData['attribute'];

        foreach($requestAttribute as $attribute_id=>$requestValue){
            if(!is_array($requestValue)){
                    //>>1.处理单值属性
                    $result = $goodsAttributeModel->where(array('goods_id'=>$goods_id,'attribute_id'=>$attribute_id))->setField('value',$requestValue);
                    if($result===false){
                        $this->rollback();
                        $this->error = '更新单值属性出错!';
                        return false;
                    }
            }else{
                //>>2.处理多值属性
                    //对比的前提是找出当前商品属性的多值属性
                    $goodsAttributeInDB = $goodsAttributeModel->getMultValueGoodsAttribute($goods_id);
                /**
                 * array(1) {
                        [2] => array(2) { //attribute_id
                            [0] => string(6) "红色"
                            [1] => string(6) "黑色"
                }
                 */
                //>>2.1 请求中有,数据库中没有, 需要添加到数据库中
                        //使用请求中的数据对应数据库中的数据库,如果没有,就添加到数据库中

                        //>>找到一个属性在数据库中对应的值
                        $goodsAttributeValueInDB = $goodsAttributeInDB[$attribute_id];
                        foreach($requestValue as $v){
                            //判断请求中的值是否在数据库中存在
                            if(!in_array($v, $goodsAttributeValueInDB)){
                                //不存在添加
                                 $result = $goodsAttributeModel->add(array('goods_id'=>$goods_id,'attribute_id'=>$attribute_id,'value'=>$v));
                                if($result===false){
                                    $this->rollback();
                                    $this->error = '更新多值属性出错!';
                                    return false;
                                }
                            }
                        }
                //>>2.1 数据库中有,请求中没有, 需要从数据库中删除该商品的属性对应的值
                        //>>1.循环数据库中的属性以及值
                     foreach($goodsAttributeInDB as $attribute_id => $dbValue){
                            //>>2.根据数据库中的属性id找到请求中对应的值
                            $requestValue = $requestAttribute[$attribute_id];
                            foreach($dbValue as $v){
                                //判断数据库中的值是否在请求中
                                if(!in_array($v,$requestValue)){
                                    //如果不在请求中,将数据库中的删除
                                   $result =  $goodsAttributeModel->where(array('goods_id'=>$goods_id,'attribute_id'=>$attribute_id,'value'=>$v))->delete();
                                    if($result===false){
                                        $this->error = '删除多值属性失败!';
                                        $this->rollback();
                                        return false;
                                    }
                                }
                            }
                     }


            }
        }

    }

}