<?php
class ZhangbianAction extends CommonAction {

    private $edit_fields = array('username', 'bankname','banknum');



    public function zhichu(){

        import('ORG.Util.Page'); // 导入分页类
        $EX= D('zhangbian');
        $brandid=$_SESSION['brandid'];

         $count = $EX->where("brandid=".$brandid)->count(); // 查询满足要求的总记录数


        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        $list = $EX->where("brandid=".$brandid)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach($list as $k=>$val){
            $val['money']=$val['money']/100;
            $val['ye']=$val['ye']/100;
            if($val['sta']==0){

                $val['sta']='等待审核';
            }else if($val['sta']==1) {
                $val['sta']='打款通过';
            }else if($val['sta']==2) {
                $val['sta']='审核驳回';
            }


            $detail = D('Brandbank')->find($val['bank_id']);
            $val['bankuser']=$detail['username'];
            $val['bankname']=$detail['bankname'];
            $val['banknum']=$detail['banknum'];
            $list[$k] = $val;
        }

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->display();
    }





    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['username'] = htmlspecialchars($data['username']);
        if (empty($data['username'])) {
            $this->baoError('开户人不能为空');
        }

        $data['bankname'] = htmlspecialchars($data['bankname']);
        if (empty($data['bankname'])) {
            $this->baoError('开户行不能为空');
        }
        $data['banknum'] = htmlspecialchars($data['banknum']);
        if (empty($data['banknum'])) {
            $this->baoError('银行卡号不能为空');
        }
        return $data;
    }


    public function shouru() {
        $brandid=$_SESSION['brandid'];
        $User = D('Payord');
        import('ORG.Util.Page'); // 导入分页类
        $map = array();
        $map['brandid']=$brandid;
        $map['sta']=1;

        if($ordid = $this->_param('ordid','htmlspecialchars')){
            $map['orderNo'] = array('LIKE','%'.$ordid.'%');
            $this->assign('rdid',$ordid);
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
            $val['ye'] =$val['ye'] /100;
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

        $this->assign('counts',$counts);

        $this->display(); // 输出模板

    }

}