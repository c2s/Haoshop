<?php
namespace Home\Controller;
use Home\Service\StockAndPriceService;
use Think\Controller;
class IndexController extends Controller {

    /**
     * 该方法被构造函数调用. 下面的方法执行时都执行之前都执行该方法..
     */
    public function _initialize(){
        //>>1.准备所有的商品分类数据
        $goodsCategoryModel = D('GoodsCategory');
        $goodsCategoryTreeData = $goodsCategoryModel->getGoodsCategoryTreeData();
        $this->assign('goodsCategoryTreeData',$goodsCategoryTreeData);


        //>>3.查询出帮助类的文章分类
        $articleCategoryModel = D('ArticleCategory');
        $helpArticleCategoryes = $articleCategoryModel->getHelpArticleCategory();
        $this->assign('helpArticleCategoryes',$helpArticleCategoryes);

        //>>4.查询出帮助类的文章内容
        $articleModel  = D('Article');
        $help_newses = $articleModel->getNews(30,1);
        $this->assign('help_newses',$help_newses);



    }

    public function index(){
        //>>1.showgoodsCategory有值的情况下,分类导航展开
        $this->assign('showGoodsCategory',true);
        $this->assign('meta_title','京西商场首页');


        //>>2.向首页分配帮助类的信息内容
        $articleModel  = D('Article');
        $newses = $articleModel->getNews();
        $this->assign('newses',$newses);

        //>>3.根据状态获取商品列表
        $goodsModel = D('Goods');
        //>>3.1 得到疯狂抢购
        $goods_1 = $goodsModel->getGoodsByGoodsStatus(1);
        //>>3.2 热卖商品
        $goods_2 = $goodsModel->getGoodsByGoodsStatus(2);
        //>>3.3 推荐商品
        $goods_4 = $goodsModel->getGoodsByGoodsStatus(4);
        //>>3.4 新品上架
        $goods_8 = $goodsModel->getGoodsByGoodsStatus(8);
        //>>3.5 猜你喜欢
        $goods_16 = $goodsModel->getGoodsByGoodsStatus(16);

        //通过关联数组的方式分配数据到页面上
        $this->assign(array(
            'goods_1'=>$goods_1,
            'goods_2'=>$goods_2,
            'goods_4'=>$goods_4,
            'goods_8'=>$goods_8,
            'goods_16'=>$goods_16,
        ));









        $this->display('index');
    }
    public function lst(){
        $this->assign('meta_title','京西商场  商品列表');

        $this->display('list');
    }


    /**
     * 展示一个商品的详细
     */
    public function goods($id){
//        $goods = S('goods_'.$id);  //goods_xxx : xxxx是id的值
//        if(!$goods){  //如果不在执行下面的代码进行查询..

                //>>1.根据id查询出当前商品的基本信息
                $goodsModel = D('Goods');
                $goods = $goodsModel->find($id);


                //>>2.查询出商品的简介内容... goods_intro
                $info = M('GoodsIntro')->getFieldByGoods_id($id,'intro');
                $goods['content'] = $info;


                //>>3.查询出相册中的数据.. 只不过查询出的第一张图片是该商品的logo
                //>>3.1 先查询出当前商品对应的相册数据
                $gallerys = D('GoodsGallery')->getGalleryPath($id);
                //>>3.2 将logo放到相册的第一张图片上
                array_unshift($gallerys,$goods['logo']);

                $goods['gallerys'] = $gallerys;



                //>>4. 根据商品的分类id找到他的父分类
                $goodsCategoryModel = D('GoodsCategory');
                $parentGoodsCategorys = $goodsCategoryModel->getParent($goods['goods_category_id']);
                $goods['parentGoodsCategorys'] =$parentGoodsCategorys;

                //>>5. 查询出当前商品的属性
                if($goods['stock_type']==2){
                    $goods['goodsAttributes'] = $goodsModel->getGoodsAttribute($id);
                }

            //再将当前商品缓存起来
//            S('goods_'.$id,$goods);


//        }

        $this->assign($goods);
        $this->assign('meta_title','商品列表....'.$goods['name']);


        /**
         * 使用php的redis扩展来操作redis数据库中.
         */
        /**
         * 1.连接到redis中
         */
        $redis = getRedis();
        /**
         * 2.操作redis
         */
        $goods_clicknum = $redis->zIncrBy('goods_clicknum',1,'goods_views:'.$id);


        $this->display('goods');
    }


    /**
     * 当前商品:
     *  单库存单价格===>goods(stock,shop_price)
     *  多库存多价格===>product()
     * @param $goods_id
     */
    public function getStockAndPrice($goods_id){
            $stockAndPriceService = D('StockAndPrice','Service');
            $stockAndPrice = $stockAndPriceService->getStockAndPrice($goods_id);
            /**
             * $stockAndPrice=>{
                 stock,
             *   shop_price
             * }
             */
            $this->ajaxReturn($stockAndPrice);
    }

}