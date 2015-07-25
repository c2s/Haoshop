<?php

namespace Home\Model;


use Org\Util\String;
use Think\Model;

class MemberModel extends Model{


    protected $_auto = array(
        array('salt',"\Org\Util\String::randString",self::MODEL_INSERT,'function'),
        array('status',2),//2表示未激活
    );

    public function add(){
        $this->data['password'] = mymd5($this->data['password'],$this->data['salt']);
        $email = $this->data['email'];
        //保存
        $id = parent::add();
        if($id!==false){
            //>>1.发送激活邮件...
            /*
             * 第一种方式

            $uid = String::uuid();  //保持到数据库中
            $fireurl = "http://www.shop.com/index.php/Member/fire/key/$uid";
             */
            /*
             * 第二种方式
             */
            $key = mymd5($id,"谁破解是牛逼");
            $fireurl = "http://www.shop.com/index.php/Member/fire/id/{$id}/key/$key";

            $content = <<<MAILCONTENT
            <h1>xxx激活邮件</h1>
            <p>点击<a href='{$fireurl}' target='_blank'>这里</a></p>
            <p>请将下面的地址复制到浏览器上</p>
            <p>{$fireurl}<p>
MAILCONTENT;
            sendMail($email,'xxx激活邮件',$content);

        }
    }


    /**
     * 注册时验证..
     * @param $param
     * @return bool
     */
    public function checkField($param){
        foreach($param as $k=>$v){
            $count = $this->where(array($k=>$v))->count();
            return $count==0; //true:表示没有当前用户
        }
    }

    public function fire($id,$requestkey){
        $key = mymd5($id,"谁破解是牛逼");
        if($key==$requestkey){
            $this->where(array('id'=>$id));
            return $this->setField('status',1);//激活
        }else{
            $this->error = '验证错误,不能够激活!';
            return false;
        }
    }


    public function login(){
       $username = $this->data['username'];
       $password = $this->data['password'];

       $row = $this->field('id,username,status,password,salt,total_score')->where(array('username'=>$username))->find();
       if($row){
           switch($row['status']){
               case 2:
                   $this->error = '未激活!'; return false;
               case 0:
               case -1:
                   $this->error = '锁定或删除!';return false;
           }

           if($row['password'] == mymd5($password,$row['salt']) ){
                 //得到用户的会员价格和折扣

                   $memberLevel =  $this->getMemberLevel($row['total_score']);
                   $row  = array_merge($row,$memberLevel);

                return $row;
           }else{
               $this->error = '密码错误!';
               return false;
           }
       }else{
           $this->error = '用户名不存在!';
           return false;
       }

    }


    /**
     * 根据积分得到当前会员的级别和折扣
     * @param $score
     */
    private function getMemberLevel($score){
        $sql = "select id as member_level_id,discount from member_level  where {$score} BETWEEN  low and  high limit 1";
        $rows  = $this->query($sql);
        $row = $rows[0];
        $row['discount'] = $row['discount']/100;
        return $row;
    }
}