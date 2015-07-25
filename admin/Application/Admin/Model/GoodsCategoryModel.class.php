<?php
namespace Admin\Model;


use Admin\Service\NestedSetsService;
use Think\Model;


class GoodsCategoryModel extends BaseModel{

    protected $_validate = array(
        array('name','require','分类名称不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
        array('name','','名称已经存在,请更换!',self::EXISTS_VALIDATE,'unique',self::MODEL_BOTH),
        array('parent_id','require','父分类不能为空!',self::EXISTS_VALIDATE,'',self::MODEL_BOTH),
    );



    public function add(){
        //>>1. 开启事务
        $this->startTrans();
        //>>保存到数据库的同时还需要移动其他的节点..
        $dbMysqlInterfaceImplModel = new DbMysqlInterfaceImplModel();
        $nestedSetsService = new NestedSetsService($dbMysqlInterfaceImplModel,'goods_category','lft','rght','parent_id','id','level');
        //insert的业务只是用来生成sql语句
        $result = $nestedSetsService->insert($this->data['parent_id'],$this->data,'bottom');
        if($result===false){
            //回滚事务
            $this->rollback();
            return false;
        }
        $this->commit();
        return $result;  //提交事务
    }


    public function save(){
        $this->startTrans();
        //需要将编辑回显的页面中的数据更新到当前行中, 并且要移动节点...(就是更改边界)
        $dbMysqlInterfaceImplModel = new DbMysqlInterfaceImplModel();
        $nestedSetsService = new NestedSetsService($dbMysqlInterfaceImplModel,'goods_category','lft','rght','parent_id','id','level');

        //移动
        $result = $nestedSetsService->moveUnder($this->data['id'],$this->data['parent_id']);
        if($result===false){
            $this->rollback();
            $this->error = '不能够选中自己或者是子分类作为父分类!';
            return false;
        }
        //更新表单中的内容
        if(parent::save()===false){
            $this->rollback();
            return false;
        }
        return $this->commit();
    }



    /**
     * 查询树的数据
     * @param $include_root 是否包含顶级分类
     * @return mixed
     */
    public function getListData($include_root)
    {
        $wheres = array();
        if(!$include_root){
            $wheres['level'] = array('NEQ',1);
        }
        return $this->field('id,name,parent_id,status,intro,level')->where($wheres)->order('lft')->where("status>-1")->select();
    }


    public function changeStatus($id,$status=-1){
        if($status==-1){
            $sql = "update goods_category as children,goods_category as parent set children.status = $status,children.name=concat(children.name,'_del')  where children.lft>=parent.lft and children.rght<=parent.rght and parent.id = $id";
        }else{
            $sql = "update goods_category as children,goods_category as parent set children.status = $status  where children.lft>=parent.lft and children.rght<=parent.rght and parent.id = $id and children.status>-1";
        }
//        $sql = "select children.id  from goods_category as children,goods_category as parent where children.lft>=parent.lft and children.rght<=parent.rght and parent.id = 8";
//        $childrenIds = array(1,2,3,4,5);
//        $this->where(array('id',array('in',$childrenIds)))->save(array('status'=>-1,'name'=>array('exp','concat(name,"_del"")')));
        return $this->execute($sql);
    }

}