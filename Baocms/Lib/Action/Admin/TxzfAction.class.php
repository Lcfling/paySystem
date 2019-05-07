<?php
class TxzfAction extends CommonAction
{



    //查余额
    public function index()
    {

        $datas["agentNo"]=100026;
        $sign=$this->getSign2($datas);
        $url="http://qrcode.linwx420.com:8088/100026/agentAmt/".$sign;
        //  print_r($list);
        $result = $this->https_post_kfs($url,$datas);

        echo $result;
        $res=json_decode($result,true);

        print_r($res);
        echo "当前余额：".$res["retData"]/100 ."元";

    }
    public function txall(){
        $datas["agentNo"]=100026;
        $sign=$this->getSign2($datas);
        $url="http://qrcode.linwx420.com:8088/100026/agentAmt/".$sign;
        //  print_r($list);
        $result = $this->https_post_kfs($url,$datas);
        $res=json_decode($result,true);

        $app["agentNo"]=100026;
        $app["orderNo"]=time().rand_string(6,1);
        $app["bizCode"]=4237;
        $app["payAmt"]=$res["retData"]-10000;
        $app["toCardNo"]="6216608000004317642";
        $app["toCardIdentity"]="410522199110289339";
        $app["toCardMobile"]="18625498727";
        $app["toCardHolder"]="耿凯祥";
        $app["toCardSubBankCode"]="";
        $app["toCardSubBankName"]="";


        $sign2=$this->getSign2($app);
        $url2="http://qrcode.linwx420.com:8088/100026/payment/".$sign2;
        $result2 = $this->https_post_kfs($url2,$app);
        print_r(json_decode($result2));
    }


    public function https_post_kfs($url,$data)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno'.curl_error($curl);
        }
        curl_close($curl);
        return $result;
    }
    public function getSign2($Obj){

        foreach ($Obj as $k => $v)
        {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String =$this->formatBizQueryParaMap2($Parameters, false);
        //echo '【string1】'.$String.'</br>';

        //签名步骤二：在string后加入KEY
        $String ='01e5d8a4-e7d2-4dae-9d33-f2b2a077fccd'.$String.'01e5d8a4-e7d2-4dae-9d33-f2b2a077fccd';
        //echo "【string2】".$String."</br>";


        //签名步骤三：MD5加密

        $String = md5($String);

        //echo "【string3】 ".$String."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }
    public function formatBizQueryParaMap2($paraMap, $urlencode){
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v)
        {
            if($urlencode)
            {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k  . $v ;
        }

        if (strlen($buff) > 0)
        {
            $reqPar = substr($buff, 0, strlen($buff));
        }
        return $reqPar;
    }
}