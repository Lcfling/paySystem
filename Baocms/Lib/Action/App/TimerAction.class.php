<?php


class TimerAction extends CommonAction
{

    /**
     * $user_id 码商id
     * $TraderUid 外部用户id 商户id和用户id的组合
     *
     */
    public function index()
    {
        //判断客户排队是否有数据 有数据的情况下 拉取码商的队列信息
        for($i=0;$i<10;$i++){
            if(D("timer")->is_PayQueue_empty()){
                exit();
            }else{
                $user_id=D("timer")->getOneUserId();
                if($user_id){
                    $TraderUid=D("timer")->getOneOutUid();
                    if(!$TraderUid){
                        //码商返回队列第一个
                        D("timer")->lpushUserId();
                        exit("外部等待id为空");
                    }
                    //记录匹配订单
                    Cac()->set("porder_".$TraderUid,time());
                    Cac()->set("porder_userid_".$TraderUid,$user_id);
                    //入队等待队列 timer超时后码商重新回队
                    Cac()->rPush("Timer_queue",$TraderUid);

                    //结束 等待客户轮训获取订单支付
                }else{
                    exit();
                }
            }
        }
        exit("循环结束");
    }

    //垃圾回收 超过1分钟 释放码商重新入队
    public function traderback(){

        for($i=0;$i<25;$i++){
            $TraderUid=Cac()->lPop("Timer_queue");
            if(empty($TraderUid)||$TraderUid==false||$TraderUid==""){
                break;
            }
            $TraderTime=Cac()->get("porder_".$TraderUid);
            $user_id=Cac()->get("porder_userid_".$TraderUid);
            if(time()-$TraderTime>60){
                //超时 码商回队

                D("timer")->lpushUserId($user_id);
                Cac()->delete("porder_".$TraderUid,time());
                Cac()->delete("porder_userid_".$TraderUid);
            }else{
                Cac()->rPush("Timer_queue",$TraderUid);
            }
        }
    }
}