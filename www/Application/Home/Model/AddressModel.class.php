<?php

namespace Home\Model;


use Think\Model;

class AddressModel extends Model
{
    protected $_auto = array(
        array('member_id',UID)
    );

    /**
     * 查询出当前用户的收货地址信息
     */
    public function getListData(){
        /**
         * select obj.*,province.name as province_name,city.name as city_name,area.name as area_name from address as obj
        join region as province on obj.province_id=province.region_code
        join region as city on obj.city_id=city.region_code
        join region as area on obj.area_id=area.region_code  where obj.member_id =
         */

        $this->alias('obj');
        $this->field("obj.*,province.name as province_name,city.name as city_name,area.name as area_name");
        $this->join('__REGION__ as province on obj.province_id=province.region_code');
        $this->join('__REGION__ as city on obj.city_id=city.region_code');
        $this->join('__REGION__ as area on obj.area_id=area.region_code');
        $this->where(array('obj.member_id'=>UID));

        return $this->select();
    }




    public function add(){
        $this->startTrans();


        //>>1.检查用户提交过来的数据如果为默认值, 将其他的默认去掉
        if(isset($this->data['is_default'])){
            $result = $this->where(array('member_id'=>UID))->setField('is_default',0);
            if($result===false){
                $this->rollback();
                return false;
            }
        }


        if(!empty($this->data['id'])){
            $id = $this->data['id'];
            //>>1.更新
           $result =  parent::save();  //使用this->data中的数据进行更新
           if($result===false){
               $this->rollback();
               return false;
           }
        }else{
            //>>2.保存
            $id = parent::add();  //
            if($id===false){
                $this->rollback();
                return false;
            }
        }
        $this->commit();

        //>>3.根据id再从数据库中获取一行数据
        $row = $this->find($id);



        //>>4.返回这一行数据
        return $row;
    }


    /**
     * 根据id查询出一行数据
     * @param array|mixed $id
     * @return mixed
     */
    public function find($id){
        $this->alias('obj');
        $this->field("obj.*,province.name as province_name,city.name as city_name,area.name as area_name");
        $this->join('__REGION__ as province on obj.province_id=province.region_code');
        $this->join('__REGION__ as city on obj.city_id=city.region_code');
        $this->join('__REGION__ as area on obj.area_id=area.region_code');
        $this->where(array('obj.id'=>$id));

        return parent::find();
    }


}