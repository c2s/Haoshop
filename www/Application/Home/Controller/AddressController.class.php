<?php

namespace Home\Controller;


use Think\Controller;

class AddressController extends CheckLoginController
{

    public function index(){
        $addressModel  = D('Address');
        if(IS_POST){
            if($addressModel->create()!==false){

                //添加完成之后返回这一行的数据
                if(($row= $addressModel->add())!==false){
                    //将这一行的数据以json的方式发送给浏览器
                    $this->ajaxReturn($row);
                }
            }
        }else{
            //>>1.准备省份的数据
            $regionModel = D('Region');
            $provinces = $regionModel->getChildren(0);  //当parent_id=0的时候是省份数据
            //>>2.分配到页面上
            $this->assign('provinces',$provinces);


            //>>3.将当前登录用户的收货人信息查询出来,再分配到页面
            $rows = $addressModel->getListData();
            $this->assign('rows',$rows);


            $this->display('index');
        }
    }

    public function remove($id){
        $addressModel = D('Address');
        $result = $addressModel->delete($id);
        if($result!==false){
            $this->ajaxReturn(array('success'=>true));
        }
    }
    public function setDefault($id){
        $addressModel = D('Address');
        //>>1.将自己的所有收货地址设置为非默认
        $addressModel->where(array('member_id'=>UID))->setField('is_default',0);
        //>>2.在讲当前id对应的收货地址设置为默认
        $addressModel->where(array('id'=>$id))->setField('is_default',1);

        $this->ajaxReturn(array('success'=>true));
    }



    public function edit($id){
        $addressModel = D('Address');
        $row = $addressModel->find($id);
        $this->ajaxReturn($row);
    }
}