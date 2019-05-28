<?php
class BusinessAction extends CommonAction {



    public function index(){

        $User = D('Business');
        import('ORG.Util.Pageam'); // 导入分页类
        $map = array('closed'=>array('IN','0,-1'));

        if($business_code = $this->_param('business_code','htmlspecialchars')){

            $map['business_code'] = $business_code;
            $this->assign('business_code',$business_code);
        }
        if($business_code = $this->_param('nickname','htmlspecialchars')){
            $map['nickname'] = $business_code;
            $this->assign('nickname',$business_code);
        }

//        if($_REQUEST["sta"]=="null"){
//            unset($_REQUEST["sta"]);
//        }else{
//            //$this->assign('status', $_REQUEST['status']); // 赋值数据
//            $map['status']=$_REQUEST["status"];
//            $this->assign('status',$_REQUEST["status"]);
//        }


        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('business_code'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        if(!empty($list)){
            foreach ($list as &$v){




                // 收款总额
                $money=D("order")->where(array( "business_code"=>$v['business_code'],"status"=>1))->field("sum(tradeMoney) as money")->select();
                $v['money']=$money[0]['money']/100;

                //实收总额
                $business_info=D("business")->where(array( "business_code"=>$v['business_code']))->find();
                $v['ss_money']=$v['money']-$v['money']*$business_info['fee'];

                //提现总额
                $tx_money=D("business_withdraw")->where(array( "business_code"=>$v['business_code'],"status"=>1))->field("sum(money) as money")->select();
                $v['txmoney']=$tx_money[0]['money']/100;

                //可提余额
                $v['ktmoney']=$v['ss_money']-$v['txmoney'];

                //订单数量
                $sql="select count(*) AS count from zf_order where business_code=".$v['business_code'];
                $countss=D()->query($sql);
                $v['countss']=$countss[0]["count"];
                //成功订单数量
                $sql="select count(*) AS count from zf_order where business_code=".$v['business_code']." and status=1";
                $counts=D()->query($sql);
                $v['counts']=$counts[0]["count"];
                //成功率
                $v['count']=round($v['counts']/$v['countss']*100,2);



            }
        }

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }




    public function businessinfo() {
        $User = D('Business');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed'=>array('IN','0,-1'));

        $business=session('admin.business_code');
        $map['business_code']=$business;



        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('business_code'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();


        $money=D("order")->where(array("business_code"=>$business,"status"=>1))->field("sum(tradeMoney) as money")->select();

        $sql="select count(*) AS count  from zf_order where business_code=".$business;
        $countss=D()->query($sql);

        $sqls="select count(*) AS count  from zf_order where business_code='$business'and status=1";
        $counts=D()->query($sqls);

        $count=$counts[0]["count"]/ $countss[0]["count"];
        $count=round($count,2);

       //   print_r($list);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('money', $money[0]['money']/100); // 赋值数据集
        $this->assign('count', $count*100); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->assign('ranks',D('Userrank')->fetchAll());
        $this->display(); // 输出模板

    }


    public function orderlist() {
        $User = D('Order');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed'=>array('IN','0,-1'));
        $business= session('admin.business_code');
        $map['business_code']=$business;

        if($status= $this->_param('status')){
            if ($status<0){
                $status=0;
            }
            $map['status'] = array('LIKE','%'.$status.'%');

            $this->assign('status',$status);
        }


        if($out_order_sn= $this->_param('out_order_sn')){

            $map['out_order_sn'] = array('LIKE','%'.$out_order_sn.'%');

            $this->assign('out_order_sn',$out_order_sn);
        }



        if($bg_date = $this->_param('bg_date','htmlspecialchars')){


            if($end_date = $this->_param('end_date','htmlspecialchars')){

                $creatime = strtotime($bg_date);//
                $creatimes = strtotime($end_date."23:59:59");//

                $map['creatime'] = array('GT',$creatime,'LT',$creatimes);
                $this->assign('end_date',$end_date);


                $this->assign('bg_date',$bg_date);
            }




            $creatime = strtotime($bg_date);//

            $map['creatime'] = array('GT',$creatime,'LT',$creatime);
            $this->assign('bg_date',$bg_date);


        }

        if($end_date = $this->_param('end_date','htmlspecialchars')){


            $creatimes = strtotime($end_date."23:59:59");//

            $map['creatime'] = array('LT',$creatimes);
            $this->assign('end_date',$end_date);


            $this->assign('bg_date',$bg_date);
        }




        if($_POST['exportExcel']=="yes"){

            $exportData = $User->where($map)->select();
            $title=array('流水号','商家账单号','商家','请求金额(元)','账单金额(元)','请求时间','完成时间','账单状态');
            foreach($exportData as $v){
                $temp=array();
                $temp[]=$v['order_sn'];
                $temp[]=$v['out_order_sn'];
                $temp[]=$v['business_code'];
                $temp[]=$v['tradeMoney'];
                $temp[]=$v['payMoney'];
                $temp[]=date("Y-m-d H:i:s",$v['creatime']);
                $temp[]=date("Y-m-d H:i:s",$v['pay_time']);
                if($v['status']==0){
                    $temp[]="未支付";
                }else  if($v['status']==1){
                    $temp[]="支付成功";
                }else   if($v['status']==2){
                    $temp[]="支付过期";
                }else{
                    $temp[]="支付取消";
                }

                $export[]=$temp;
            }
            $this->export($title,$export);
            return;
        }





        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $userdata=D("business")->where(array("business_code"=>$business))->find();
        $money=D("order")->where(array("business_code"=>$business,"status"=>1))->field("sum(tradeMoney) as money")->select();

        foreach ($list as $k=>$v){
            $list[$k]["tradeMoney"]= $list[$k]["tradeMoney"]/100;
            $list[$k]["payMoney"]=$list[$k]["payMoney"]/100;
            $list[$k]["dzmoney"]=$list[$k]["tradeMoney"]-($list[$k]["tradeMoney"]*$userdata["fee"]);
        }

        $this->assign('list', $list); // 赋值数据集
        $this->assign('nickname', $userdata['nickname']); // 赋值数据集
        $this->assign('money', $money[0]['money']/100); // 赋值数据集
        //  $this->assign('todaytime', time()); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('ranks',D('Userrank')->fetchAll());
        $this->display(); // 输出模板

    }
    private function export($title,$data){
        exportExcel($title, $data, date("Y-m-d H:i:s".time()), './', true);
    }
}
