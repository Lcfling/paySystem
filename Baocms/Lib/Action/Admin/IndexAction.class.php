<?php
require_once LIB_PATH.'/GatewayClient/Gateway.php';

use GatewayClient\Gateway;
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
	
	
    public function main(){
		$this->assign('warning',$warning = D('Admin')->find($this->_admin['admin_id']));
        $bg_time = strtotime(TODAY);


        $counts['totay_order'] = (int) D('Order')->where(array('type' => 'goods', 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();

        $counts['order'] = (int) D('Order')->where(array('type' => 'goods', 'status' => array('EGT', 0)))->count();

        $counts['gold'] = (int) D('Order')->where(array('type' => 'gold', 'status' => array('EGT', 0)))->count();
        $counts['today_yuyue'] = (int) D('Shopyuyue')->where(array('create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();

        //查询今日会员
        $counts['users'] = (int) D('Users')->count();
        $counts['totay_user'] = (int) D('Users')->where(array('reg_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();
        $counts['user_moblie'] = (int) D('Users')->where(array('mobile'=>array('EXP','IS NULL')))->count();
        $counts['user_email'] = (int) D('Users')->where(array('email'=>array('EXP','IS NULL')))->count();
        $counts['user_weixin'] = (int) D('Connect')->where(array('type'=>weixin))->count();
        $counts['user_weibo'] = (int) D('Connect')->where(array('type'=>weibo))->count();
        $counts['user_qq'] = (int) D('Connect')->where(array('type'=>qq))->count();
        $counts['user_weixin_day'] = (int) D('Connect')->where(array('reg_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->count();

        $counts['online']=Cac()->get('alluserin');

        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));

      //  echo $beginToday;

        $counts['totay_order'] = (int) D('Order')->where(array('type' => 'goods', 'create_time' => array(array('ELT', NOW_TIME), array('EGT', $bg_time)), 'status' => array('EGT', 0)))->count();

        //机器人余额
        $robots=D("Users")->where('is_robot=1')->field('user_id')->select();
        foreach ($robots as $v){
            $robotsarr[]=$v['user_id'];
        }
        $rolist=implode(',',$robotsarr);
        $robmoney=D('Paid')->where('user_id in ('.$rolist.')')->field('sum(money) as money')->select();
        $counts['robmoney']=$robmoney[0]['money']/100;

        //免死金额
        $miansi=D("Kickback")->where('is_robot=1')->field('sum(money) as money')->select();
        $counts['miansi']=$miansi[0]['money']/100;

        //佣金
        $yongjin=D("Fanyong")->where('1')->field('sum(fenyong_edu) as money')->select();
        $counts['yongjin']=$yongjin[0]['money']/100;

        //客户总余额
        $client=D("Paid")->where('1')->field('sum(money) as money')->select();
        $counts['client']=$client[0]['money']/100;

        //中奖
        $zj=D("Paid")->where('type=7 and is_afect=1')->field('sum(money) as money')->select();
        $counts['zj']=$zj[0]['money']/100;

        //体验金
        $tyj=D("Paid")->where("remark='体验金'")->field('sum(money) as money')->select();
        $counts['tyj']=$tyj[0]['money']/100;
        $zfb=D("Paid")->where("remark='支付宝充值'")->field('sum(money) as money')->select();
        $counts['zfb']=$zfb[0]['money']/100;
      //今日线上充值
        $tdzfb=D("Paid")->where("remark='支付宝充值' and creatime>".$beginToday)->field('sum(money) as money')->select();
        $sql=D('Paid')->getLastSql();
       // echo $sql;
        $counts['tdzfb']=$tdzfb[0]['money']/100;

        //今日线下充值
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


        $this->assign('counts', $counts);




        $this->display();
    }
}
