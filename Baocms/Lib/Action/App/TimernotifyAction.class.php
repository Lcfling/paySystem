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
                    $data=array(
                        'order_sn'=>$v['order_sn'],
                        'out_order_sn'=>$v['out_order_sn'],
                        'paymoney'=>$v['payMoney'],
                        'pay_time'=>$v['pay_time'],
                    );
                    $business = D('business');
                    $where['business_code']=$v['business_code'];
                    $businessinfo=$business->where($where)->find();
                    if(empty($businessinfo)){
                        $this->ajaxReturn('error40003','商户号不存在!',0);
                    }
                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                    $res =$this->https_post_kfs($url,$data);
                    file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方订单数据~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl.txt',print_r($v,true).PHP_EOL,FILE_APPEND);
                    if($res == 'success'){
                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回成功~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>1,'callback_num'=>1,'callback_time'=>time()));
                        $res =D("Users")->enterlist($user_id,$tradeMoney,$erweima_id);
                        $list =Cac()->lRange('erweimas1111',0,-1);
                        print_r($res);
                        print_r($list);
                        print_r($user_id.'-'.$tradeMoney.'-'.$erweima_id);
                    }else{
                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回失败~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
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
                    $data=array(
                        'order_sn'=>$v['order_sn'],
                        'out_order_sn'=>$v['out_order_sn'],
                        'paymoney'=>$v['payMoney'],
                        'pay_time'=>$v['pay_time'],
                    );
                    $business = D('business');
                    $where['business_code']=$v['business_code'];
                    $businessinfo=$business->where($where)->find();
                    if(empty($businessinfo)){
                        $this->ajaxReturn('error40003','商户号不存在!',0);
                    }
                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                    $res =$this->https_post_kfs($url,$data);
                    file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方订单数据~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl.txt',print_r($v,true),FILE_APPEND);
                    if($res == 'success'){
                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回成功~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0,'callback_num'=>1))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>1,'callback_num'=>2,'callback_time'=>time()));
                        D("Users")->enterlist($user_id,$tradeMoney,$erweima_id);
                    }else{
                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回失败~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
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
                    $data=array(
                        'order_sn'=>$v['order_sn'],
                        'out_order_sn'=>$v['out_order_sn'],
                        'paymoney'=>$v['payMoney'],
                        'pay_time'=>$v['pay_time'],
                    );
                    $business = D('business');
                    $where['business_code']=$v['business_code'];
                    $businessinfo=$business->where($where)->find();
                    if(empty($businessinfo)){
                        $this->ajaxReturn('error40003','商户号不存在!',0);
                    }
                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                    $res =$this->https_post_kfs($url,$data);
                    file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方订单数据~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl.txt',print_r($v,true),FILE_APPEND);
                    if($res == 'success'){
                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回成功~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0,'callback_num'=>2))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>1,'callback_num'=>3,'callback_time'=>time()));
                        D("Users")->enterlist($user_id,$tradeMoney,$erweima_id);
                    }else{
                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回失败~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
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

    public function setstale(){
        $bg_time = strtotime(TODAY);
        $bj_time = $bg_time - 300;
        $Order=D('Order');
        $res =$Order->where(array('status'=>0,'creatime'=>array('LT',$bj_time)))->field('status')->save(array('status'=>2));
        print_r($res);
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
}
?>