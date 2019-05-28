<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/20
 * Time: 14:25
 */
class RebateModel extends Model {

    public function fy($order_id,$list,$use_id,$rate,$erweima_id,$business_code,$out_uid){
        $money = $list * $rate;
        if($money<0 || $money==0 ){return;}
        $this->rebate($order_id,$use_id,$money,$erweima_id,$business_code,$out_uid);
        $userinfo = D('Users')->where(array('pid'=>$use_id))->field('user_id,rate')->find();
        if($userinfo['user_id']){
            $puse_id =$userinfo['user_id'];
            $prate =$userinfo['rate'] * 10000;
            $rate = $rate * 10000;
            $pmoney = ($prate - $rate) * $list / 10000;
            if($pmoney<0 || $pmoney==0 ){return;}
            $this->rebate($order_id,$puse_id,$pmoney,$erweima_id,$business_code,$out_uid);
            $userinfo1 = D('Users')->where(array('pid'=>$puse_id))->field('user_id,rate')->find();
            if($userinfo1['user_id']){
                $duse_id =$userinfo1['user_id'];
                $drate =$userinfo['rate'] * 10000;
                $dmoney = ($drate - $prate) * $list /10000;
                if($dmoney<0 || $dmoney==0){return;}
                $this->rebate($order_id,$duse_id,$dmoney,$erweima_id,$business_code,$out_uid);
            }

        }
    }

    private function rebate($order_id,$user_id,$money,$erweima_id,$business_code,$out_uid){
        $data=array(
            'user_id'=>$user_id,
            'order_id'=>$order_id,
            'score'=>$money,
            'erweima_id'=>$erweima_id,
            'business_code'=>$business_code,
            'out_uid'=>$out_uid,
            'status'=>5,
            'remark'=>'佣金',
            'creatime'=>time()
        );
        D('Account_log')->add($data);
    }

}