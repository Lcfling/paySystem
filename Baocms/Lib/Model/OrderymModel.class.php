<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/14
 * Time: 16:00
 */
class OrderymModel extends CommonModel{
    public function erweima_rengelist($user_id,$money,$erweima_id){
        //用户入接单队列
        Cac()->rPush('jiedans',$user_id);
        // 二维码存入用户缓冲
        Cac()->rPush('erweimas'.$money.$user_id,$erweima_id);
    }
}
