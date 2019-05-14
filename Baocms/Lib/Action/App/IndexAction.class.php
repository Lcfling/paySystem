<?PHP

class IndexAction extends Action
{


    public function index()
    {
        $data['out_uid']='123';
        $data["payType"] = 1;//支付方式  1微信  2支付宝
        $data["out_order_sn"] = "n1909";//订单号
        $data["tradeMoney"] = "4";//
        $data["notifyUrl"] = "http://91pai.webziti.com/app/index/notifyUrl";//回调
        $data["sign"] = $this->getSign($data);//签名
        $data["business_code"] = '30001';//商户ID
        $url = 'http://91pai.webziti.com/app/orderym/kuaifupay';
        $res = $this->https_post_kf($url,$data);
        print_r($res);exit();
    }

    public function notifyUrl(){
//        $data=array(
//            'order_sn'=>$orderinfo['order_sn'],
//            'out_order_sn'=>$orderinfo['out_order_sn'],
//            'tradeMoney'=>$orderinfo['tradeMoney'],
//            'pay_time'=>$pay_time,
//        );
        $retrun_datas =$_POST;
        $retrun_sign=$retrun_datas['sign'];//签名值
        unset($retrun_datas['sign']);
        $sign =$this->getSign($retrun_datas);
        if($retrun_sign==$sign){
            file_put_contents('./notifyUrl.txt',print_r($retrun_datas,true),FILE_APPEND);
        }else{
            file_put_contents('./notifyUrl.txt',print_r($retrun_datas,true),FILE_APPEND);
            file_put_contents('./notifyUrl.txt','sign'.PHP_EOL.$sign,FILE_APPEND);
            file_put_contents('./notifyUrl.txt','retrun_sign'.PHP_EOL.$retrun_sign,FILE_APPEND);
        }
    }

    private function getSign($Obj)
    {

        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
//        echo '【string1】' . $String . '</br>';
        //签名步骤二：在string后加入KEY
        $String = $String . "&accessKey=" . '7a50b63265f5db56bf184e4320a70f8a';
        //echo "【string2】".$String."</br>";
        //签名步骤三：MD5加密
        $String = md5($String);
        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    private function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    private function https_post_kf($url, $data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno' . curl_error($curl);
        }
        curl_close($curl);
        return $result;
    }
}
?>