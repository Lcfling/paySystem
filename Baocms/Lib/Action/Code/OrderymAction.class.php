<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/8
 * Time: 10:27
 */

class OrderymAction extends Action
{

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
    public function geturl(){
        $inputstr = D("QRcode")->getQRurl('leshua.622c7.cn/erweima/20190603051242.png','1559559026','219','10044');
        echo $inputstr;
        exit();
    }
    /**
     * 第三方调支付
     */
    public function kuaifupay(){

        $datas =$_POST;
        file_put_contents('./businesspost.txt',"~~~~~~~~~~~~~post数据".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
        file_put_contents('./businesspost.txt',print_r($datas,true).PHP_EOL,FILE_APPEND);
        file_put_contents('./businesspost.txt',"~~~~~~~~~~~~~business_code".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
        file_put_contents('./businesspost.txt',print_r($datas['business_code'],true).PHP_EOL,FILE_APPEND);
        $sign=$datas['sign'];
        $business_code=$datas['business_code']; //商户号 不参与签名
        $out_order_sn = $datas['out_order_sn'];//商户订单号
        $type =$datas['payType'];
        $codeType =$datas['codeType'];
        unset($datas['sign']);
        unset($datas['business_code']);
        $business = D('business');
        $Order=D('Order');

        $where['business_code']=$business_code;
        if(empty($business_code)){
//            if($business_code == 30011){
                $inputstr=array(
                    'status'=>0,
                    'data'=>"error40002",
                    'info'=>"商户号不能为空!"
                );
                $this->returnhtml($inputstr);
//            }

            $this->ajaxReturn('error40002','商户号不能为空!',0);
        }
        $businessinfo=$business->where($where)->find();
        if(empty($businessinfo)){
//            if($business_code == 30011){
                $inputstr=array(
                    'status'=>0,
                    'data'=>"error40003",
                    'info'=>"商户未启用!"
                );
                $this->returnhtml($inputstr);
//            }
            $this->ajaxReturn('error40003','商户未启用!',0);
        }
        if (!is_numeric($datas['tradeMoney']))
        {
//            if($business_code == 30011){
                $inputstr=array(
                    'status'=>0,
                    'data'=>"error40006",
                    'info'=>"订单金额有误!"
                );
                $this->returnhtml($inputstr);
//            }
            $this->ajaxReturn('error40006','订单金额有误!',0);
        }
        if ($type != 1 && $type != 2)
        {
//            if($business_code == 30011){
                $inputstr=array(
                    'status'=>0,
                    'data'=>"error40007",
                    'info'=>"支付类型无效!"
                );
                $this->returnhtml($inputstr);
//            }
            $this->ajaxReturn('error40007','支付类型无效!',0);
        }
        if ($codeType != 1 && $codeType != 2)
        {
            $inputstr=array(
                'status'=>0,
                'data'=>"error40007",
                'info'=>"二维码类型无效!"
            );
            $this->returnhtml($inputstr);
        }
        if ($orderlist = $Order->where(array('out_order_sn'=>$out_order_sn,'business_code'=>$business_code,'status'=>0))->find())
        {
            $Erwermalsit = D('Erweima')->where(array('id'=>$orderlist['erweima_id']))->find();
            $this->geterweimaurl($Erwermalsit["erweima"],$orderlist['id'],$orderlist['user_id'],$orderlist['creatime']+300,2,$business_code);
        }
        if ($orderlist = $Order->where(array('out_order_sn'=>$out_order_sn,'business_code'=>$business_code,'status'=>2))->find())
        {
            $Erwermalsit = D('Erweima')->where(array('id'=>$orderlist['erweima_id']))->find();
            $this->geterweimaurl($Erwermalsit["erweima"],$orderlist['id'],$orderlist['user_id'],$orderlist['creatime']+300,3,$business_code);
        }

        if( $sign!=$this->getSignK($datas,$businessinfo['accessKey'])){

             file_put_contents('./sign.txt',"~~~~~~~~~~~~~~~平台sign".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
             $psign = $this->getSignK($datas,$businessinfo['accessKey']);
             file_put_contents('./sign.txt',print_r($psign,true).PHP_EOL,FILE_APPEND);
             file_put_contents('./sign.txt',"~~~~~~~~~~~~~~~平台商户sign".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
             file_put_contents('./sign.txt',print_r($sign,true).PHP_EOL,FILE_APPEND);
//            if($business_code == 30011){
                $inputstr=array(
                    'status'=>0,
                    'data'=>"error",
                    'info'=>"签名错误!"
                );
                $this->returnhtml($inputstr);
//            }

            $this->ajaxReturn('error','签名错误!',0);
        }
        if($codeType == 1){
            $erweimainfo = D("Users")->getcode($datas["tradeMoney"]/100,1);//二维码信息
            $payMoney =$erweimainfo["edu"];
        }else{
            $erweimainfo = D("Users")->getGeneric_code($datas["tradeMoney"]/100,1);//二维码信息
            $payMoney =$datas["tradeMoney"] - mt_rand(0,9);
        }
        if(!$erweimainfo){
//            if($business_code == 30011){
                $inputstr=array(
                    'status'=>0,
                    'data'=>"error40004",
                    'info'=>"暂无支付码!"
                );
                $this->returnhtml($inputstr);
//            }
            $this->ajaxReturn('error40004','暂无支付码!',0);
        }

        $time =time();

        //保存商户订单记录
        $data =array(
            'out_uid'=>$datas["out_uid"],
            'out_order_sn'=>$datas["out_order_sn"],
            'order_sn'=>$this->getrequestId(),
            'payType'=>$datas["payType"],
            'tradeMoney'=>$datas["tradeMoney"],
            'payMoney'=>$payMoney,
            'erweima_id'=>$erweimainfo['id'],
            'business_code'=>$business_code,
            'user_id'=>$erweimainfo['user_id'],
            'creatime'=>$time,
            'notifyUrl'=>$datas['notifyUrl']
        );
        $order_id = $Order->add($data);
        if($order_id){
            $logdata =array(
                'user_id'=>$erweimainfo['user_id'],
                'order_id'=>$order_id,
                'score'=>-$datas["tradeMoney"],
                'erweima_id'=>$erweimainfo['id'],
                'business_code'=>$business_code,
                'out_uid'=>$datas["out_uid"],
                'status'=>3,
                'payType'=>$datas["payType"],
                'remark'=>'冻结中',
                'creatime'=>$time
            );
            D('Account_log')->add($logdata);
            if($codeType == 1){
                $this->geterweimaurl($erweimainfo["erweima"],$order_id,$erweimainfo['user_id'],$time + 300,1,$business_code);
            }else{
                $this->getcomonerweimaurl($erweimainfo["erweima"],$order_id,$erweimainfo['user_id'],$time + 300,1,$business_code,$payMoney);
            }
        }else{
            $this->ajaxReturn('error40005','',0);
        }


    }

    /**获取支付页面
     * @param $erweimaurl
     */
    private function geterweimaurl($erweimaurl,$order_id,$user_id,$gptime,$type,$business_code){
        $url=substr($erweimaurl,1);
        $data = $_SERVER['HTTP_HOST'].$url.'&'.$order_id.'&'.$user_id.'&'.$gptime;

        if($type == 1){
            if($business_code == 30011){
                $inputstr = D("QRcode")->getQRurl($_SERVER['HTTP_HOST'].$url,$gptime,$order_id,$user_id);
                echo $inputstr;exit();
            }
            $qrurl = 'http://'.$_SERVER['HTTP_HOST'].'/wxzfqr/zhifufirst.html?data='.$data;
        }elseif($type == 2){
            if($business_code == 30011){
                $inputstr = D("QRcode")->getQRurlsecond($_SERVER['HTTP_HOST'].$url,$gptime,$order_id,$user_id);
                echo $inputstr;exit();
            }
            $qrurl = 'http://'.$_SERVER['HTTP_HOST'].'/wxzfqr/zhifusecond.html?data='.$data;
        }else{
            if($business_code == 30011){
                $inputstr = D("QRcode")->getQRurlthird($_SERVER['HTTP_HOST'].$url,$gptime,$order_id,$user_id);
                echo $inputstr;exit();
            }
            $qrurl = 'http://'.$_SERVER['HTTP_HOST'].'/wxzfqr/zhifuthird.html?data='.$data;
        }
        $this->ajaxReturn('OK',$qrurl,1001);//输出支付url
    }

    /**获取通用码支付页面
     * @param $erweimaurl
     */
    private function getcomonerweimaurl($erweimaurl,$order_id,$user_id,$gptime,$type,$business_code,$tradeMoney){
        $url=substr($erweimaurl,1);
        $data = $_SERVER['HTTP_HOST'].$url.'&'.$order_id.'&'.$user_id.'&'.$gptime;

        if($type == 1){
//            if($business_code == 30011){
                $inputstr = D("QRcode")->getcommonQRurl($_SERVER['HTTP_HOST'].$url,$gptime,$order_id,$user_id,$tradeMoney);
                echo $inputstr;exit();
//            }
//            $qrurl = 'http://'.$_SERVER['HTTP_HOST'].'/wxzfqr/zhifufirst.html?data='.$data;
        }elseif($type == 2){
//            if($business_code == 30011){
                $inputstr = D("QRcode")->getcomonQRurlsecond($_SERVER['HTTP_HOST'].$url,$gptime,$order_id,$user_id,$tradeMoney);
                echo $inputstr;exit();
//            }
            $qrurl = 'http://'.$_SERVER['HTTP_HOST'].'/wxzfqr/zhifusecond.html?data='.$data;
        }else{
//            if($business_code == 30011){
                $inputstr = D("QRcode")->getcomonQRurlthird($_SERVER['HTTP_HOST'].$url,$gptime,$order_id,$user_id);
                echo $inputstr;exit();
//            }
            $qrurl = 'http://'.$_SERVER['HTTP_HOST'].'/wxzfqr/zhifuthird.html?data='.$data;
        }
        $this->ajaxReturn('OK',$qrurl,1001);//输出支付url
    }
    /**
     * 前端回调
     */
    public function kfnotifyurl(){
        $Order=D('Order');
        $datas =$_POST;
        $user_id = $datas['user_id'];
        $payType = 1;
        $pay_time = $datas['pay_time'];
        $payMoney = $datas['payMoney'] *100;
        if($orderinfo =$Order->where(array('user_id'=>$user_id,'payMoney'=>$payMoney,'payType'=>$payType,'status'=>0))->find()){
            file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~订单匹配成功".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
            file_put_contents('./notifyUrl.txt',print_r($datas,true),FILE_APPEND);
            $res =$Order->where(array('user_id'=>$user_id,'payMoney'=>$payMoney,'payType'=>$payType,'status'=>0))->field('status,sk_status,pay_time')->save(array('sk_status'=>2,'status'=>1,'pay_time'=>$pay_time));
            file_put_contents('./notifyUrl.txt',$Order->getLastSql(),FILE_APPEND);
            if($res){
                $order_id=$orderinfo['id'];
                $tradeMoney=$orderinfo['tradeMoney'];
                $erweima_id=$orderinfo['erweima_id'];
                $business_code=$orderinfo['business_code'];
                $out_uid=$orderinfo["out_uid"];
                if($orderinfo['dj_status']==0){
                    $logdata =array(
                        'user_id'=>$user_id,
                        'order_id'=>$order_id,
                        'score'=>$tradeMoney,
                        'erweima_id'=>$erweima_id,
                        'business_code'=>$business_code,
                        'out_uid'=>$out_uid,
                        'status'=>4,
                        'payType'=>$payType,
                        'remark'=>'资金解冻',
                        'creatime'=>time()
                    );
                    D('Account_log')->add($logdata);
                    D('Order')->where(array('id'=>$order_id,'user_id'=>$user_id,'payType'=>$payType))->field('dj_status')->save(array('dj_status'=>1));
                }

                $paydata =array(
                    'user_id'=>$user_id,
                    'order_id'=>$order_id,
                    'score'=>-$tradeMoney,
                    'erweima_id'=>$erweima_id,
                    'business_code'=>$business_code,
                    'out_uid'=>$out_uid,
                    'status'=>2,
                    'payType'=>$payType,
                    'remark'=>'资金扣除',
                    'creatime'=>time()
                );
                $paystatus = D('Account_log')->add($paydata);

                //存入缓存
                D("Users")->enterlist($user_id,$tradeMoney/100,$erweima_id);
                if($paystatus){
                    $userinfo = D('Users')->where(array('user_id'=>$user_id))->field('rate,pid')->find();
                    file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~码商费率".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl.txt',print_r($userinfo['rate'],true).PHP_EOL,FILE_APPEND);
                    //返佣
                    D('Rebate')->fy($order_id,$tradeMoney,$user_id,$userinfo['rate'],$userinfo['pid'],$erweima_id,$business_code,$out_uid);
                    //减码的额度
                    D("erweima")->where(array("id"=>$erweima_id))->setDec('limits',$tradeMoney/100);
                }
                $this->ajaxReturn('success','',1);
            }else{
                $this->ajaxReturn('fail','',0);
            }
        }else{
            $datas['status']=1;
            D('Yc_order')->add($datas);
            file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~订单匹配失败".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
            file_put_contents('./notifyUrl.txt',print_r($datas,true),FILE_APPEND);
            $this->ajaxReturn('fail','',0);
        }

    }
    /**
     * 第一次进入订单检测
     */
    public function ddcheckfirst(){
        if($this->isPost()){
            $user_id =$_POST['user_id'];//码商id
            $orderid =$_POST['order_id'];//订单id
            $Order =D('Order');

            if ($orderlist = $Order->where(array('user_id'=>$user_id,'id'=>$orderid,'status'=>2))->find())
            {
                $this->ajaxReturn('','订单已过期,请取消订单!',0);
            }
            if ($orderlist = $Order->where(array('user_id'=>$user_id,'id'=>$orderid,'status'=>3))->find())
            {
                $this->ajaxReturn('','订单已被取消!',0);
            }
            if ($orderlist = $Order->where(array('user_id'=>$user_id,'id'=>$orderid,'status'=>1))->find())
            {
                $this->ajaxReturn('','已支付成功!',0);
            }

            $this->ajaxReturn($_POST,'请求成功!',1);
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    /**
     * 第二次进入订单检测
     */
    public function ddchecksecond(){
        if($this->isPost()){
            $user_id =$_POST['user_id'];//码商id
            $orderid =$_POST['order_id'];//订单id
            $Order =D('Order');
            if ($orderlist = $Order->where(array('user_id'=>$user_id,'id'=>$orderid,'status'=>0))->find())
            {
                $this->ajaxReturn('','订单已存在,继续支付或者取消订单!',0);
            }
            if ($orderlist = $Order->where(array('user_id'=>$user_id,'id'=>$orderid,'status'=>2))->find())
            {
                $this->ajaxReturn('','订单已过期,请取消订单!',0);
            }
            if ($orderlist = $Order->where(array('user_id'=>$user_id,'id'=>$orderid,'status'=>3))->find())
            {
                $this->ajaxReturn('','订单已被取消!',0);
            }
            if ($orderlist = $Order->where(array('user_id'=>$user_id,'id'=>$orderid,'status'=>1))->find())
            {
                $this->ajaxReturn('','已支付成功!',0);
            }

            $this->ajaxReturn($_POST,'请求成功!',1);
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    /**
     * 取消订单
     */
    public function ddcancel(){
        if($this->isPost()){
            $user_id =$_POST['user_id'];//用户id
            $orderid =$_POST['order_id'];//订单id

            if(!$orderlist =D('Order')->where(array('id'=>$orderid,'user_id'=>$user_id))->find()){
                $this->ajaxReturn('','此订单不存在!',0);
            }
            if(D('Order')->where(array('status'=>3,'id'=>$orderid,'user_id'=>$user_id))->find()){
                $this->ajaxReturn('','请勿频繁操作!',0);
            }
            if(D('Order')->where(array('status'=>1,'id'=>$orderid,'user_id'=>$user_id))->find()){
                $this->ajaxReturn('','您已支付成功!',0);
            }

            $savestatus =D('Order')->where(array('id'=>$orderid,'user_id'=>$user_id))->field('status')->save(array('status'=>3));
            if($savestatus){
                if($orderlist['dj_status']==0){
                    $data =array(
                        'user_id'=>$user_id,
                        'order_id'=>$orderid,
                        'score'=>$orderlist['tradeMoney'],
                        'erweima_id'=>$orderlist['erweima_id'],
                        'business_code'=>$orderlist['business_code'],
                        'out_uid'=>$orderlist['out_uid'],
                        'status'=>4,
                        'payType'=>1,
                        'remark'=>'资金解冻',
                        'creatime'=>time()
                    );
                    D('Account_log')->add($data);
                    D('Order')->where(array('id'=>$orderid,'user_id'=>$user_id))->field('dj_status')->save(array('dj_status'=>1));
                }
                D("Users")->enterlist($user_id,$orderlist['tradeMoney']/100,$orderlist['erweima_id']);
                $this->ajaxReturn('','取消成功!',1);
            }else{
                $this->ajaxReturn('','取消失败,稍后重试!',0);
            }

        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    /**
     * 已付款检测
     */
    public function paycheck(){
        if($this->isPost()){
            $user_id =$_POST['user_id'];//码商id
            $orderid =$_POST['order_id'];//订单id
            $Order =D('Order');
            if ($orderlist = $Order->where(array('user_id'=>$user_id,'id'=>$orderid,'status'=>0))->find())
            {
                $this->ajaxReturn('','订单未支付成功，请继续支付或者稍后再试!',0);
            }
            if ($orderlist = $Order->where(array('user_id'=>$user_id,'id'=>$orderid,'status'=>2))->find())
            {
                $this->ajaxReturn('','订单已过期,请取消订单!',0);
            }

            if ($orderlist = $Order->where(array('user_id'=>$user_id,'id'=>$orderid,'status'=>1))->find())
            {
                $this->ajaxReturn('','支付成功!',1);
            }

            if ($orderlist = $Order->where(array('user_id'=>$user_id,'id'=>$orderid,'status'=>3))->find())
            {
                $this->ajaxReturn('','订单已取消!',0);
            }

            $this->ajaxReturn($_POST,'订单异常!',0);
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
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

        // file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~加密钱钱前数据~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
        // file_put_contents('./notifyUrl.txt',print_r($String,true).PHP_EOL,FILE_APPEND);
        // $this->writeLog($String);
        //签名步骤二：在string后加入KEY
        $String = $String."&accessKey=".$key;
        //echo "【string2】".$String."</br>";
        file_put_contents('./sign.txt',"~~~~~~~~~~~~~~~加密前报文".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
        file_put_contents('./sign.txt',print_r($String,true).PHP_EOL,FILE_APPEND);
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

    /**
     * 返回html页面代码
     */
    private function returnhtml($inputstr){
        $json = json_encode($inputstr,true);
        echo htmlentities($json ,ENT_QUOTES,"UTF-8");exit();
    }
}