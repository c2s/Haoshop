<?php

namespace Home\Model;


use Think\Model;

class OrderInfoModel extends Model
{

    /**
     * 将请求数据保持到数据库表中order_info表中
     * @param mixed|string $requestData
     */
    public function add($requestData){
        $this->startTrans();

        //>>1.通过请求数据为order_info表中准备数据

            //>>1.1 准备订单的用户
            $orderInfo = array();
            $orderInfo['member_id'] = UID;

            //>>1.2 订单的总价格 (购物车中的总价格+运费)
            $price = 0;

            $shoppingCarModel = D('ShoppingCar');
            $shoppingCar = $shoppingCarModel->getListData();
            foreach($shoppingCar as $shoppingCar_item){
                $price+=($shoppingCar_item['num'] * $shoppingCar_item['shop_price']);
            }



          ////////////////////////判断购物车中的商品在库存中是否存在,并且减少库存  开始////////////////////////////
        //>>1.打开一个文件.让文件作为锁
        $file = fopen('./xxx.lock','r');
        //>>2.加锁
        if(flock($file,LOCK_EX)):
            foreach($shoppingCar as $shoppingCar_item){
                        //>>1.判断单库存单价格的库存(goods表中stock)
                    if(empty($shoppingCar_item['goods_attribute_ids'])){
                            $wheres = "id={$shoppingCar_item['goods_id']} and stock>={$shoppingCar_item['num']}";
                            $goodsModel = M('Goods');
                            $row = $goodsModel->field('id')->where($wheres)->find();

                            if($row){  //如果查询出来说明有库存,需要减少库存
                                $result = $goodsModel->where($wheres)->setDec('stock',$shoppingCar_item['num']);
                                if($result===false){
                                    $this->error = '减少库存失败!';
                                    $this->rollback();
                                    return false;
                                }
                            }else{
                                $this->error = '库存不足!';
                                $this->rollback();
                                return false;
                            }
                    }else{
                        //>>2.判断多库存多价格的库存(product表中stock字段上)
                        $productModel = M('product');
                        $wheres = "goods_id={$shoppingCar_item['goods_id']}  and goods_attribute_ids='{$shoppingCar_item['goods_attribute_ids']}' and  stock>={$shoppingCar_item['num']}";
                        $row  = $productModel->where($wheres)->find();
                        if($row){  //如果查询出来说明有库存,需要减少库存
                            $result = $productModel->where($wheres)->setDec('stock',$shoppingCar_item['num']);
                            if($result===false){
                                $this->error = '减少库存失败!';
                                $this->rollback();
                                return false;
                            }
                        }else{
                            $this->error = '库存不足!';
                            $this->rollback();
                            return false;
                        }
                    }
            }

        flock($file,LOCK_UN);
        endIf;
        fclose($file);
          ////////////////////////判断购物车中的商品在库存中是否存在,并且减少库存  接收////////////////////////////

                //根据送货方式的id,得到价格
                $deliveryModel = M('Delivery');
                $deliveryPrice = $deliveryModel->getFieldById($requestData['delivery_id'],'price');

            $orderInfo['price'] = ($price+$deliveryPrice);  //包含商品价格和运费

            $orderInfo['add_time'] = NOW_TIME; //添加时间
            $orderInfo['delivery_price'] = $deliveryPrice; //运费

             //>>1.3 收货地址
                $addressModel = M('Address');
                $address  = $addressModel->field('name,province_id,city_id,area_id,detail_address,tel')->find($requestData['address_id']);
                $orderInfo  = array_merge($orderInfo,$address);

              //>>1.4 送货方式
                $orderInfo['delivery_id'] = $requestData['delivery_id'];
              //>>1.5.支付方式
               $orderInfo['pay_type'] = $requestData['pay_type'];

        //>>2.保持orderInfo中的数据到order_info表中
       $id=  parent::add($orderInfo);
        if($id===false){
            $this->rollback();
            return false;
        }



        //>>5.保持订单明细(从购物车中得到数据保持到订单明细表中)
        $orderinfoItems = array();
        //将购物车中的每个明细转换为订单的每个明细,然后保存明细表中
        foreach($shoppingCar as $item){
            $orderinfoItem=array('goods_id'=>$item['goods_id'],'goods_name'=>$item['name'],'goods_logo'=>$item['logo'],'goods_shop_price'=>$item['shop_price'],'num'=>$item['num'],'price'=>$item['shop_price'],'order_info_id'=>$id);
            if(!empty($item['goods_attribute_ids'])){
                $orderinfoItem['goods_attribute_ids'] = $item['goods_attribute_ids'];
                $orderinfoItem['goods_attribute_strs'] = $item['goods_attribute_strs'];
            }else{
                $orderinfoItem['goods_attribute_ids'] = '';
                $orderinfoItem['goods_attribute_strs'] = '';
            }
            $orderinfoItems[] = $orderinfoItem;
        }


        $oderInfoItemModel = M('OrderInfoItem');

        if(!empty($orderinfoItems)){
            $result = $oderInfoItemModel->addAll($orderinfoItems);
            if($result===false){
                $this->rollback();
                $this->error = '保存订单明细失败!';
                return false;
            }
        }

        //>>6.保持发票数据
            $invoice =$requestData['invoice'];
            //>>6.1准备发票的名字
            switch($invoice['type']){
                case 0:
                    $userinfo = login();
                    $invoice_name = $userinfo['username'];
                    break;
                case 1:
                    $invoice_name = $invoice['name'];
                    break;
            }
            //>>6.2 准备发票的内容
                $content = $invoice['content'];
                if($content==="1"){  //如果是1, 表示需要得到用户的购买详细信息作为 发票的明细
                    $content = '';
                    foreach($shoppingCar as $item){
                        $total_price = $item['num'] * $item['shop_price'];
                        $content.= "{$item['name']}&nbsp;&nbsp;&nbsp;{$item['num']}&nbsp;&nbsp;&nbsp;{$item['shop_price']}&nbsp;&nbsp;&nbsp;{$total_price}<br/>";
                    }
                }

        $invoice = array('name'=>$invoice_name,'content'=>$content,'price'=>$price,'order_info_id'=>$id);
        $invoiceModel = M('Invoice');
        $invoice_id = $invoiceModel->add($invoice);
        if($invoice_id===false){
            $this->rollback();
            $this->error = '保持发票失败!';
            return false;
        }


        //>>3.计算出订单号    2015072 0000000001
        $sn = date("YmdHis").str_pad($id,10,'0',STR_PAD_LEFT);
        //>>4.将订单号更新到当前的订单中
        $result = parent::save(array('id'=>$id,'sn'=>$sn,'invoice_id'=>$invoice_id));
        if($result===false){
            $this->error = '更新订单号或者发票失败!';
            $this->rollback();
            return false;
        }

        $this->commit();

        return  $sn; //返回订单
    }


    /**
     * 使用支付宝进行支付
     */
    public function doPay($sn){
        //>>1.先查询出订单的信息
        if(!$sn){
            $sn = session('sn');
        }

        $orderInfo = $this->getBySn($sn); //动态查询的方法
        //>>2. 使用支付宝第三方插件进行支付




//        require_once("alipay.config.php");
        $alipay_config = C('ALIPAY_CONFIG');
        vendor('Alipay.lib.alipay_submit#class'); //加载thinkphp框架代码中的alipay第三方组件中的内容

        /**************************请求参数**************************/

        //支付类型
        $payment_type = "1";
        //必填，不能修改
        //服务器异步通知页面路径
        $notify_url = "http://localhost/index.php/OrderInfo/notify";
        //需http://格式的完整路径，不能加?id=123这类自定义参数

        //页面跳转同步通知页面路径
        $return_url = "http://www.shop.com/index.php/OrderInfo/paySuccess";  //提示一个支付成功-->跳转到你的网站,不要再这里写任何的业务代码
        //需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/

        //商户订单号
        $out_trade_no = $sn;
        //商户网站订单系统中唯一订单号，必填

        //订单名称
        $subject = '订单号:'.$sn;
        //必填

        //付款金额
//        $price = $orderInfo['price'];
        $price = "0.1";
        //必填

        //商品数量
        $quantity = "1";
        //必填，建议默认为1，不改变值，把一次交易看成是一次下订单而非购买一件商品
        //物流费用
        $logistics_fee = "0.00";
        //必填，即运费
        //物流类型
        $logistics_type = "EXPRESS";
        //必填，三个值可选：EXPRESS（快递）、POST（平邮）、EMS（EMS）
        //物流支付方式
        $logistics_payment = "SELLER_PAY";
        //必填，两个值可选：SELLER_PAY（卖家承担运费）、BUYER_PAY（买家承担运费）
        //订单描述

        $body = '这是测试订单';
        //商品展示地址
        $show_url = "http://www.shop.com/index.php/OrderInfo/show/id/".$orderInfo['id'];
        //需以http://开头的完整路径，如：http://www.商户网站.com/myorder.html

        //收货人姓名
        $receive_name = $orderInfo['name'];
        //如：张三

        //收货人地址
           //需要根据订单中的province_id, city_id,area_id 查询出省市区,
        $receive_address = $orderInfo['detail_address'];
        //如：XX省XXX市XXX区XXX路XXX小区XXX栋XXX单元XXX号

        //收货人邮编
        $receive_zip = '123456';
        //如：123456

        //收货人电话号码
        $receive_phone = $orderInfo['tel'];
        //如：0571-88158090

        //收货人手机号码
        $receive_mobile = $orderInfo['tel'];
        //如：13312341234


        /************************************************************/

//构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "create_partner_trade_by_buyer",
            "partner" => trim($alipay_config['partner']),
            "seller_email" => trim($alipay_config['seller_email']),
            "payment_type"	=> $payment_type,
            "notify_url"	=> $notify_url,
            "return_url"	=> $return_url,
            "out_trade_no"	=> $out_trade_no,
            "subject"	=> $subject,
            "price"	=> $price,
            "quantity"	=> $quantity,
            "logistics_fee"	=> $logistics_fee,
            "logistics_type"	=> $logistics_type,
            "logistics_payment"	=> $logistics_payment,
            "body"	=> $body,
            "show_url"	=> $show_url,
            "receive_name"	=> $receive_name,
            "receive_address"	=> $receive_address,
            "receive_zip"	=> $receive_zip,
            "receive_phone"	=> $receive_phone,
            "receive_mobile"	=> $receive_mobile,
            "_input_charset"	=> trim(strtolower($alipay_config['input_charset']))
        );

//        dump($parameter);
//        exit;

//建立请求
        $alipaySubmit = new \AlipaySubmit($alipay_config);
        //构建出一个html代码.
        $html_text = $alipaySubmit->buildRequestForm($parameter,"get", "确认");
        return $html_text;

    }


    public function notify(){

        $alipay_config = C('ALIPAY_CONFIG');
        vendor('Alipay.lib.alipay_notify#class');

//计算得出通知验证结果
        $alipayNotify = new \AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();

        if($verify_result) {//验证成功
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //请在这里加上商户的业务逻辑程序代


            //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——

            //获取支付宝的通知返回参数，可参考技术文档中服务器异步通知参数列表

            //商户订单号

            $out_trade_no = $_POST['out_trade_no'];

            //支付宝交易号

            $trade_no = $_POST['trade_no'];

            //交易状态
            $trade_status = $_POST['trade_status'];


            if($_POST['trade_status'] == 'WAIT_BUYER_PAY') {
                //该判断表示买家已在支付宝交易管理中产生了交易记录，但没有付款

                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                echo "success";		//请不要修改或删除

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }
            else if($_POST['trade_status'] == 'WAIT_SELLER_SEND_GOODS') {
                //该判断表示买家已在支付宝交易管理中产生了交易记录且付款成功，但卖家没有发货

                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序



//                $out_trade_no 根据商家的订单号更改订单的状态...
                $this->where(array('sn'=>$out_trade_no))->save(array('pay_status'=>2,'order_status'=>1));


                echo "success";		//请不要修改或删除

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }
            else if($_POST['trade_status'] == 'WAIT_BUYER_CONFIRM_GOODS') {
                //该判断表示卖家已经发了货，但买家还没有做确认收货的操作

                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                echo "success";		//请不要修改或删除

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }
            else if($_POST['trade_status'] == 'TRADE_FINISHED') {
                //该判断表示买家已经确认收货，这笔交易完成

                //判断该笔订单是否在商户网站中已经做过处理
                //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
                //如果有做过处理，不执行商户的业务程序

                echo "success";		//请不要修改或删除

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }
            else {
                //其他状态判断
                echo "success";

                //调试用，写文本函数记录程序运行情况是否正常
                //logResult ("这里写入想要调试的代码变量值，或其他运行的结果记录");
            }

            //——请根据您的业务逻辑来编写程序（以上代码仅作参考）——

            /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }
        else {
            //验证失败
            echo "fail";

            //调试用，写文本函数记录程序运行情况是否正常
            //logResult("这里写入想要调试的代码变量值，或其他运行的结果记录");
        }
    }


    /**
     * 将超期并且没有支付订单的库存回滚
     */
    public function timeout(){
        //>>1.找到超期的订单订单明细
        $sql = "select item.* from order_info_item as item join order_info as info on item.order_info_id = info.id
where  info.pay_status = 0 and info.order_status<>2 and info.add_time + 600 < UNIX_TIMESTAMP()";

        $rows = $this->query($sql);
        if(empty($rows)){
            //说明没有过期的订单
            return false;
        }
        $this->startTrans();
        foreach($rows as $row){
            //>>2.根据超期的订单找到订单明细,将订单明细中的库存数量放到原有的库存中
            if(!empty($row['goods_attribute_ids'])){
                //多库存多价格回滚到product表中
                $result  = M('Product')->where(array('goods_id'=>$row['goods_id'],'goods_attribute_ids'=>$row['goods_attribute_ids']))->setInc('stock',$row['num']);
                if($result===false){
                    $this->rollback();
                    return false;
                }
            }else{
                //>>单库存单价格回滚到goods表中
                $result  = M('Goods')->where(array('id'=>$row['goods_id']))->setInc('stock',$row['num']);
                if($result===false){
                    $this->rollback();
                    return false;
                }
            }
        }

        //>>2.修改订单的状态
            //从过期的订单明细中找到订单的订单id
       $order_info_ids = array_column($rows,'order_info_id');
       $result =  M('OrderInfo')->where(array('id'=>array('in',$order_info_ids)))->setField('order_status',2);
        if($result===false){
            $this->rollback();
            return false;
        }
        $this->commit();

    }






}