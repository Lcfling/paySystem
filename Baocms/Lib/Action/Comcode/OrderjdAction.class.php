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
            $list = D('Order')->where(array('user_id'=>$user_id))->order('creatime desc')->field('id,erweima_id,order_sn,sk_status,tradeMoney,payType,status,creatime')->select();
            foreach ($list as $k =>&$v){
                $v['tradeMoney']= $v['tradeMoney']/100;
                $v['creatime']= date('Y/m/d H:i:s',$v['creatime']);
                $v['name'] = $this->getname($v['erweima_id']);
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

    /**
     * 码商手动收款
     */
    public function savesk_status(){
        if($this->isPost()){
            $user_id =$this->uid;//用户id
            $order_id =$_POST['order_id'];
//            if(D('Order')->where(array('user_id'=>$user_id,'id'=>$order_id,'status'=>0))->find()){
//                $this->ajaxReturn('','当前无法操作,请5分钟之后进行操作!',0);
//            }
            if(!$orderlist =D('Order')->where(array('user_id'=>$user_id,'id'=>$order_id,'sk_status'=>0))->find()){
                $this->ajaxReturn('','订单不存在!',0);
            }
            if(D('Order')->where(array('user_id'=>$user_id,'id'=>$order_id,'sk_status'=>2))->find()){
                $this->ajaxReturn('','系统回调成功,您已收款成功,请刷新当前页面!',0);
            }
            if(D('Order')->where(array('user_id'=>$user_id,'id'=>$order_id,'sk_status'=>2))->find()){
                $this->ajaxReturn('','系统回调成功,您已收款成功,请刷新当前页面!',0);
            }

            $orderstatus = D('Order')->where(array('user_id'=>$user_id,'id'=>$order_id,'sk_status'=>0))->field('sk_status')->save(array('sk_status'=>1));
            if($orderstatus){
                $erweima_id =$orderlist['erweima_id'];
                $chanum = $orderlist['chanum'];
                //存入缓存
                D("Orderym")->chanum_push($erweima_id,$chanum);
                $this->ajaxReturn('','手动收款成功!',1);
            }else{
                $this->ajaxReturn('','手动收款失败!',0);
            }
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }

    }

    private function getname($erweima_id){
       $name = D('Erweima_generic')->where(array('id'=>$erweima_id))->getField('name');
       return $name;
    }
    
}