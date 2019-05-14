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

    /**
     * 获取商户秘钥 唯一识别码
     */
    public function getbusiness(){
        $business = D('business');
        $business->where(array('id'=>1))->find();
        $data =array(
            'business_code'=>$business['business_code'],
            'accessKey'=>$business['accessKey']
        );
        $this->ajaxReturn($data,'请求成功!',1);
    }

    /**
     * 第三方调支付
     */
    public function kuaifupay(){
        $datas =$_POST;
        $sign=$datas['sign'];
        $business_code=$datas['business_code']; //商户号 不参与签名
        unset($datas['sign']);
        unset($datas['business_code']);
        $business = D('business');
        $where['business_code']=$business_code;
        if(empty($business_code)){

            $this->ajaxReturn('error40002','商户号不能为空!',0);
        }
        $businessinfo=$business->where($where)->find();
        if(empty($businessinfo)){
            $this->ajaxReturn('error40003','商户号不存在!',0);

        }

        if( $sign!=$this->getSignK($datas,$businessinfo['accessKey'])){
            $this->ajaxReturn('error','签名错误!',0);
        }
        $erweimainfo = D("Users")->getcode($datas["tradeMoney"]);//二维码信息
        //保存商户订单记录
        $Order=D('Order');
        $data =array(
            'out_uid'=>$datas["out_uid"],
            'out_order_sn'=>$datas["out_order_sn"],
            'order_sn'=>$this->getrequestId(),
            'payType'=>$datas["payType"],
            'tradeMoney'=>$datas["tradeMoney"],
            'erweima_id'=>$erweimainfo['id'],
            'user_id'=>$erweimainfo['user_id'],
            'creatime'=>time(),
            'notifyUrl'=>$datas['notifyUrl']
        );
        $res = $Order->add($data);
        if($res){
            $logdata =array(
                'user_id'=>$erweimainfo['user_id'],
                'score'=>-$datas["tradeMoney"],
                'erweima_id'=>$erweimainfo['id'],
                'business_code'=>$business_code,
                'out_uid'=>$datas["out_uid"],
                'status'=>3,
                'payType'=>$datas["payType"],
                'remark'=>'跑分冻结',
                'creatime'=>time()
            );
            D('Account_log')->add($logdata);
            $url=substr($erweimainfo["erweima"],1);
            $this->ajaxReturn('success',$_SERVER['HTTP_HOST'].$url,1);//输出支付url
        }else{
            $this->ajaxReturn('fail','',0);//输出支付url
        }


    }

    /**
     * 前端回调及 调 第三方回调
     */
    public function kfnotifyurl(){


        $Order=D('Order');
        $datas =$_POST;
        $user_id = $datas['user_id'];
        $tradeMoney = $datas['tradeMoney'] * 100;
        $payType = 1;
        $pay_time = $datas['pay_time'];
        if($orderinfo =$Order->where(array('user_id'=>$user_id,'tradeMoney'=>$tradeMoney,'payType'=>$payType,'status'=>0))->find()){
            $res =$Order->where(array('user_id'=>$user_id,'tradeMoney'=>$tradeMoney,'payType'=>$payType,'status'=>0))->field('status,pay_time')->save(array('status'=>1,'pay_time'=>$pay_time,'dj_status'=>1));
            if($res){
                $logdata =array(
                    'user_id'=>$user_id,
                    'score'=>$tradeMoney,
                    'erweima_id'=>$orderinfo['erweima_id'],
                    'business_code'=>$orderinfo['business_code'],
                    'out_uid'=>$orderinfo["out_uid"],
                    'status'=>4,
                    'payType'=>$payType,
                    'remark'=>'跑分解冻',
                    'creatime'=>time()
                );
                D('Account_log')->add($logdata);
                $url=$orderinfo['notifyUrl'];
                $data=array(
                    'order_sn'=>$orderinfo['order_sn'],
                    'out_order_sn'=>$orderinfo['out_order_sn'],
                    'tradeMoney'=>$orderinfo['tradeMoney'],
                    'pay_time'=>$pay_time,
                );

                $business = D('business');
                $where['business_code']=$orderinfo['business_code'];
                $businessinfo=$business->where($where)->find();
                if(empty($businessinfo)){
                    $this->ajaxReturn('error40003','商户号不存在!',0);
                }
                $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                $this->https_post_kfs($url,$data);
                $this->ajaxReturn('success','',1);
            }else{
                $this->ajaxReturn('fail','',0);
            }
        }else{
            $datas['status']=1;
            D('Yc_order')->add($datas);
            file_put_contents('./notifyUrl.txt',print_r($datas,true),FILE_APPEND);
            $this->ajaxReturn('fail','',0);
        }

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

    /**签名
     * @param $Obj
     * @param $key
     * @return string
     */
    private function getSignK($Obj,$key){

        foreach ($Obj as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String =$this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$String.'</br>';


        // $this->writeLog($String);
        //签名步骤二：在string后加入KEY
        $String = $String."&accessKey=".$key;
        //echo "【string2】".$String."</br>";

        //echo $String;
        //签名步骤三：MD5加密

        $String = md5($String);

        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    /**字典排序 & 拼接
     * @param $paraMap
     * @param $urlencode
     * @return bool|string
     */
    function formatBizQueryParaMap($paraMap, $urlencode){
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }

        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**生成唯一订单号
     * @return string
     */
    private function getrequestId(){
        list($s1, $s2)	=	explode(' ', microtime());
        list($ling, $haomiao)=	explode('.', $s1);
        $haomiao    =	substr($haomiao,0,3);
        $requestId  =	date("YmdHis",$s2).$haomiao; //商户订单号(out_trade_no).必填(建议是英文字母和数字,不能含有特殊字符)
        return $requestId;
    }
}