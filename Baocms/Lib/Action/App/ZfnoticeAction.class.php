<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/25
 * Time: 11:08
 */
class ZfnotionAction extends CommonAction{

    /**
     * 获取公告
     */
    public function index(){

        $indexinfo = D('index')->where(array('id'=>1))->find();
        $this->ajaxReturn($indexinfo,'请求成功!',1);
    }
    /**
     *获取消息
     */
    public function  message(){
        if($this->isPost()){
            $uid =$this->uid;
            $messageinfo =D('Message')->where(array('user_id'=>$uid))->select();
            $messagenum = D('Message')->where(array('user_id'=>$uid,'ifread'=>0))->count();
            $data =array(
                'messageinfo'=>$messageinfo,
                'messagenum'=>$messagenum
            );
            $this->ajaxReturn($data,'请求成功!',1);
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    /**
     *未读更改为已读
     */
    public function  setifread(){
        if($this->isPost()){
            $uid =$this->uid;
            $statusnum = D('Message')->where(array('user_id'=>$uid,'ifread'=>0))->field('ifread')->save(array('ifread'=>1));
            $this->ajaxReturn('','请求成功!',1);
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

}