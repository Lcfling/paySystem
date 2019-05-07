<?php

class MoneyAction extends CommonAction
{
    public function Index()
    {
        if ($this->isPost()) {
            $sta=$_POST['bg_date'];
            $end=$_POST['end_date'];
            $todaytime=strtotime($sta);
            $today_end=strtotime($end);
        }else{

            $todaytime=mktime(0,0,0,date('m'),date('d'),date('Y'));

            $today_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;
        }

        $User = D('order');
        import('ORG.Util.Page'); // 导入分页类
       // $map = array('closed' => array('IN', '0,-1'));


        $map['notify_time'] =array('between',array($todaytime,$today_end));
        $map['status'] ='1';
         $liushui=$User->where($map)->sum('total_amount');
        $this->assign('liushui', $liushui/100); // 赋值数据集
      //  $count = $User->where($map)->count(); // 查询满足要求的总记录数
     //   $Page = new Page($count, 8); // 实例化分页类 传入总记录数和每页显示的记录数
      //  $show = $Page->show(); // 分页显示输出

        $list = $User->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {

            $list[$k] = $val;
        }

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('todaytime', $todaytime); // 赋值数据
        $this->assign('today_end', $today_end); // 赋值数据
        $this->display(); // 输出模板
    }
    public function info(){

        if ($this->isPost()) {
            $sta=$_POST['bg_date'];
            $end=$_POST['end_date'];
            $todaytime=strtotime($sta);
            $today_end=strtotime($end);
        }else{

            $todaytime=$_GET['sta'];
            $today_end=$_GET['end'];
        }


        $User = D('Order');
        import('ORG.Util.Page'); // 导入分页类


        $map['notify_time'] =array('between',array($todaytime,$today_end));
        $map['status'] ='1';

        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 8); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as $k => $val) {
            $list[$k] = $val;
        }
        $todaytime=date("Y-m-d",$todaytime);
        $today_end=date("Y-m-d",$today_end);
        $this->assign('todaytime', $todaytime); // 赋值数据
        $this->assign('today_end', $today_end); // 赋值数据
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板

    }

    public function putmoney()
    {
        if ($this->isPost()) {
            $sta=$_POST['bg_date'];
            $end=$_POST['end_date'];
            $todaytime=strtotime($sta);
            $today_end=strtotime($end);
        }else{
            $todaytime=mktime(0,0,0,date('m'),date('d'),date('Y'));

            $today_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

        }

        $User = D('Tixian');
        import('ORG.Util.Page'); // 导入分页类
        // $map = array('closed' => array('IN', '0,-1'));


        $map['pass_time'] =array('between',array($todaytime,$today_end));
        $map['status'] ='1';
        $liushui=$User->where($map)->sum('money');
        $this->assign('liushui', $liushui/100); // 赋值数据集

        $this->assign('todaytime', $todaytime); // 赋值数据
        $this->assign('today_end', $today_end); // 赋值数据
        $this->display(); // 输出模板
    }


    public function putinfo(){

        if ($this->isPost()) {
            $sta=$_POST['bg_date'];
            $end=$_POST['end_date'];
            $todaytime=strtotime($sta);
            $today_end=strtotime($end);
        }else{

            $todaytime=$_GET['sta'];
            $today_end=$_GET['end'];
        }


        $User = D('Tixian');
        import('ORG.Util.Page'); // 导入分页类


        $map['pass_time'] =array('between',array($todaytime,$today_end));
        $map['status'] ='1';

        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 8); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('ID' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as $k => $val) {
            $list[$k] = $val;
        }
        $todaytime=date("Y-m-d",$todaytime);
        $today_end=date("Y-m-d",$today_end);
        $this->assign('todaytime', $todaytime); // 赋值数据
        $this->assign('today_end', $today_end); // 赋值数据
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板

    }

    public function maid(){
        {
            if ($this->isPost()) {
                $sta=$_POST['bg_date'];
                $end=$_POST['end_date'];
                $todaytime=strtotime($sta);
                $today_end=strtotime($end);
            }else{
                $todaytime=mktime(0,0,0,date('m'),date('d'),date('Y'));

                $today_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

            }

            $User = D('Fanyong');
            $map['fyDate'] =array('between',array($todaytime,$today_end));
            $map['type'] ='saolei';
            $saolei_liushui=$User->where($map)->sum('fenyong_edu');
            $this->assign('saolei_liushui', $saolei_liushui/100); // 扫雷赋值数据集


           // $map['fyDate'] =array('between',array($todaytime,$today_end));
            $map['type'] ='jielong';
            $jielong_liushui=$User->where($map)->sum('fenyong_edu');
            $this->assign('jielong_liushui', $jielong_liushui/100); // 接龙赋值数据集

            $this->assign('todaytime', $todaytime); // 赋值数据
            $this->assign('today_end', $today_end); // 赋值数据
            $this->display(); // 输出模板
        }

    }

    public function maidinfo(){

        if ($this->isPost()) {
            $sta=$_POST['bg_date'];
            $end=$_POST['end_date'];
            $type=$_POST['choose'];
            $todaytime=strtotime($sta);
            $today_end=strtotime($end);
        }else{
            $type=$_GET['game'];
            $todaytime=$_GET['sta'];
            $today_end=$_GET['end'];
            $this->assign('type', $type); // 赋值数据集
        }


        $User = D('Fanyong');
        import('ORG.Util.Page'); // 导入分页类


        $map['fyDate'] =array('between',array($todaytime,$today_end));
        $map['type'] =$type;

        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 8); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('ID' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as $k => $val) {
            $list[$k] = $val;
        }
        $todaytime=date("Y-m-d",$todaytime);
        $today_end=date("Y-m-d",$today_end);
        $this->assign('todaytime', $todaytime); // 赋值数据
        $this->assign('today_end', $today_end); // 赋值数据
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->display(); // 输出模板

    }

    public function profit(){

        {
            if ($this->isPost()) {
                $sta=$_POST['bg_date'];
                $end=$_POST['end_date'];
                $todaytime=strtotime($sta);
                $today_end=strtotime($end);
            }else{
                $todaytime=mktime(0,0,0,date('m'),date('d'),date('Y'));

                $today_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

            }
            //______________________________________________分佣数据
            $User = D('Fanyong');
            $map['fyDate'] =array('between',array($todaytime,$today_end));
            $map['type'] ='saolei';
            $saolei_fenyong=$User->where($map)->sum('fenyong_edu');
            $this->assign('saolei_fenyong', $saolei_fenyong/100); // 扫雷赋值数据集


            $map['type'] ='jielong';
            $jielong_fenyong=$User->where($map)->sum('fenyong_edu');
           $this->assign('jielong_fenyong', $jielong_fenyong/100); // 接龙赋值数据集
            //__________________________________________________________________________
            //获取机器人
            $User = D('Users');
            $map['is_robot'] ='1';
            $robat_list=$User->where($map);
            //$this->baoError(var_dump($robat_list));


            $User = D('kickback_jielong');
            $map['recivetime'] =array('between',array($todaytime,$today_end));
            $map['user_id'] ='0';
            $map['is_robot'] ='1';
            $jielong_miansi=$User->where($map)->sum('money');
            $jielong_yingli=$jielong_miansi-$jielong_fenyong;
            $this->assign('jielong_miansi', $jielong_miansi/100); // 扫雷赋值数据集
            $this->assign('jielong_yingli', $jielong_yingli/100); // 扫雷赋值数据集


            $this->assign('todaytime', $todaytime); // 赋值数据
            $this->assign('today_end', $today_end); // 赋值数据
            $this->display(); // 输出模板
        }
    }

    public function profitinfo(){

        if ($this->isPost()) {
            $sta=$_POST['bg_date'];
            $end=$_POST['end_date'];
            $type=$_POST['choose'];
            $todaytime=strtotime($sta);
            $today_end=strtotime($end);
        }else{
            $type=$_GET['game'];
            $todaytime=$_GET['sta'];
            $today_end=$_GET['end'];
            $this->assign('type', $type); // 赋值数据集
        }


        $User = D('Fanyong');
        import('ORG.Util.Page'); // 导入分页类


        $map['fyDate'] =array('between',array($todaytime,$today_end));
        $map['type'] =$type;

        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 8); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('ID' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as $k => $val) {
            $list[$k] = $val;
        }
        $todaytime=date("Y-m-d",$todaytime);
        $today_end=date("Y-m-d",$today_end);
        $this->assign('todaytime', $todaytime); // 赋值数据
        $this->assign('today_end', $today_end); // 赋值数据
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->display(); // 输出模板

    }
    public function rate(){
        if ($this->isPost()) {
            $sta=$_POST['bg_date'];
            $end=$_POST['end_date'];
            $todaytime=strtotime($sta);
            $today_end=strtotime($end);
        }else{
            $todaytime=mktime(0,0,0,date('m'),date('d'),date('Y'));

            $today_end=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;

        }

        $User = D('Tixian');
        import('ORG.Util.Page'); // 导入分页类
        // $map = array('closed' => array('IN', '0,-1'));


       $map['pass_time'] =array('between',array($todaytime,$today_end));
        $map['status'] ='1';
        $liushui=$User->where($map)->sum('rate');
        $this->assign('liushui', $liushui/100); // 赋值数据集

        $this->assign('todaytime', $todaytime); // 赋值数据
        $this->assign('today_end', $today_end); // 赋值数据
        $this->display(); // 输出模板

    }
}