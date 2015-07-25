<?php
namespace Admin\Controller;


use Think\Controller;


class ArticleController extends BaseController{
    protected $meta_title = '新闻';


    protected function _edit_view_before(){
        //查询所有的文章分类
        $articleCategoryModel = D('ArticleCategory');
        $articleCategoryes = $articleCategoryModel->getListData();
        $this->assign('articleCategoryes',$articleCategoryes);
    }


    public function edit($id)
    {
        if (IS_POST) {
            if ($this->model->create() !== false) {
                $requestData = I('post.');
                $requestData['content'] = $_POST['content'];
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

    public function add()
    {
        if (IS_POST) {
            if ($this->model->create() !== false) {
                $requestData = I('post.');
                $requestData['content'] = $_POST['content'];
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

}