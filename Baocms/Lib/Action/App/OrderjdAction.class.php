<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 17:20
 */
class OrderjdAction extends CommonAction{

    /**
     * 接单订单列表
     */
    public function orderjd_list(){

        if($this->isPost()){
            $user_id =$this->uid;//用户id
            $recmoney =D('Account')->getdaybrokerage($user_id,3);
            $sucmoney =D('Account')->getdaybrokerage($user_id,2);
            $list = D('Order')->where(array('user_id'=>$user_id))->field('order_sn,tradeMoney,payType,status,creatime')->select();
            foreach ($list as $k =>&$v){
                $v['tradeMoney']= $v['tradeMoney']/100;
                $v['creatime']= date('Y/m/d H:i:s',$v['creatime']);
            }
            $data = array(
                'recmoney'=>-$recmoney/100,
                'sucmoney'=>-$sucmoney/100,
                'list'=>$list
            );
            $this->ajaxReturn($data,'请求成功!',1);
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    public function cancel(){
        if($this->isPost()){
            $user_id =$_POST['user_id'];//用户id
            $orderid =$_POST['orderid'];//订单id
            if(D('Order')->where(array('status'=>3,'id'=>$orderid,'user_id'=>$user_id))->find()){
                $this->ajaxReturn('','请勿频繁操作!',0);
            }
            if(D('Order')->where(array('status'=>1,'id'=>$orderid,'user_id'=>$user_id))->find()){
                $this->ajaxReturn('','您已支付成功!',1);
            }
            if($orderlist =D('Order')->where(array('id'=>$orderid,'user_id'=>$user_id))->find()){
                $this->ajaxReturn('','此订单不存在!',0);
            }
            $savestatus =D('Order')->where(array('status'=>2,'id'=>$orderid,'user_id'=>$user_id))->field('status')->save(array('status'=>3));
            if($savestatus){
                if($orderlist['dj_status']==0){
                    $data =array(
                        'user_id'=>$user_id,
                        'score'=>$orderlist['money'],
                        'erweima_id'=>$orderlist['erweima_id'],
                        'business_id'=>$orderlist['business_id'],
                        'out_uid'=>$orderlist['out_uid'],
                        'status'=>4,
                        'type'=>1,
                        'remark'=>'解冻',
                        'creatime'=>time()
                    );
                    D('Account_log')->add($data);
                }
                $this->ajaxReturn('','取消成功!',1);
            }else{
                $this->ajaxReturn('','取消失败,稍后重试!',0);
            }

        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

}