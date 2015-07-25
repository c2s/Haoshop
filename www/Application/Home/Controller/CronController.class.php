<?php

namespace Home\Controller;


use Think\Controller;

/**
 * 该类中的方法都是被定时任务调用执行
 * Class CronController
 * @package Home\Controller
 */
class CronController extends Controller{


    public function clicknumRedisToMysql(){
        //>>1.从redis中取出商品的浏览次数
            $redis = getRedis();
            $result = $redis->zRange('goods_clicknum',0,-1,true);
        //>>2.将商品的浏览次数同步的mysql中
            foreach($result as $goods_id=>$clicknum){
                $goods_id = substr($goods_id,12);
                $sql = "update goods set clicknum  = $clicknum where id = $goods_id";
                M()->execute($sql);
            }
    }



}