<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/25
 * Time: 11:08
 */
class ZfnoticeAction extends CommonAction{

    /**
     * 获取公告
     */
    public function index(){
        if($this->isPost()){
            $messageinfo =D('index')->order('creatime desc')->select();
            $data =array(
                'messageinfo'=>$messageinfo
            );
            $this->ajaxReturn($data,'请求成功!',1);
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     *获取消息
     */
    public function  message(){
        if($this->isPost()){
            $uid =$this->uid;
            $messageinfo =D('Message')->where(array('user_id'=>$uid))->order('creatime desc')->select();
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
            $mas_id =$_POST['mas_id'];
            $statusnum = D('Message')->where(array('user_id'=>$uid,'ifread'=>0,'id'=>$mas_id))->field('ifread')->save(array('ifread'=>1));
            $this->ajaxReturn('','请求成功!',1);
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }



    // 客服二维码
    public function kefu(){

        $kefu=D("kefu");
        $list = $kefu->order(array('id'=>'desc'))->limit(1)->select();
        $this->ajaxReturn($list,'请求成功!',1);

    }

}