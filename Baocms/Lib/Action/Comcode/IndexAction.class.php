<?PHP

class IndexAction extends Action
{

    public function getcode(){
        for($i=20;$i<40;$i++){
            Cac()->delete('lkcode'.$i);
            $chanum[] =Cac()->lRange('lkcode'.$i,0,-1);
        }
        print_r($chanum);
    }
    public function index()
    {

        $data['out_uid']='100000003217';
        $codeType =$_POST['codeType'];
        $data["payType"] = 1;//支付方式  1微信  2支付宝
        $data["codeType"] = $codeType;//二维码类型  1 固码  2 通用码
        $data["out_order_sn"] = $_POST['out_order_sn'];//订单号
        $data["tradeMoney"] = $_POST['money'];
        $data["notifyUrl"] = "http://".$_SERVER['HTTP_HOST']."/code/orderym/notifyUrl";//回调
        $key = $_POST['accessKey'];
        $data["sign"] = $this->getSign($data,$key);//签名
        $data["business_code"] = $_POST['business_code'];
        if($codeType == 1){
            $url = 'http://'.$_SERVER['HTTP_HOST'].'/app/orderym/kuaifupay';
        }else{
            $url = 'http://'.$_SERVER['HTTP_HOST'].'/code/orderym/kuaifupay';
        }
        $res = $this->https_post_kf($url,$data);
        print_r($res);exit();
    }

    public function notifyUrl(){

        $retrun_datas =$_POST;
        $retrun_sign=$retrun_datas['sign'];//签名值
        unset($retrun_datas['sign']);
        $key = '3a55e1588ce31326f56fcca97a20626f';
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
?>