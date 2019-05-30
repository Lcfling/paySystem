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
            $list = D('Order')->where(array('user_id'=>$user_id))->order('creatime desc')->field('erweima_id,order_sn,tradeMoney,payType,status,creatime')->select();
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

    private function getname($erweima_id){
       $name = D('erweima')->where(array('id'=>$erweima_id))->getField('name');
       return $name;
    }
    
}