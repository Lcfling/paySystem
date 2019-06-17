<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/8
 * Time: 10:27
 */

class OrderymAction extends Action
{

    public function getcache(){
//        D("Users")->enterlist('111',1,11);
//        D("Users")->enterlist('111',1,12);
        $list =Cac()->lRange('erweimas1111',0,-1);
        print_r($list);
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
        $type =$datas['type'];
        unset($datas['sign']);
        unset($datas['business_code']);
        $business = D('business');
        $Order=D('Order');

        $where['business_code']=$business_code;
        if(empty($business_code)){

            $this->ajaxReturn('error40002','商户号不能为空!',0);
        }
        $businessinfo=$business->where($where)->find();
        if(empty($businessinfo)){
            $this->ajaxReturn('error40003','商户未启用!',0);
        }
        if (!is_numeric($datas['tradeMoney']))
        {
            $this->ajaxReturn('error40006','订单金额有误!',0);
        }
        if ($type != 1 && $type != 2)
        {
            $this->ajaxReturn('error40007','支付类型无效!',0);
        }
        if ($Order->where(array('out_uid'=>$datas["out_uid"],'business_code'=>$business_code,'status'=>0))->find())
        {
            $this->ajaxReturn('error40008','已有订单存在,请您先取消!',0);
        }

        if( $sign!=$this->getSignK($datas,$businessinfo['accessKey'])){
            $this->ajaxReturn('error','签名错误!',0);
        }

        $erweimainfo = D("Users")->getcode($datas["tradeMoney"]/100);//二维码信息
        if(empty($erweimainfo)){
            $this->ajaxReturn('error40004','暂无支付码!',0);
        }
//        print_r('money-'.$datas["tradeMoney"]/100);
//        print_r("~~~~~~~~~~二维码信息~~~~~~~~~~~~~~~");
//        echo "<pre>";print_r($erweimainfo);
        //保存商户订单记录
        $data =array(
            'out_uid'=>$datas["out_uid"],
            'out_order_sn'=>$datas["out_order_sn"],
            'order_sn'=>$this->getrequestId(),
            'payType'=>$datas["payType"],
            'tradeMoney'=>$datas["tradeMoney"],
            'payMoney'=>$erweimainfo["edu"],
            'erweima_id'=>$erweimainfo['id'],
            'business_code'=>$business_code,
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
            $rate = D('Users')->where(array('user_id'=>$erweimainfo['user_id']))->getField('rate');
            D('Rebate')->fy($datas["tradeMoney"] * 100 ,$erweimainfo['user_id'],$rate,$erweimainfo['id'],$business_code,$datas["out_uid"]);
            $this->geterweimaurl($erweimainfo["erweima"]);
        }else{
            $this->ajaxReturn('error40005','',0);
        }


    }

    /**获取支付页面
     * @param $erweimaurl
     */
    private function geterweimaurl($erweimaurl){
        $url=substr($erweimaurl,1);
        $qrurl = 'http://'.$_SERVER['HTTP_HOST'].'/wxzfqr/zhifu.html?url=';
        $this->ajaxReturn('OK',$qrurl.$_SERVER['HTTP_HOST'].$url,1001);//输出支付url
    }
    /**
     * 前端回调及 调 第三方回调
     */
    public function kfnotifyurl(){
        $Order=D('Order');
        $datas =$_POST;
        $user_id = $datas['user_id'];
        $payType = 1;
        $pay_time = $datas['pay_time'];
        $payMoney = $datas['tradeMoney'] *100;
        if($orderinfo =$Order->where(array('user_id'=>$user_id,'payMoney'=>$payMoney,'payType'=>$payType,'status'=>0))->find()){
            file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~订单匹配成功~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
            file_put_contents('./notifyUrl.txt',print_r($datas,true),FILE_APPEND);
            $money =$orderinfo['tradeMoney'];
            $res =$Order->where(array('user_id'=>$user_id,'payMoney'=>$payMoney,'payType'=>$payType,'status'=>0))->field('status,pay_time,dj_status')->save(array('status'=>1,'pay_time'=>$pay_time,'dj_status'=>1));
            file_put_contents('./notifyUrl.txt',$Order->getLastSql(),FILE_APPEND);
            if($res){
                $logdata =array(
                    'user_id'=>$user_id,
                    'score'=>$money,
                    'erweima_id'=>$orderinfo['erweima_id'],
                    'business_code'=>$orderinfo['business_code'],
                    'out_uid'=>$orderinfo["out_uid"],
                    'status'=>4,
                    'payType'=>$payType,
                    'remark'=>'跑分解冻',
                    'creatime'=>time()
                );
                D('Account_log')->add($logdata);
                $paydata =array(
                    'user_id'=>$user_id,
                    'score'=>-$money,
                    'erweima_id'=>$orderinfo['erweima_id'],
                    'business_code'=>$orderinfo['business_code'],
                    'out_uid'=>$orderinfo["out_uid"],
                    'status'=>2,
                    'payType'=>$payType,
                    'remark'=>'支付扣除',
                    'creatime'=>time()
                );
                D('Account_log')->add($paydata);
                $this->ajaxReturn('success','',1);
            }else{
                $this->ajaxReturn('fail','',0);
            }
        }else{
            $datas['status']=1;
            D('Yc_order')->add($datas);
            file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~订单匹配失败~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
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
//        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno'.curl_error($curl);
        }
        curl_close($curl);
        return $result;
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