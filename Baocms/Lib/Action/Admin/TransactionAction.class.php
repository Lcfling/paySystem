<?php
class TransactionAction extends CommonAction {




    public function success() {
        $brandid=$_SESSION['brandid'];

        if ($this->isPost()) {
            $sta=$_POST['bg_date'];
            $end=$_POST['end_date'];
            $todaytime=strtotime($sta);
            $today_end=strtotime($end);
        }else{

            if($bg_date= $this->_param('bg_date','htmlspecialchars')){
                $todaytime =strtotime($bg_date);

            }else{
                $todaytime=mktime(0,0,0,date('m'),date('d'),date('Y'));

            }

            if($end_date= $this->_param('end_date','htmlspecialchars')){
                $today_end =strtotime($end_date);

            }else{
                $today_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'));
            }
            $map['creattime'] = array('between', array($todaytime, $today_end));
        }


        $User = D('Payord');
        import('ORG.Util.Page'); // 导入分页类
        $map = array();
        $map['brandid']=$brandid;
        $map['sta']=1;

        if($ordid = $this->_param('ordid','htmlspecialchars')){
            $map['orderNo'] =$ordid;
        }else{
            $map['creattime'] =array('between',array($todaytime,$today_end));
        }

        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$val){
            $val['pay_money'] =$val['pay_money'] /100;
            $val['payAmt'] =$val['payAmt'] /100;
            $val['money'] =$val['money'] /100;
            $val['rate'] =$val['rate'] /100;
            if ($val['ifsuccess'] == 1){
                $val['ifsuccess']="回调成功";
            }else{
                $val['ifsuccess']="回调失败";
            }


            $list[$k] = $val;
        }

        $pay_money=D("Payord")->where($map)->field('sum(money) as money')->select();
        $counts['money']=$pay_money[0]['money']/100;


        // print_r($list);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('todaytime', $todaytime); // 赋值数据
        $this->assign('today_end', $today_end); // 赋值数据
        $this->assign('counts',$counts);
        $this->assign('ordid',$ordid);
        $this->display(); // 输出模板

    }

    public function failure() {
        $brandid=$_SESSION['brandid'];

        if ($this->isPost()) {
            $sta=$_POST['bg_date'];
            $end=$_POST['end_date'];
            $todaytime=strtotime($sta);
            $today_end=strtotime($end);
        }else{

            if($bg_date= $this->_param('bg_date','htmlspecialchars')){
                $todaytime =strtotime($bg_date);

            }else{
                $todaytime=mktime(0,0,0,date('m'),date('d'),date('Y'));

            }

            if($end_date= $this->_param('end_date','htmlspecialchars')){
                $today_end =strtotime($end_date);

            }else{
                $today_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'));

            }
            $map['creattime'] = array('between', array($todaytime, $today_end));
        }


        $User = D('Payord');
        import('ORG.Util.Page'); // 导入分页类
        $map = array();
        $map['brandid']=$brandid;
        $map['sta']=0;

        if($ordid = $this->_param('ordid','htmlspecialchars')){
            $map['orderNo'] =$ordid;
        }else{
            $map['creattime'] =array('between',array($todaytime,$today_end));
        }

        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$val){
            $val['pay_money'] =$val['pay_money']/100;
            $val['payAmt'] =$val['payAmt'] /100;
            $val['money'] =$val['money'] /100;
            $val['rate'] =$val['rate'] /100;
            if ($val['ifsuccess'] == 1){
                $val['ifsuccess']="回调成功";
            }else{
                $val['ifsuccess']="回调失败";
            }
            $list[$k] = $val;
        }
        $pay_money=D("Payord")->where($map)->field('sum(pay_money) as pay_money')->select();
        $counts['pay_money']=$pay_money[0]['pay_money']/100;
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('todaytime', $todaytime); // 赋值数据
        $this->assign('today_end', $today_end); // 赋值数据
        $this->assign('ordid',$ordid);
        $this->assign('counts',$counts);
        $this->display(); // 输出模板

    }


    public function lists(){
        if ($this->isPost()) {

            $sta=$_POST['bg_date'];
            $end=$_POST['end_date'];
            $todaytime=strtotime($sta);
            $today_end=strtotime($end);
            $map['creatime'] =array('between',array($todaytime,$today_end));

        }else{

            $sta=$_GET['bg_date'];
            $end=$_GET['end_date'];
            $todaytime=strtotime($sta);
            $today_end=strtotime($end);
            if(empty($todaytime)){
                $todaytime=mktime(0,0,0,date('m'),date('d'),date('Y'));

            }
            if(empty($today_end)){
                $today_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
                $map['creatime'] =array('between',array($todaytime,$today_end));
            }
        }

        $User = D('Order');
        import('ORG.Util.Pageam'); // 导入分页类
        $map = array();

        if($_REQUEST["status"]=="null"||!isset($_REQUEST["status"])){
            unset($_REQUEST["status"]);
        }else{
            $this->assign('status', $_REQUEST['status']); // 赋值数据
            $map['status']=$_REQUEST["status"];
        }

        if($_REQUEST['business_code']){
            $map['business_code']=$_REQUEST['business_code'];
        }

        if($_REQUEST['user_id']){
            $map['user_id']=$_REQUEST['user_id'];
        }

        if($_REQUEST['out_order_sn']){
            $map['out_order_sn']=$_REQUEST['out_order_sn'];
        }



        if($_POST['exportExcel']=="yes"){

            $exportData = $User->where($map)->select();
            $title=array('商户号','订单号','交易额(元)','实付金额(元)','交易状态','回调状态,','创建时间','平台订单号','完成时间');
            foreach($exportData as $v){
                $temp=array();
                $temp[]=$v['business_code'];
                $temp[]=$v['out_order_sn'];
                $temp[]=$v['tradeMoney'];
                $temp[]=$v['payMoney'];
                if($v['status']==1){
                    $temp[]="已支付";
                }else{
                    $temp[]="未支付";
                }

                if ($v['callback_status']==1){
                    $temp[]="成功";
                }else{
                    $temp[]="失败";
                }
                $temp[]=date("Y-m-d H:i:s",$v['creatime']);
                $temp[]=$v['order_sn'];
                $temp[]=date("Y-m-d H:i:s",$v['pay_time']);

//                $business_info=D("Business")->where("business=".$v['business_code'])->find();
//                $temp[]=$business_info['fee'];
//                $temp[]=$business_info['fee']*$v['tradeMoney'];
                $export[]=$temp;
            }
            $this->export($title,$export);
            return;
        }

        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $map2=$map;
        $map2['status']=1;
        $allmoney=$User->where($map2)->field('sum(tradeMoney) as money')->select();
        $allmoney=$allmoney[0]['money']/100;

        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$val){
            $val['payMoney'] =$val['payMoney'] /100;
            $val['tradeMoney'] =$val['tradeMoney'] /100;


            if($val['creattime']){
                $val['creattime']=date("Y-m-d H:i:s",$val['creattime']);
            }
            if($val['pay_time']){
                $val['pay_time']=date("Y-m-d H:i:s",$val['pay_time']);
            }

            $businessinfo=D("business")->where(array("business_code"=>$val["business_code"]))->find();
            $val['fee']=$businessinfo['fee'];
            $val['dbmoney']=$val['tradeMoney']-$val['tradeMoney']*$businessinfo['fee'];
            $list[$k] = $val;
        }
        // print_r($list);
        $this->assign('allmoney', $allmoney); // 赋值数据集
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('todaytime', $todaytime); // 赋值数据
        $this->assign('today_end', $today_end); // 赋值数据

        $this->display(); // 输出模板
    }

    //手动解冻
    public function jiedong($order_sn){

        //查询订单信息
        $order_sn_info=D("order")->where(array("order_sn"=>$order_sn))->find();
        if ($order_sn_info['status']== 1){
            $this->baoSuccess("此订单已支付成功!");
        }

        if ($order_sn_info['dj_status']== 1){
            $this->baoSuccess("此订单已解冻!");
        }

        $dj_status= D("order")->where(array("order_sn"=>$order_sn))->save(array("dj_status"=>1));

        if ($dj_status){
            $obj = D('Account_log');
            $data['score']=$order_sn_info['tradeMoney'];
            $data['user_id'] = $order_sn_info['user_id'];
            $data['status']=1;
            $data['remark']="手动跑分解冻";
            $data['creatime']=time();

            if (false !==$obj->add($data)) {

                $this->baoSuccess('解冻成功', U('transaction/lists'));
            }else{
                $this->baoError('操作失败222');
            }

        }else{
            $this->baoError('操作失败'.$order_sn);
        }





    }





    /**
     *第一次 异步回调
     */
    public function sfpushfirst($out_order_sn){

        $key = "36cae679f8cb296d69be4f27bd8cc3d6";
        if($key == '36cae679f8cb296d69be4f27bd8cc3d6'){
            $Order=D('Order');
            $orderinfo =$Order->where(array("out_order_sn"=>$out_order_sn))->select();
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
//                        $this->ajaxReturn('error40003','商户号不存在!',0);
                        $this->baoSuccess("商户号不存在");
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
                        $this->baoSuccess("回调成功!".$v['id'],U('transaction/lists'));

                    }else{
                        file_put_contents('./notifyUrl.txt',"~~~~~~~~~~~~~~~第三方回调返回失败~~~~~~~~~~~~~~~".PHP_EOL,FILE_APPEND);
                        file_put_contents('./notifyUrl.txt',print_r($res,true).PHP_EOL,FILE_APPEND);
                        $Order->where(array('id'=>$v['id'],'status'=>1,'callback_status'=>0))->field("callback_status,callback_num,callback_time")->save(array('callback_status'=>0,'callback_num'=>1,'callback_time'=>time()));
                        $this->baoError("回调成功!第三方返回失败!");
                    }
                }

            }else{
                $this->baoError('订单不存在');
            }
        }else{

            $this->baoError('蛇皮让你蛇皮');
        }
    }




    private function export($title,$data){
        exportExcel($title, $data, date("Y-m-d H:i:s".time()), './', true);
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

