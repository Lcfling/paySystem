<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 17:20
 */
class OrderjdAction extends CommonAction{

    /**
     * 接单订单列表
     */
    public function orderjd_list(){

        if($this->isPost()){
            $user_id =$this->uid;//用户id
//            $recmoney =D('Account')->getdaybrokerage($user_id,3);
//            $sucmoney =D('Account')->getdaybrokerage($user_id,2);



            $bg_time = strtotime(TODAY);
            $recmoney =  D('Order')->where(array('user_id' => $user_id, 'creatime' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->sum('tradeMoney');
            $sucmoney =  D('Order')->where(array('user_id' => $user_id, 'status' =>1 , 'creatime' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->sum('tradeMoney');




            $list = D('Order')->where(array('user_id'=>$user_id))->order('creatime desc')->field('id,payMoney,erweima_id,order_sn,sk_status,tradeMoney,payType,status,creatime')->select();
            foreach ($list as $k =>&$v){
                $v['tradeMoney']= $v['tradeMoney']/100;
                $v['payMoney']= $v['payMoney']/100;
                $v['creatime']= date('Y/m/d H:i:s',$v['creatime']);
                $v['name'] = $this->getname($v['erweima_id']);
            }
            $data = array(
                'recmoney'=>$recmoney/100,
                'sucmoney'=>$sucmoney/100,
                'list'=>$list
            );
            $this->ajaxReturn($data,'请求成功!',1);
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    /**
     * 码商手动收款
     */
    public function savesk_status(){
        if($this->isPost()){
            $user_id =$this->uid;//用户id
            $order_id =$_POST['order_id'];

//            if(D('Order')->where(array('user_id'=>$user_id,'id'=>$order_id,'status'=>0))->find()){
//                $this->ajaxReturn('','当前无法操作,请5分钟之后进行操作!',0);
//            }
            if(!$orderlist =D('Order')->where(array('user_id'=>$user_id,'id'=>$order_id,'sk_status'=>0))->find()){

                $this->ajaxReturn('','订单不存在!',0);

            }

            if(D('Order')->where(array('user_id'=>$user_id,'id'=>$order_id,'sk_status'=>2))->find()){
                $this->ajaxReturn('','系统回调成功,您已收款成功,请刷新当前页面!',0);
            }

            if(D('Order')->where(array('user_id'=>$user_id,'id'=>$order_id,'sk_status'=>1))->find()){
                $this->ajaxReturn('','请勿重复点击!',0);
            }

            $orderstatus = D('Order')->where(array('user_id'=>$user_id,'id'=>$order_id,'sk_status'=>0))->field('sk_status')->save(array('sk_status'=>1));


           $order_info=D('Order')->where(array('user_id'=>$user_id,'id'=>$order_id))->find();

            if($orderstatus){
                $erweima_id =$orderlist['erweima_id'];
                $payType =$orderlist['payType'];
                //存入缓存
                D("Users")->Genericlist($user_id,$payType,$erweima_id);
                // 未超时
                if (time() - 10800 <$order_info['creatime'] ){

                        $this->budan($order_info['order_sn']);
                }else{
                    //超时
                    $this->csbudan($order_info['order_sn']);
                }


                $this->ajaxReturn('','手动收款成功!',1);
            }else{
                $this->ajaxReturn('','手动收款失败!',0);
            }
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }

    }


    private function getname($erweima_id){
       $name = D('Erweima_generic')->where(array('id'=>$erweima_id))->getField('name');
       return $name;
    }

    //  补单
    public function budan($order_sn){

        //查询订单信息
        $order_sn_info=D("order")->where(array("order_sn"=>$order_sn))->find();


        $data['score']=$order_sn_info['tradeMoney'];
        $data['user_id'] = $order_sn_info['user_id'];
        $data['status']=4;
        $data['erweima_id']=$order_sn_info['erweima_id'];
        $data['business_code']=$order_sn_info['business_code'];
        $data['remark']="手动资金解冻";
        $data['creatime']=time();
        D("Account_log")->add($data);


        $info['score']=-$order_sn_info['tradeMoney'];
        $info['user_id'] = $order_sn_info['user_id'];
        $info['status']=2;
        $info['erweima_id']=$order_sn_info['erweima_id'];
        $info['business_code']=$order_sn_info['business_code'];
        $info['remark']="手动资金扣除";
        $info['creatime']=time();
        D("Account_log")->add($info);

        D("order")->where(array("order_sn"=>$order_sn))->save(array("status"=>1,"is_shoudong"=>1,"dj_status"=>2,"pay_time"=>time()));

        $userinfo=D("Users")->where(array("user_id"=>$order_sn_info['user_id']))->find();


        D('Rebate')->fy($order_sn_info['id'],$order_sn_info['tradeMoney'],$order_sn_info['user_id'],$userinfo['rate'],$userinfo['pid'],$order_sn_info['erweima_id'],$order_sn_info['business_code'],$order_sn_info["out_uid"]);


        D("erweima_generic")->where(array("id"=>$order_sn_info['erweima_id']))->setDec('limits',$order_sn_info['tradeMoney']/100); // 用户的积分减5

        $this->sfpushfirst($order_sn);

    }

    //  超时补单
    public function csbudan($order_sn){

        //查询订单信息
        $order_sn_info=D("order")->where(array("order_sn"=>$order_sn))->find();



        $info['score']=-$order_sn_info['tradeMoney'];
        $info['user_id'] = $order_sn_info['user_id'];
        $info['status']=2;
        $info['erweima_id']=$order_sn_info['erweima_id'];
        $info['business_code']=$order_sn_info['business_code'];
        $info['remark']="手动资金扣除";
        $info['creatime']=time();
        D("Account_log")->add($info);

        D("order")->where(array("order_sn"=>$order_sn))->save(array("status"=>1,"is_shoudong"=>1,"dj_status"=>2,"pay_time"=>time()));

        $userinfo=D("Users")->where(array("user_id"=>$order_sn_info['user_id']))->find();


        D('Rebate')->fy($order_sn_info['id'],$order_sn_info['tradeMoney'],$order_sn_info['user_id'],$userinfo['rate'],$userinfo['pid'],$order_sn_info['erweima_id'],$order_sn_info['business_code'],$order_sn_info["out_uid"]);
        D("erweima_generic")->where(array("id"=>$order_sn_info['erweima_id']))->setDec('limits',$order_sn_info['tradeMoney']/100); // 用户的积分减5

        $this->sfpushfirst($order_sn);

    }

    /**
     *第一次 异步回调
     */
    public function sfpushfirst($order_sn){

        $key = "36cae679f8cb296d69be4f27bd8cc3d6";
        if($key == '36cae679f8cb296d69be4f27bd8cc3d6'){
            $Order=D('Order');
            $orderinfo =$Order->where(array("order_sn"=>$order_sn))->select();
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
                        'status'=>$v['status']
                    );
                    $business = D('business');
                    $where['business_code']=$v['business_code'];
                    $businessinfo=$business->where($where)->find();
                    if(empty($businessinfo)){
                        $this->ajaxReturn('error40003','商户号不存在!',0);
                      //  $this->baoSuccess("商户号不存在");
                    }

                    $data['sign']=$this->getSignK($data,$businessinfo['accessKey']);
                    $res =$this->https_post_kfs($url,$data);
                    file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方订单数据~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                    file_put_contents('./notifyUrl_sd.txt',print_r($v,true).PHP_EOL,FILE_APPEND);
                    if($res == 'success'){
                        file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方回调返回成功~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl_sd.txt',print_r($res,true).PHP_EOL,FILE_APPEND);

                        $Order->where(array('id'=>$v['id']))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>1,'callback_num'=>1,'callback_time'=>time()));
                        //  $res =D("Users")->enterlist($user_id,$tradeMoney/100,$erweima_id);

                        //更改二维码状态
                        D("erweima_generic")->where(array("user_id"=>$user_id,"id"=>$erweima_id))->save(array("use_status"=>0));

                        $this->ajaxReturn('','回调成功!');

                        //$this->baoSuccess("回调成功!".$order_sn,U('transaction/lists'));

                    }else{
                        file_put_contents('./notifyUrl_sd.txt',"~~~~~~~~~~~~~~~第三方回调返回失败~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl_sd.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>time()));
                        //  $res =D("Users")->enterlist($user_id,$tradeMoney/100,$erweima_id);
                        //更改二维码状态
                        D("erweima_generic")->where(array("user_id"=>$user_id,"id"=>$erweima_id))->save(array("use_status"=>0));

                        $this->ajaxReturn('','回调成功!第三方返回失败');
                      //  $this->baoError("回调成功!第三方返回失败!");
                    }
                }

            }else{
                $this->baoError('订单不存在');
            }
        }else{

            $this->baoError('蛇皮让你蛇皮');
        }
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