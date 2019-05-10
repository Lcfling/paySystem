<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/8
 * Time: 10:27
 */

class OrderymAction extends Action
{
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
            $this->ajaxReturn('','请求失败!',0);
        }
    }

    public function kuaifupay(){

        $datas["merId"] = $_POST['merId'];
        $datas["businessOrderId"] =$_POST['businessOrderId'];
        $datas["tradeMoney"] = $_POST['tradeMoney'];
        $datas["payType"] = $_POST['payType'];
        $datas["asynURL"] = $_POST['asynURL'];

        $sign=$_POST['sign'];

        $extraParams=$_POST['extraParams']; //子商户在我们平台的记录  不参与签名

        $users=D('Admin');
        $where['brandid']=$extraParams;
        $line_rate=$users->where($where)->find();
        if(empty($line_rate)){
            $this->ajaxReturn('error40003','商户号不存在!',0);

        }

        if($line_rate["merId"]!=$datas["merId"]){

            $this->ajaxReturn('error40004','商户密钥错误!',0);

        }
        $pid=$line_rate['pid'];
        $pid_rate=$line_rate['pid_rate'];

        //  if($datas["merId"]!='e5bf50c101d94c0ab8866a5282a64617'){
        // $this->ajaxReturn('error40001','商户密钥错误!',0);

        //}

        if(empty($extraParams)){

            $this->ajaxReturn('error40002','商户号不能为空!',0);
        }

        if( $sign!=$this->getSignK($datas)){
            $this->ajaxReturn('error','签名错误!',0);
        }else{
            $datas["merId"]='e5bf50c101d94c0ab8866a5282a64617';

        }



        //索引pid信息

        /*$users=D('Admin');
        $where['brandid']=$extraParams;
        $line_rate=$users->where($where)->find();
        if(empty($line_rate)){
            $this->ajaxReturn('error40003','商户号不存在!',0);

        }

        if($line_rate["merId"]!=$datas["merId"]){

            $this->ajaxReturn('error40004','商户密钥错误!',0);

        }

        // $brand_rate=$line_rate['rate'];
        $pid=$line_rate['pid'];
        $pid_rate=$line_rate['pid_rate'];*/





//保存子商户记录

        //
        $pay=D('Payord');
        $data['orderNo']=$datas["businessOrderId"];
        $data['pay_money']=$datas["tradeMoney"]*100;
        $data['brandid']=$extraParams;
        $data['pid']=$pid;
        $data['pid_rate']=$pid_rate;
        $data['notifyUrl']=$datas['asynURL'];
        $data['sign']='no';
        $data['creattime']=time();
        $data['sta']=0;
        $pay->add($data);

        //提交请求  签名 回调需要重新
        $datas["asynURL"] = "http://shanghu.zgzyph.com/app/index/kfnotifyurl";
        //$datas["sign"] =$this->getSignK($datas);//签名
        //$datas['extraParams']=$extraParams;
        //$url = 'http://sh.doopooe.com/basic/gateway/v1/OrderPay';
        $url = 'http://sh.aiyft.com/basic/gateway/v1/OrderPay';

        //header("Location:".$url);

        //$data_post = $datas;

        $result = $this->https_post_kfs($url, json_encode($datas, true));

        $final = json_decode($result,true);
        // var_dump($order_res);
// 返回数据错误处理
        $code = $final['code'];
        if($code !== '1000') {
            $msg=$this->get_err_msg($code);
            $this->ajaxReturn('error',$msg,0);

        }

// 获取订单信息
        // $order_info = $result['info'];
        // $result = $this->https_post_kfs($url,$data_post);


        //print_r($final);
        // $this->ajaxReturn('',$final,1);//输出支付url
        $url=$final["info"]["codeurl"];
        $this->ajaxReturn('success',$url,1);//输出支付url
        //  $this->writeLog($result);

        // 保存
        /* $myfile = fopen("ss4.txt", "w") or die("Unable to open file!");

         fwrite($myfile, $datas);
         fclose($myfile);*/


    }

    private function https_post_kfs($url,$data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno'.curl_error($curl);
        }
        curl_close($curl);
        return $result;
    }

    private function get_err_msg($code) {
        $err_msg = array(
            '1002' => '支付失败',
            '0000' => '验签失败',
            '0001' => '商户不存在',
            '0002' => '商户未启用',
            '0003' => '必传参数为空',
            '0004' => '产生订单失败',
            '0005' => '订单金额有误',
            '0006' => '订单金额超出支付范围',
            '0007' => '支付类型无效',
            '0017' => '系统异常',
            '0019' => '修改订单支付方式失败',
            '0020' => '交易金额格式错误',
            '0040' => '代理不存在',
            '0041' => '中转手机不存在',
            '0042' => '生成收款码失败',
            '0043' => '未找到商户所属中转手机代理',
            '0044' => '无手机在线',
            '0045' => '未找到商户的支付通道'
        );
        return $err_msg[$code];
    }
}