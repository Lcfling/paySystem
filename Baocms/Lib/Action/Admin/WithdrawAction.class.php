<?php
class WithdrawAction extends CommonAction {

    private $edit_fields = array('username', 'bankname','banknum');
    private $tx_fields = array('money', 'bank_id','password');


    public function change(){

        import('ORG.Util.Page'); // 导入分页类
        $brandid=$_SESSION['brandid'];

        $EX= D('zhangbian');
        $brandid=$_SESSION['brandid'];



        // $sql = "select f.*,s.* from bao_payord as f,bao_zhangbian as s where f.brandid=$brandid and s.brandid=$brandid";
        $sql_z = "select * from bao_zhangbian where brandid=$brandid";
        $count_z = M('kuaifu')->execute($sql_z);//execute    query

        $sql_ord = "select * from bao_payord where brandid=$brandid";
        $count_ord = M('kuaifu')->execute($sql_ord);//execute    query
        $count=$count_ord+$count_z;

        //   $count = $EX->where("brandid=".$brandid)->count(); // 查询满足要求的总记录数


        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出


        // $sql = "select f.*,s.* from bao_payord as f,bao_zhangbian as s where f.brandid=$brandid and s.brandid=$brandid";
        //$list = M('kuaifu')->query($sql);//execute    query

        $list = $EX->where("brandid=".$brandid)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //$list = M('kuaifu')->join("bao_payord u on brandid=$brandid")->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        //  $list = D('Zhangbian')->alias('a')->join('Zhangbian c ON c.brandid ='.$brandid)->field();
        //  print_r($list);
        foreach($list as $k=>$val){
            $val['money']=$val['money']/100;
            if($val['sta']==0){

                $val['sta']='等待审核';
            }else if($val['sta']==1) {
                $val['sta']='打款通过';
            }else if($val['sta']==2) {
                $val['sta']='审核驳回';
            }

            //  $detail = D('Brandbank')->where("id=". $val['bank_id'])->select();
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
    public function search(){
        $User = D('Brandbank');
        import('ORG.Util.Page'); // 导入分页类
        $branid=$_SESSION['brandid'];

        $map = array();


        if($username= $this->_param('username','htmlspecialchars')){
            $map['username'] =$username;
        }

        if($bankname= $this->_param('bankname','htmlspecialchars')){
            $map['bankname'] =$bankname;
        }

        if($banknum= $this->_param('banknum','htmlspecialchars')){
            $map['banknum'] =$banknum;
        }


        $map['brandid']=$branid;
        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$val){

            $list[$k] = $val;
        }

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->display(); // 输出模板


    }
    public function withdraw(){
        $brandid=$_SESSION['brandid'];
        $rujin=D("Payord")->where("brandid=$brandid and sta=1")->field('sum(payAmt) as money')->select();
        $counts['rujin']=$rujin[0]['money'];
        //减去 提现记录
        $txian=D("Zhangbian")->where("brandid=$brandid and sta<2")->field('sum(money) as money')->select();
        $counts['txian']=$txian[0]['money'];
        if(empty($counts['txian'])){
            $counts['txian']=0;

        }

        //剩余真实提现

        $counts['money']=((int)$counts['rujin']-(int)$counts['txian'])/100;

        $obj = D('Brandbank');
        $menu = $obj->where("brandid=".$brandid)->select();

        $sqlpaypsd=D("Admin");
        $list = $sqlpaypsd->where("brandid=".$brandid)->select();

        // if ($list[0]['paypassword']!=md5($paypassword)){
        //   $this->baoError('原密码输入不正确');

        //   }

        $sqlzb = D('zhangbian');
        if ($this->isPost()) {
            $postdata = $this->_post('data', false);
            $data = $this->txCheck($counts['money'],$list[0]['paypassword']);
            $data['brandid'] = $brandid;
            $data['pid'] = $list[0]['pid'];
            $data['money'] = $postdata['money']*100;
            $data['bank_id'] = $postdata['bank_id'];
            $data['creattime'] = time();
            $data['sta'] = 0;
            $data['statime'] = 0;
//==================================================================
            $rujin=D("Payord")->where("brandid=$brandid and sta=1")->field('sum(money) as money')->select();
            $counts['rujin']=$rujin[0]['money'];

            $txian=D("Zhangbian")->where("brandid=$brandid and sta<2")->field('sum(money) as money')->select();
            $counts['txian']=$txian[0]['money'];

            if(empty($counts['txian'])){
                $counts['txian']=0;

            }
            $counts['money']=$counts['rujin']-$counts['txian'];
            $tixian=$postdata['money']*100;
            $data['ye'] = $counts['money']-$tixian;
//=================================================
            if (false !==$sqlzb->add($data)) {
                $this->baoSuccess('操作成功', U('zhangbian/zhichu'));
            }
            $this->baoError('操作失败');
        }
        $this->assign('counts', $counts);
        $this->assign('datas', $menu);
        $this->display(); // 输出模板
    }
    public function bank() {
        $brandid=$_SESSION['brandid'];

        $obj=D("Brandbank");

        if ($this->isPost()) {

            $postdata =$this->_post('data', false);

            $data = $this->editCheck();

            $data['brandid'] = $brandid;
            $data['username'] = $postdata['username'];
            $data['bankname'] = $postdata['bankname'];
            $data['banknum'] = $postdata['banknum'];
            $data['creattime'] = time();
            if (false !==$obj->add($data)) {

                $this->baoSuccess('操作成功', U('withdraw/bank'));
            }
            $this->baoError('操作失败');
        } else {
            // $this->assign('detail', $detail);
            //  $this->assign('ranks',D('Userrank')->fetchAll());
            $this->display();

        }

    }

    private function txCheck($sqlmoney,$paypsd) {
        $data = $this->checkFields($this->_post('data', false), $this->tx_fields);




        $data['money'] = htmlspecialchars($data['money']);
        if (empty($data['money'])) {
            $this->baoError('提现额度不能为空');
        }
        if ($data['money']*100>$sqlmoney*100) {
            $this->baoError('提现额度大于'.$sqlmoney);
        }

        if ($data['money']*100<10000) {
            $this->baoError('提现最少100元起');
        }
        if ($data['money']*100>5000000) {
            $this->baoError('提现最高不得超过50000元');
        }
        $data['bank_id'] = htmlspecialchars($data['bank_id']);
        if (empty($data['bank_id'])) {
            $this->baoError('请选择到账银行卡');
        }
        $data['password'] = htmlspecialchars($data['password']);
        if (empty($data['password'])) {
            $this->baoError('提现密码不能为空');
        }

        if (md5($data['password'])!=$paypsd) {
            $this->baoError('提现密码不正确');
        }
        return $data;
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
    public function bankcontrol(){
        $User = D('Brandbank');
        import('ORG.Util.Page'); // 导入分页类

        $branid=$_SESSION['brandid'];
        $map = array();
        $map['brandid']=$branid;
        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$val){

            $list[$k] = $val;
        }

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->display(); // 输出模板


    }
    public function bankedit($bank_id = 0) {

        if ($bank_id = (int) $bank_id) {
            $obj = D('Brandbank');
            if (!$detail = $obj->find($bank_id)) {
                $this->baoError('请选择要编辑的信息');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['id'] = $bank_id;
                if (false !==$obj->save($data)) {
                    //  Cac()->delete('userinfo_'.$bank_id);
                    $this->baoSuccess('操作成功', U('withdraw/bankcontrol'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                //$this->assign('ranks',D('Userrank')->fetchAll());
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的信息');
        }
    }

    public function bankdelete($bank_id = 0) {
        if (is_numeric($bank_id) && ($bank_id = (int) $bank_id)) {
            $obj = D('Brandbank');
            //$obj->save(array('user_id'=>$user_id,'closed'=>1));
            $obj->delete($bank_id);
            $this->baoSuccess('删除成功！', U('withdraw/bankcontrol'));
        } else {
            $bank_id = $this->_post('bank_id', false);
            if (is_array($bank_id)) {
                $obj = D('Brandbank');
                foreach ($bank_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('user/index'));
            }
            $this->baoError('请选择要删除的信息');
        }
    }

    public function withdrawsearch($bank_id=0){

        $brandid=$_SESSION['brandid'];
        $rujin=D("Payord")->where("brandid=$brandid and sta=1")->field('sum(money) as money')->select();
        $counts['rujin']=$rujin[0]['money'];
        //减去 提现记录
        $txian=D("Zhangbian")->where("brandid=$brandid and sta<2")->field('sum(money) as money')->select();
        $counts['txian']=$txian[0]['money'];
        if(empty($counts['txian'])){
            $counts['txian']=0;

        }

        //剩余真实提现

        $counts['money']=((int)$counts['rujin']-(int)$counts['txian'])/100;

        $obj = D('Brandbank');
        $bankinfo = $obj->where("id=".$bank_id)->find();

        $sqlpaypsd=D("Admin");
        $list = $sqlpaypsd->where("brandid=".$brandid)->select();
        $data=array();


        $sqlzb = D('zhangbian');
        if ($this->isPost()) {
            $postdata = $this->_post('data', false);
            $data = $this->searchtxCheck($counts['money'],$list[0]['paypassword']);
            $data['brandid'] = $brandid;
            $data['pid'] = $list[0]['pid'];
            $data['money'] = $postdata['money']*100;
            $data['bank_id'] = $postdata['bank_id'];
            $data['creattime'] = time();
            $data['sta'] = 0;
            $data['statime'] = 0;
//==================================================================
            $rujin=D("Payord")->where("brandid=$brandid and sta=1")->field('sum(money) as money')->select();
            $counts['rujin']=$rujin[0]['money'];

            $txian=D("Zhangbian")->where("brandid=$brandid and sta<2")->field('sum(money) as money')->select();
            $counts['txian']=$txian[0]['money'];

            if(empty($counts['txian'])){
                $counts['txian']=0;

            }
            $counts['money']=$counts['rujin']-$counts['txian'];
            $tixian=$postdata['money']*100;
            $data['ye'] = $counts['money']-$tixian;

//=================================================
            if (false !==$sqlzb->add($data)) {
                $this->baoSuccess('操作成功', U('zhangbian/zhichu'));
            }
            $this->baoError('操作失败');
        }
        $this->assign('counts', $counts);
        $this->assign('bankinfo', $bankinfo);

        $this->display(); // 输出模板
    }


    private function searchtxCheck($sqlmoney,$paypsd) {
        $data = $this->checkFields($this->_post('data', false), $this->tx_fields);

        $data['money'] = htmlspecialchars($data['money']);
        if (empty($data['money'])) {
            $this->baoError('提现额度不能为空');
        }
        if ($data['money']*100>$sqlmoney*100) {
            $this->baoError('提现额度大于'.$sqlmoney);
        }

        if ($data['money']*100<10000) {
            $this->baoError('提现最少100元起');
        }
        if ($data['money']*100>5000000) {
            $this->baoError('提现最高不得超过50000元');
        }

        $data['password'] = htmlspecialchars($data['password']);
        if (empty($data['password'])) {
            $this->baoError('提现密码不能为空');
        }

        if (md5($data['password'])!=$paypsd) {
            $this->baoError('提现密码不正确');
        }
        return $data;
    }
}