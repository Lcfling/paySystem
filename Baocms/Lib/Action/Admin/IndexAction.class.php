<?php
//require_once LIB_PATH.'/GatewayClient/Gateway.php';
//
//use GatewayClient\Gateway;
class IndexAction extends CommonAction
{
    public function index()
    {
        $menu = D('Menu')->fetchAll();
        if ($this->_admin['role_id'] != 1) {
            if ($this->_admin['menu_list']) {
                foreach ($menu as $k => $val) {
                    if (!empty($val['menu_action']) && !in_array($k, $this->_admin['menu_list'])) {
                        unset($menu[$k]);
                    }
                }
                foreach ($menu as $k1 => $v1) {
                    if ($v1['parent_id'] == 0) {
                        foreach ($menu as $k2 => $v2) {
                            if ($v2['parent_id'] == $v1['menu_id']) {
                                $unset = true;
                                foreach ($menu as $k3 => $v3) {
                                    if ($v3['parent_id'] == $v2['menu_id']) {
                                        $unset = false;
                                    }
                                }
                                if ($unset) {
                                    unset($menu[$k2]);
                                }
                            }
                        }
                    }
                }
                foreach ($menu as $k1 => $v1) {
                    if ($v1['parent_id'] == 0) {
                        $unset = true;
                        foreach ($menu as $k2 => $v2) {
                            if ($v2['parent_id'] == $v1['menu_id']) {
                                $unset = false;
                            }
                        }
                        if ($unset) {
                            unset($menu[$k1]);
                        }
                    }
                }
            } else {
                $menu = array();
            }
        }
        $this->assign('menuList', $menu);
        $this->display();
    }


    public function indexs()
    {
        $menu = D('Menu')->fetchAll();
        if ($this->_admin['role_id'] != 1) {
            if ($this->_admin['menu_list']) {
                foreach ($menu as $k => $val) {
                    if (!empty($val['menu_action']) && !in_array($k, $this->_admin['menu_list'])) {
                        unset($menu[$k]);
                    }
                }
                foreach ($menu as $k1 => $v1) {
                    if ($v1['parent_id'] == 0) {
                        foreach ($menu as $k2 => $v2) {
                            if ($v2['parent_id'] == $v1['menu_id']) {
                                $unset = true;
                                foreach ($menu as $k3 => $v3) {
                                    if ($v3['parent_id'] == $v2['menu_id']) {
                                        $unset = false;
                                    }
                                }
                                if ($unset) {
                                    unset($menu[$k2]);
                                }
                            }
                        }
                    }
                }
                foreach ($menu as $k1 => $v1) {
                    if ($v1['parent_id'] == 0) {
                        $unset = true;
                        foreach ($menu as $k2 => $v2) {
                            if ($v2['parent_id'] == $v1['menu_id']) {
                                $unset = false;
                            }
                        }
                        if ($unset) {
                            unset($menu[$k1]);
                        }
                    }
                }
            } else {
                $menu = array();
            }
        }
        $this->assign('menuList', $menu);
        $this->display();
    }
	
	
    public function main(){
        $brandid=$_SESSION['brandid'];
      $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));

        $counts['count_ord']=D('Payord')->where("brandid=$brandid and creattime>".$beginToday)->count();


        $counts['count_ord_success']=D('Payord')->where("brandid=$brandid and sta=1 and creattime>".$beginToday)->count();
        $counts['count_ord_fail']=D('Payord')->where("brandid=$brandid and sta=0  and creattime>".$beginToday )->count();

        $pay_money=D("Payord")->where("brandid=$brandid and sta=1  and creattime>".$beginToday)->field('sum(pay_money) as pay_money')->select();
        $counts['pay_money']=$pay_money[0]['pay_money']/100;

        $day_rujin=D("Payord")->where("brandid=$brandid and sta=1  and creattime>".$beginToday)->field('sum(money) as money')->select();
        $counts['day_rujin']=$day_rujin[0]['money']/100;


        $rujin=D("Payord")->where("brandid=$brandid and sta=1")->field('sum(money) as money')->select();
        $counts['rujin']=$rujin[0]['money']/100;





        $txian=D("Zhangbian")->where("brandid=$brandid and sta<2")->field('sum(money) as money')->select();
        $counts['txian']=$txian[0]['money']/100;

        if(empty($counts['txian'])){
            $counts['txian']=0;

        }

        //驳回额度
        $txian_bohui=D("Zhangbian")->where("brandid=$brandid and sta=2")->field('sum(money) as money')->select();
        $counts['txian_bohui']=$txian_bohui[0]['money']/100;

        if(empty($counts['txian_bohui'])){
            $counts['txian_bohui']=0;

        }


        //账号余额- 提现表==(账户余额可提现+被驳回的额度)
        $counts['money']=$counts['rujin']-$counts['txian'];
       
        /*   //今日线下充值
        $tdxianxia=D("Paid")->where("remark='线下充值' and creatime>".$beginToday)->field('sum(money) as money')->select();
        $sql=D('Paid')->getLastSql();
        // echo $sql;
        $counts['tdxianxia']=$tdxianxia[0]['money']/100;

        //总提现申请
        $txall=D("Tixian")->where("1")->field('sum(money) as money')->select();
        $counts['txall']=$txall[0]['money']/100;
        //
        //总提现申请
        $txsucc=D("Tixian")->where("status=1")->field('sum(money) as money')->select();
        $counts['txsucc']=$txsucc[0]['money']/100;

        //今日总提现
        $tdtxall=D("Tixian")->where("time>".$beginToday)->field('sum(money) as money')->select();
        $counts['tdtxall']=$tdtxall[0]['money']/100;

        //总提现申请
        $tdtxsucc=D("Tixian")->where("status=1 and time>".$beginToday)->field('sum(money) as money')->select();
        $counts['tdtxsucc']=$tdtxsucc[0]['money']/100;


        //总计发包个数
        $counts['countfabao']=D('Paid')->where("remark='发送红包'")->count();

        $counts['countzl']=D('Paid')->where("remark='中雷' and money>0")->count();
        //print_r($counts);


            $xiaxia_money=D('Paid')->where("remark='线下充值'")->field('sum(money) as money')->select();
             $counts['xianxia_money']=$xiaxia_money[0]['money']/100;

                //会员总收入
               $vip_count=D('Users')->where("vip=1")->count();
               $counts['vip_money']=($vip_count-21)*598;

        //今线下会员收入
        $jr_xianxiamoney= D('Order')->where("remark='后台充值vip' and status=1 and creatime>".$beginToday)->field('sum(total_amount) as money')->select();
        $counts['jr_xianxiamoney']=$jr_xianxiamoney[0]['money']/100;

        //今线上会员收入
        $jr_xianshangmoney= D('Order')->where("remark='购买vip' and status=1 and creatime>".$beginToday)->field('sum(total_amount) as money')->select();
        $counts['jr_xianshangmoney']=$jr_xianshangmoney[0]['money']/100;
*/

        $this->assign('counts', $counts);


        $this->display();
    }


    public function mains(){
        $business= session('admin.business_code');

        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));

        $counts['count_ord']=D('order')->where("business_code=$business and creattime>".$beginToday)->count();
        if (empty($counts['count_ord'])){
            $counts['count_ord']=0;
        }
        //$counts['count_ord']=D('order')->where("business_code=$business ")->count();

        $counts['count_ord_success']=D('order')->where("business_code=$business and status=1 and creattime>".$beginToday)->count();
        if (empty($counts['count_ord_success'])){
            $counts['count_ord_success']=0;
        }


        $counts['count_ord_fail']=D('order')->where("business_code=$business and status=0  and creattime>".$beginToday )->count();
        if (empty($counts['count_ord_fail'])){
            $counts['count_ord_fail']=0;
        }




        $pay_money=D("Order")->where("business_code=$business and status=1  ")->field('sum(tradeMoney) as pay_money')->select();
        $counts['pay_money']=$pay_money[0]['pay_money']/100;



        $day_rujin=D("Order")->where("business_code=$business and status=1  and creattime>".$beginToday)->field('sum(tradeMoney) as money')->select();
        $counts['day_rujin']=$day_rujin[0]['money']/100;




        $rujin=D("Payord")->where("brandid=$brandid and sta=1")->field('sum(money) as money')->select();
        $counts['rujin']=$rujin[0]['money']/100;




        // 累计提现成功
        $txian=D("business_withdraw")->where("business_code=$business and status=1")->field('sum(money) as money')->select();
        $counts['txian']=$txian[0]['money']/100;


        // 待审核提现
        $dtxian=D("business_withdraw")->where("business_code=$business and status=0")->field('sum(money) as money')->select();
        $counts['dtxian']=$dtxian[0]['money']/100;



        if(empty($counts['txian'])){
            $counts['txian']=0;

        }

        //驳回额度
        $txian_bohui=D("Zhangbian")->where("brandid=$brandid and sta=2")->field('sum(money) as money')->select();
        $counts['txian_bohui']=$txian_bohui[0]['money']/100;

        if(empty($counts['txian_bohui'])){
            $counts['txian_bohui']=0;

        }

        $business_info=D("business")->where(array("business_code"=>$business))->find();
        //账号余额- 提现表==(账户余额可提现+被驳回的额度)
        $yjmoney=$counts['pay_money']-$counts['txian']- $counts['dtxian'];
        $counts['money']= round($yjmoney-$yjmoney*$business_info['fee'],2);

        /*   //今日线下充值
        $tdxianxia=D("Paid")->where("remark='线下充值' and creatime>".$beginToday)->field('sum(money) as money')->select();
        $sql=D('Paid')->getLastSql();
        // echo $sql;
        $counts['tdxianxia']=$tdxianxia[0]['money']/100;

        //总提现申请
        $txall=D("Tixian")->where("1")->field('sum(money) as money')->select();
        $counts['txall']=$txall[0]['money']/100;
        //
        //总提现申请
        $txsucc=D("Tixian")->where("status=1")->field('sum(money) as money')->select();
        $counts['txsucc']=$txsucc[0]['money']/100;

        //今日总提现
        $tdtxall=D("Tixian")->where("time>".$beginToday)->field('sum(money) as money')->select();
        $counts['tdtxall']=$tdtxall[0]['money']/100;

        //总提现申请
        $tdtxsucc=D("Tixian")->where("status=1 and time>".$beginToday)->field('sum(money) as money')->select();
        $counts['tdtxsucc']=$tdtxsucc[0]['money']/100;


        //总计发包个数
        $counts['countfabao']=D('Paid')->where("remark='发送红包'")->count();

        $counts['countzl']=D('Paid')->where("remark='中雷' and money>0")->count();
        //print_r($counts);


            $xiaxia_money=D('Paid')->where("remark='线下充值'")->field('sum(money) as money')->select();
             $counts['xianxia_money']=$xiaxia_money[0]['money']/100;

                //会员总收入
               $vip_count=D('Users')->where("vip=1")->count();
               $counts['vip_money']=($vip_count-21)*598;

        //今线下会员收入
        $jr_xianxiamoney= D('Order')->where("remark='后台充值vip' and status=1 and creatime>".$beginToday)->field('sum(total_amount) as money')->select();
        $counts['jr_xianxiamoney']=$jr_xianxiamoney[0]['money']/100;

        //今线上会员收入
        $jr_xianshangmoney= D('Order')->where("remark='购买vip' and status=1 and creatime>".$beginToday)->field('sum(total_amount) as money')->select();
        $counts['jr_xianshangmoney']=$jr_xianshangmoney[0]['money']/100;
*/
        $businessinfo=D("business")->where(array("business_code"=>$business))->find();

        $this->assign('businessinfo', $businessinfo);
        $this->assign('time', time());
        $this->assign('counts', $counts);
        $this->display();
    }


    public function gonggao(){

            $index = D('Index');
            $gonggao=$index->find();
            if ($this->isPost()) {
                $data['content']=$_POST['content'];
                $data['creatime'] = time();
                $data['lb']=1;

                if (false !==$index->add($data)) {
                    $this->baoSuccess('添加成功', U('index/gonggao'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('content', $gonggao['content']);
                $this->display();
            }
        $this->display();
    }


    public function kefu(){

        if ($this->isPost()) {
            //Logo
            if ( !file_exists("./kefu/" . $_FILES["kefu"]["name"]))
            {

                $myfile = fopen("FILES_kefu.txt", "a+") ;
                fwrite($myfile,var_export($_FILES,true) );
                fclose($myfile);


                $fileName=$_FILES['kefu']['name'];//得到上传文件的名字
                $name=explode('.',$fileName);//将文件名以'.'分割得到后缀名,得到一个数组
                $date=date('Ymdhis');//得到当前时间,如;20070705163148
                $newPath=$date.'.'.$name[1];//得到一个新的文件为'20070705163148.jpg',即新的路径

                move_uploaded_file($_FILES["kefu"]["tmp_name"], "./kefu/" .$newPath);
            }

            $data['content'] ="./kefu/" .$newPath;



            $data['lb']=1;
            $data['time'] = time();

            $obj = D('kefu');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('index/kefu'));
            }
        }
        $this->display();

    }
}
