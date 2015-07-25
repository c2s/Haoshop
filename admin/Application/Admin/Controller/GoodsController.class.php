<?php
namespace Admin\Controller;


use Think\Controller;


class GoodsController extends BaseController{
    protected $meta_title = '商品表';

    /**
     * 是否使用请求中所有数据给Model中的add或者save方法
     * @var bool
     */
    protected $usePostParams = true;


    protected function _edit_view_before(){
        //>>1.为页面提供分类数据
        $goodsCategoryModel = D('GoodsCategory');
        $goodsCategoryes = $goodsCategoryModel->getListData(false);
        $this->assign('goodsCategoryes',json_encode($goodsCategoryes));
        //>>2.为页面提供品牌数据
        $brandModel = D('Brand');
        $brands = $brandModel->getListData();
        $this->assign('brands',$brands);
        //>>3.为页面提供供应商数据
        $supplierModel = D('Supplier');
        $suppliers = $supplierModel->getListData();
        $this->assign('suppliers',$suppliers);



     /*   $id = I('get.id');
        if(!empty($id)){
            //表示编辑
            //需要根据id从goods_intro表中获取一行intro
           $intro  =  M('GoodsIntro')->getFieldByGoods_id($id,'intro');   //select intro from goods_intro where goods_id = $id;
            $this->assign('intro',$intro);
        }*/


        //>>4.查询出所有的会员级别
        $memberLevelModel = D('MemberLevel');
        $memberLevels = $memberLevelModel->getListData();
        $this->assign('memberLevels',$memberLevels);

        //>>5.准备好商品类型的数据
        $goodsTypeModel = D('GoodsType');
        $goodsTypes = $goodsTypeModel->getListData();
        $this->assign('goodsTypes',$goodsTypes);

        $id = I('get.id');
        if(!empty($id)){

            //>>1.查询出当前商品的类型下的所有属性, 展示属性表单
            $attributeModel = D('Attribute');
            $attributesJSON = $attributeModel->getListByGoodsId($id);
            $this->assign('attributeJSON',$attributesJSON);

            //>>2.查询出当前商品的属性值, 为了回显 属性表单中的值
            $goodsAttributeModel  = D('GoodsAttribute');
            $goodsAttributes = $goodsAttributeModel->getListByGoodsId($id);
            $this->assign('goodsAttributes',$goodsAttributes);


        }
    }


    /**
     * 覆盖父类中的方法. 使用$_POST['intro']接收简介中的内容
     */
    public function add()
    {
        if (IS_POST) {
            if ($this->model->create() !== false) {

                $requestData = I('post.');
                //防止I()将intro转义
                $requestData['intro']  = $_POST['intro'];

                if ($this->model->add($requestData) !== false) {
                    $this->success('添加成功!', cookie('__forward__'));
                    return;
                }
            }
            $this->error('添加失败!' . showModelError($this->model));
        } else {
            $this->assign('meta_title','添加'.$this->meta_title);
            $this->_edit_view_before();  //如果子类覆盖_edit_view_before时, 就是调用子类的该方法..
            $this->display('edit');
        }
    }



    public function edit($id)
    {
        if (IS_POST) {
            if ($this->model->create() !== false) {
                $requestData = I('post.');
                //防止I()将intro转义
                $requestData['intro']  = $_POST['intro'];
                if ($this->model->save($requestData) !== false) {
                    $this->success('更新成功!', cookie('__forward__'));
                    return;
                }
            }
            $this->error('更新失败!' . showModelError($this->model));
        } else {
            $row = $this->model->find($id);
            $this->assign($row);
            $this->_edit_view_before();
            $this->assign('meta_title','编辑'.$this->meta_title);
            $this->display('edit');
        }
    }


    public function deleteGallery($gallery_id){
        $goodsGalleryModel = M('GoodsGallery');
        if($goodsGalleryModel->delete($gallery_id)!==false){
            $this->ajaxReturn(array('success'=>true));
        }else{
            $this->ajaxReturn(array('success'=>false));
        }
    }


    public function searchArticle($keyword){
        $articleModel  = D('Article');
        //根据关键字搜索出内容
        $rows = $articleModel->searchByKeyworkd($keyword);
        //将内容以json的方式发送给浏览器
        $this->ajaxReturn($rows);
    }

}