<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/30
 * Time: 11:04
 */
class WxzftyAction extends Action{


    public function index()
    {
        if($this->isPost()){

            $data['out_uid']='0';
            $data["payType"] = $_POST['payType'];//支付方式  1微信  2支付宝
            $data["out_order_sn"] = $_POST['out_order_sn'];//订单号
            $data["tradeMoney"] = $_POST['money'];//
            $data["notifyUrl"] = 'http://'.$_SERVER['HTTP_HOST']."/app/index/notifyUrl";//回调
            $key = '6610330216b45c1f68e59a245e9b9822';
            $data["sign"] = $this->getSign($data,$key);//签名
            $data["business_code"] = 1;
            $url = 'http://'.$_SERVER['HTTP_HOST'].'/app/Orderymty/kuaifupay';
            $res = $this->https_post_kf($url,$data);
            echo $res;
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }

    }

    public function notifyUrl(){

        $retrun_datas =$_POST;
        $retrun_sign=$retrun_datas['sign'];//签名值
        unset($retrun_datas['sign']);
        $key = '6610330216b45c1f68e59a245e9b9822';
        $sign =$this->getSign($retrun_datas,$key);
        if($retrun_sign==$sign){
            echo "success";
            file_put_contents('./notifyUrl.txt',print_r($retrun_datas,true).PHP_EOL,FILE_APPEND);
        }else{
            echo "fail";
            file_put_contents('./notifyUrl.txt',print_r($retrun_datas,true).PHP_EOL,FILE_APPEND);
            file_put_contents('./notifyUrl.txt','sign-'.$sign.PHP_EOL,FILE_APPEND);
            file_put_contents('./notifyUrl.txt','retrun_sign-'.$retrun_sign.PHP_EOL,FILE_APPEND);
        }
    }

    private function getSign($Obj,$key)
    {

        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
//        echo '【string1】' . $String . '</br>';
        //签名步骤二：在string后加入KEY
        $String = $String . "&accessKey=" . $key;
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