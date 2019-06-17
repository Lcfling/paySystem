<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/15
 * Time: 11:47
 */
class TimernotifyAction extends Action
{

    /**
     *第一次 异步回调
     */
    public function sfpushfirst(){
        $key = $_GET['key'];
        if($key == '36cae679f8cb296d69be4f27bd8cc3d6'){
            $Order=D('Order');
            $orderinfo =$Order->where(array('status'=>1,'callback_status'=>0,'callback_num'=>0))->select();
            if($orderinfo){
                foreach ($orderinfo as $k=>$v){
                    $url=$v['notifyUrl'];
                    $user_id = $v['user_id'];
                    $tradeMoney = $v['tradeMoney'] ;
                    $erweima_id =$v['erweima_id'];
                    $payType =$v['payType'];
                    $business_code =$v['business_code'];
                    $data=array(
                        'order_sn'=>$v['order_sn'],
                        'out_order_sn'=>$v['out_order_sn'],
                        'paymoney'=>$tradeMoney,
                        'pay_time'=>$v['pay_time'],
                        'status'=>$v['status']
                    );
                    $business = D('business');
                    $where['business_code']=$v['business_code'];
                    $businessinfo=$business->where($where)->find();
                    if(empty($businessinfo)){
                        $this->ajaxReturn('error40003','商户未启用!',0);
                    }
                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                    $res =$this->https_post_kfs($url,$data);
                    file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方订单数据".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl.txt',print_r($v,true).PHP_EOL,FILE_APPEND);
                    if($res == 'success'){
                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回成功".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>1,'callback_num'=>1,'callback_time'=>time()));

                    }else{
                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回失败".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>time()));
                    }
                }

            }
        }else{
            $data['status']= 0;
            $data['msg']='蛇皮让你蛇皮!';
            echo json_encode($data);
        }


    }
    /**
     * 第二次异步回调
     */
    public function sfpushsecond(){
        $key = $_GET['key'];
        if($key == '1bf5b073e8e060b89db0b8aed668eec2'){
            $Order=D('Order');
            $orderinfo =$Order->where(array('status'=>1,'callback_status'=>0,'callback_num'=>1))->select();
            if($orderinfo){
                foreach ($orderinfo as $k=>$v){
                    $url=$v['notifyUrl'];
                    $user_id = $v['user_id'];
                    $tradeMoney = $v['tradeMoney'] ;
                    $erweima_id =$v['erweima_id'];
                    $payType =$v['payType'];
                    $business_code =$v['business_code'];
                    $data=array(
                        'order_sn'=>$v['order_sn'],
                        'out_order_sn'=>$v['out_order_sn'],
                        'paymoney'=>$tradeMoney,
                        'pay_time'=>$v['pay_time'],
                        'status'=>$v['status']
                    );
                    $business = D('business');
                    $where['business_code']=$v['business_code'];
                    $businessinfo=$business->where($where)->find();
                    if(empty($businessinfo)){
                        $this->ajaxReturn('error40003','商户未启用!',0);
                    }
                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                    $res =$this->https_post_kfs($url,$data);
                    file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方订单数据".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl.txt',print_r($v,true),FILE_APPEND);
                    if($res == 'success'){
                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回成功".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0,'callback_num'=>1))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>1,'callback_num'=>2,'callback_time'=>time()));
                    }else{
                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回失败".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0,'callback_num'=>1))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>0,'callback_num'=>2,'callback_time'=>time()));

                    }
                }

            }
        }else{
            $data['status']= 0;
            $data['msg']='蛇皮让你蛇皮!';
            echo json_encode($data);
        }

    }
    /**
     * 第三次异步回调
     */
    public function sfpushthird(){
        $key = $_GET['key'];
        if($key == 'caeaa34417150c16d3ab950be8ed08d7'){
            $Order=D('Order');
            $orderinfo =$Order->where(array('status'=>1,'callback_status'=>0,'callback_num'=>2))->select();
            if($orderinfo){
                foreach ($orderinfo as $k=>$v){
                    $url=$v['notifyUrl'];
                    $user_id = $v['user_id'];
                    $tradeMoney = $v['tradeMoney'] ;
                    $erweima_id =$v['erweima_id'];
                    $payType =$v['payType'];
                    $business_code =$v['business_code'];
                    $data=array(
                        'order_sn'=>$v['order_sn'],
                        'out_order_sn'=>$v['out_order_sn'],
                        'paymoney'=>$tradeMoney,
                        'pay_time'=>$v['pay_time'],
                        'status'=>$v['status']
                    );
                    $business = D('business');
                    $where['business_code']=$v['business_code'];
                    $businessinfo=$business->where($where)->find();
                    if(empty($businessinfo)){
                        $this->ajaxReturn('error40003','商户号不存在!',0);
                    }
                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                    $res =$this->https_post_kfs($url,$data);
                    file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方订单数据".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl.txt',print_r($v,true),FILE_APPEND);
                    if($res == 'success'){
                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回成功".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0,'callback_num'=>2))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>1,'callback_num'=>3,'callback_time'=>time()));
                    }else{
                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回失败".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0,'callback_num'=>2))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>2,'callback_num'=>3,'callback_time'=>time()));
                    }
                }
            }
        }else{
            $data['status']= 0;
            $data['msg']='蛇皮让你蛇皮!';
            echo json_encode($data);
        }

    }

    /**
     * 订单5分钟更改为过期
     */
    public function setstale(){
        $bj_time = time() - 300;
        $Order=D('Order');
        if($orderinfo = $Order->where(array('status'=>0,'creatime'=>array('LT',$bj_time)))->select()){
            $Order->where(array('status'=>0,'creatime'=>array('LT',$bj_time)))->field('status')->save(array('status'=>2));
            foreach ($orderinfo as $k=>$v){
                $user_id =$v['user_id'];
                $money = $v['tradeMoney']/100;
                $erweima_id =$v['erweima_id'];
                D("Users")->enterlist($user_id,$money,$erweima_id);
            }
        }
    }

    /**
     * 过期订单3个小时之后解冻并返回跑分 更改订单为订单取消
     */
    public function orderunfreeze(){
        $bj_time = time() - 10800 ;
        $Order=D('Order');
        if($orderinfo = $Order->where(array('status'=>2,'dj_status'=>0,'creatime'=>array('LT',$bj_time)))->select()){
            $Order->where(array('status'=>2,'dj_status'=>0,'creatime'=>array('LT',$bj_time)))->field('status')->save(array('status'=>3));
            foreach ($orderinfo as $k=>$v){
                $order_id = $v['id'];
                $user_id = $v['user_id'];
                $payType = $v['payType'];
                $data=array(
                    'user_id'=>$v['user_id'],
                    'score'=>$v['tradeMoney'],
                    'erweima_id'=>$v['erweima_id'],
                    'business_code'=>$v['business_code'],
                    'out_uid'=>$v['out_uid'],
                    'status'=>4,
                    'payType'=>$v['payType'],
                    'remark'=>'资金解冻',
                    'creatime'=>time()
                );
                D('Account_log')->add($data);
                D('Order')->where(array('id'=>$order_id,'user_id'=>$user_id,'payType'=>$payType))->field('dj_status')->save(array('dj_status'=>1));
            }
        }
    }

    /**
     * 第一次过期订单异步回调
     */
    public function qxpushfirst(){
        $key = $_GET['key'];
        if($key == 'caeaa34417150c16d3ab950be8ed08d7'){
            $Order=D('Order');
            $orderinfo =$Order->where(array('status'=>2,'callback_status'=>0,'callback_num'=>0))->select();
            if($orderinfo){
                foreach ($orderinfo as $k=>$v){
                    $url=$v['notifyUrl'];
                    $user_id = $v['user_id'];
                    $tradeMoney = $v['tradeMoney'] ;
                    $erweima_id =$v['erweima_id'];
                    $data=array(
                        'order_sn'=>$v['order_sn'],
                        'out_order_sn'=>$v['out_order_sn'],
                        'paymoney'=>$tradeMoney,
                        'pay_time'=>$v['pay_time'],
                        'status'=>$v['status']
                    );
                    $business = D('business');
                    $where['business_code']=$v['business_code'];
                    $businessinfo=$business->where($where)->find();
                    if(empty($businessinfo)){
                        $this->ajaxReturn('error40003','商户号不存在!',0);
                    }
                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                    $res =$this->https_post_kfs($url,$data);
                    file_put_contents('./gqnotifyUrl.txt',"~~~~~~~~~~~~~~~第三方过期订单数据".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./gqnotifyUrl.txt',print_r($v,true),FILE_APPEND);
                    if($res == 'success'){
                        file_put_contents('./gqnotifyUrl.txt',"~~~~~~~~~~~~~~~第三方过期订单回调返回成功".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./gqnotifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>2,'callback_status'=>0,'callback_num'=>0))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>1,'callback_num'=>1,'callback_time'=>time()));
                    }else{
                        file_put_contents('./gqnotifyUrl.txt',"~~~~~~~~~~~~~~~第三方过期订单回调返回失败".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./gqnotifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>2,'callback_status'=>0,'callback_num'=>0))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>time()));
                    }
                }
            }
        }else{
            $data['status']= 0;
            $data['msg']='蛇皮让你蛇皮!';
            echo json_encode($data);
        }

    }
    /**
     * 第二次过期订单异步回调
     */
    public function qxpushsecond(){
        $key = $_GET['key'];
        if($key == 'caeaa34417150c16d3ab950be8ed08d7'){
            $Order=D('Order');
            $orderinfo =$Order->where(array('status'=>2,'callback_status'=>0,'callback_num'=>1))->select();
            if($orderinfo){
                foreach ($orderinfo as $k=>$v){
                    $url=$v['notifyUrl'];
                    $user_id = $v['user_id'];
                    $tradeMoney = $v['tradeMoney'] ;
                    $erweima_id =$v['erweima_id'];
                    $data=array(
                        'order_sn'=>$v['order_sn'],
                        'out_order_sn'=>$v['out_order_sn'],
                        'paymoney'=>$tradeMoney,
                        'pay_time'=>$v['pay_time'],
                        'status'=>$v['status']
                    );
                    $business = D('business');
                    $where['business_code']=$v['business_code'];
                    $businessinfo=$business->where($where)->find();
                    if(empty($businessinfo)){
                        $this->ajaxReturn('error40003','商户号不存在!',0);
                    }
                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                    $res =$this->https_post_kfs($url,$data);
                    file_put_contents('./gqnotifyUrl.txt',"~~~~~~~~~~~~~~~第三方过期订单数据".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./gqnotifyUrl.txt',print_r($v,true),FILE_APPEND);
                    if($res == 'success'){
                        file_put_contents('./gqnotifyUrl.txt',"~~~~~~~~~~~~~~~第三方过期订单回调返回成功".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./gqnotifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>2,'callback_status'=>0,'callback_num'=>1))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>1,'callback_num'=>2,'callback_time'=>time()));
                    }else{
                        file_put_contents('./gqnotifyUrl.txt',"~~~~~~~~~~~~~~~第三方过期订单回调返回失败".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./gqnotifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>2,'callback_status'=>0,'callback_num'=>1))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>0,'callback_num'=>2,'callback_time'=>time()));
                    }
                }
            }
        }else{
            $data['status']= 0;
            $data['msg']='蛇皮让你蛇皮!';
            echo json_encode($data);
        }

    }
    /**
     * 第三次过期订单异步回调
     */
    public function qxpushthird(){
        $key = $_GET['key'];
        if($key == 'caeaa34417150c16d3ab950be8ed08d7'){
            $Order=D('Order');
            $orderinfo =$Order->where(array('status'=>2,'callback_status'=>0,'callback_num'=>2))->select();
            if($orderinfo){
                foreach ($orderinfo as $k=>$v){
                    $url=$v['notifyUrl'];
                    $user_id = $v['user_id'];
                    $tradeMoney = $v['tradeMoney'] ;
                    $erweima_id =$v['erweima_id'];
                    $data=array(
                        'order_sn'=>$v['order_sn'],
                        'out_order_sn'=>$v['out_order_sn'],
                        'paymoney'=>$tradeMoney,
                        'pay_time'=>$v['pay_time'],
                        'status'=>$v['status']
                    );
                    $business = D('business');
                    $where['business_code']=$v['business_code'];
                    $businessinfo=$business->where($where)->find();
                    if(empty($businessinfo)){
                        $this->ajaxReturn('error40003','商户号不存在!',0);
                    }
                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                    $res =$this->https_post_kfs($url,$data);
                    file_put_contents('./gqnotifyUrl.txt',"~~~~~~~~~~~~~~~第三方过期订单数据".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./gqnotifyUrl.txt',print_r($v,true),FILE_APPEND);
                    if($res == 'success'){
                        file_put_contents('./gqnotifyUrl.txt',"~~~~~~~~~~~~~~~第三方过期订单回调返回成功".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./gqnotifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>2,'callback_status'=>0,'callback_num'=>2))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>1,'callback_num'=>3,'callback_time'=>time()));
                    }else{
                        file_put_contents('./gqnotifyUrl.txt',"~~~~~~~~~~~~~~~第三方过期订单回调返回失败".date('Y/m/d h:i:s')."~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./gqnotifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>2,'callback_status'=>0,'callback_num'=>2))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>2,'callback_num'=>3,'callback_time'=>time()));
                    }
                }
            }
        }else{
            $data['status']= 0;
            $data['msg']='蛇皮让你蛇皮!';
            echo json_encode($data);
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
}
?>