<?php
class UserAction extends CommonAction {
    private $create_fields = array('account', 'password','rank_id', 'face','mobile','email','nickname','face','ext0');
    private $edit_fields = array('account', 'password','rank_id','face', 'mobile','email','nickname','face','ext0');
    private $notice_fields = array('title', 'content');




    public function index() {
        $User = D('Users');
        import('ORG.Util.Pageam'); // 导入分页类


        if($user_id= $this->_param('user_id','htmlspecialchars')){
            $map['user_id'] = $user_id;
            $this->assign('user_id',$user_id);
        }

        if($mobile = $this->_param('mobile','htmlspecialchars')){
            $map['account'] = array('LIKE','%'.$mobile.'%');
            $this->assign('mobile',$mobile);
        }

        if($shenfen = $this->_param('shenfen','htmlspecialchars')){
            $map['shenfen'] = $shenfen;
            $this->assign('shenfen',$shenfen);
        }

        if($frozen = $this->_param('frozen','htmlspecialchars')){
            $map['frozen'] = $frozen;
            $this->assign('frozen',$frozen);
        }

        if($bg_time = $this->_param('bg_time','htmlspecialchars')){
            $end_time = $this->_param('end_time','htmlspecialchars');
            $map['creatime'] =array('between',array($bg_time,$end_time));
            $this->assign('bg_time',$bg_time);
            $this->assign('end_time',$end_time);
        }



        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('user_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as &$v){

            // 收款总额
            $money=D("order")->where(array("user_id"=>$v['user_id']))->field("sum(tradeMoney) as money")->select();
            $v['paimoney']=$money[0]['money']/100;

            $cmoney=D("order")->where(array("user_id"=>$v['user_id'],"status"=>1))->field("sum(tradeMoney) as money")->select();
            $v['cmoney']=$cmoney[0]['money']/100;


            $sql="select count(*) AS count  from zf_order where user_id=".$v['user_id'];
            $countss=D()->query($sql);
            $v['pcounts']=$countss[0]["count"];

            $sqls="select count(*) AS count  from zf_order where user_id=".$v['user_id']." and status=1";
            $counts=D()->query($sqls);
            $v['ccounts']=$counts[0]["count"];

           $score= D("Account_log")->where(array("user_id"=>$v['user_id']))->field("sum(score) as score")->select();
            $v['syscore']=$score[0]['score']/100;
            

        }

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }


    public function zhangdan() {
        $User = D('Account_log');
        import('ORG.Util.Pageam'); // 导入分页类


        if($user_id= $this->_param('user_id','htmlspecialchars')){
            $map['user_id'] = $user_id;
            $this->assign('user_id',$user_id);
        }

        if($mobile = $this->_param('mobile','htmlspecialchars')){
            $map['account'] = array('LIKE','%'.$mobile.'%');
            $this->assign('mobile',$mobile);
        }
        $map["status"]=1;
        $map["remark"]="充值";

        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as &$v){

            $v['score']=$v['score']/100;
        }

        //平台码商总充值
        $score=D("Account_log")->field("sum(score) as score")->select();
        $score=$score[0]['score']/100;

        //今日收益
        $sy_begintime=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y')));
        $sy_catime = strtotime($sy_begintime);
        $sy_where['creatime']=array("EGT",$sy_catime);
        $jrscore=D("Account_log")->where($sy_where)->field("sum(score) as score ")->select();
        $jrscore=$jrscore[0]['score']/100;




        //今日提现
        $tx_begintime=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y')));
        $tx_catime = strtotime($tx_begintime);
        $tx_where['creatime']=array("EGT",$tx_catime);
        $tx_where['status']=1;
        $jr_money=D("withdraw")->where($tx_where)->field("sum(money) as money")->select();
        $jr_money=$jr_money[0]['money']/100;


        //已提现成功
        $y_tx=D("withdraw")->where(array("status"=>1))->field("sum(money) as money")->select();
        $y_tx=$y_tx[0]['money']/100;


        //  码商总提现
        $money=D("withdraw")->where(array("status"=>0))->field("sum(money) as money")->select();
        $money=$money[0]['money']/100;




        $this->assign('score', $score); // 平台码商总充值
        $this->assign('jrscore', $jrscore); // 今日收益
        $this->assign('money', $money+$y_tx); // 码商总提现
        $this->assign('jr_money', $jr_money); // 今日提现
        $this->assign('y_tx', $y_tx); // 已提现


        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function create() {
        if ($this->isPost()) {

            $data = $this->createCheck();
            $obj = D('Users');
            print_r($data);
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('user/index'));
            }
            $this->baoError('操作失败！'.$data['pid']);
        } else {
            $this->assign('ranks',D('Userrank')->fetchAll());
            $this->display();
        }
    }






    private function createCheck() {
       // $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['account'] = htmlspecialchars($_POST['account']);
        if (empty($data['account'])) {
            $this->baoError('账户不能为空');
        }
        $userinfo=D('Users')->where("account=".$data['account'])->find();

        if($userinfo){
            $this->baoError('该账户已经存在！');
        }
        $data['password'] = htmlspecialchars($_POST['password']);
        if (empty($data['password'])) {
            $this->baoError('密码不能为空');
        }

        $data['password'] = md5($data['password']);

        $data['pid'] = htmlspecialchars($_POST['pid']);
        if (empty($data['pid'])) {
            $pid=0;
        }else{
            $pid=$data['pid'];
        }

        $data['shenfen'] = htmlspecialchars($_POST['shenfen']);
        if (empty($data['shenfen'])) {
            $this->baoError('身份不能为空');
        }

        $data['rate'] = htmlspecialchars($_POST['rate']);
        if (empty($data['rate'])) {
            $this->baoError('佣金费率不能为空');
        }
     //   $this->baoError('佣金费率不能为空'.$data['rate']/100);
        $info['pid']=$pid;
        $info['shenfen']= $data['shenfen'];
        $info['account']= $data['account'];
        $info['password']= $data['password'];
        $info['rate']=$data['rate']/100;
        $info['token']=md5(rand_string(6,1));
        $info['money']=0;
        $info['imsi_num']=0;
        $info['frozen']=0;
        $info['take_status']=0;
        $info['reg_ip']=$info['last_ip']=getip();
        $info['reg_time']=$info['last_time']=time();
        $info['mobile']=$data['account'];
        return $info;
    }

    public function edit($user_id = 0) {
        if ($user_id = (int) $user_id) {
            $obj = D('Users');
            if (!$detail = $obj->find($user_id)) {
                $this->baoError('请选择要编辑的会员');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['user_id'] = $user_id;
                if (false !==$obj->save($data)) {
                    Cac()->delete('userinfo_'.$user_id);
                    $this->baoSuccess('操作成功', U('user/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('ranks',D('Userrank')->fetchAll());
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的会员');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['account'] = htmlspecialchars($data['account']);
        if (empty($data['account'])) {
            $this->baoError('账户不能为空');
        }
        /*if($data['password'] == '******'){
            unset($data['password']);
        }else{
            $data['password'] = htmlspecialchars($data['password']);
            if (empty($data['password'])) {
                $this->baoError('密码不能为空');
            }
            $data['password'] = md5($data['password']);
        }*/
        $data['nickname'] = htmlspecialchars($data['nickname']);
        // $data['face'] = htmlspecialchars($data['face']);
        //$data['email'] = htmlspecialchars($data['email']);
        //$data['ext0'] = htmlspecialchars($data['ext0']);
        //  $data['rank_id'] = (int)$data['rank_id'];
        if (empty($data['nickname'])) {
            $this->baoError('昵称不能为空');
        }
        return $data;
    }

    public function delete($user_id = 0) {
        if (is_numeric($user_id) && ($user_id = (int) $user_id)) {
            $obj = D('Users');
            //$obj->save(array('user_id'=>$user_id,'closed'=>1));
            $obj->delete($user_id);
            $this->baoSuccess('删除成功！', U('user/index'));
        } else {
            $user_id = $this->_post('user_id', false);
            if (is_array($user_id)) {
                $obj = D('Users');
                foreach ($user_id as $id) {
                    $obj->delete($id);
                }
                $this->baoSuccess('删除成功！', U('user/index'));
            }
            $this->baoError('请选择要删除的会员');
        }
    }


    public function add($user_id){
        if ($user_id = (int) $user_id) {
            $obj = D('Account_log');
            if ($this->isPost()) {
                $data['score']=$_REQUEST['score']*100;
                $data['user_id'] = $user_id;
                $data['status']=1;
                $data['remark']="充值";
                $data['creatime']=time();
                if (false !==$obj->add($data)) {
                    $this->baoSuccess('添加成功', U('user/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('user_id', $user_id);
                $this->display();
            }
        } else {
            $this->baoError('请选择正确的会员');
        }
    }



    public function addcode($user_id){
        if ($user_id = (int) $user_id) {
            $User = D('Users');
            if ($this->isPost()) {

              $code=(int)$_REQUEST['code'];

                $info=$User->where('user_id='.$user_id)->setInc('imsi_num',$code);

                if (false !==$info) {
                    $this->baoSuccess('添加成功', U('user/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('user_id', $user_id);
                $this->display();
            }
        } else {
            $this->baoError('请选择正确的会员');
        }
    }


    public function adduser() {
        if ($this->isPost()) {

            $data = $this->createCheck();
            $obj = D('Users');

            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('user/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('ranks',D('Userrank')->fetchAll());
            $this->display();
        }
    }

    private function usereditCheck() {

        //  $data = $this->checkFields($this->_post('data', false), $this->edit_fields);

        $data['name'] = $_POST['name'];
        if (empty($data['name'])) {
            $this->baoError('开户姓名不能为空'.$data['name']);
        }
        $data['deposit_name'] = $_POST['deposit_name'];
        if (empty($data['deposit_name'])) {
            $this->baoError('卡号不能为空');
        }
        $data['deposit_card'] = $_POST['deposit_card'];
        if (empty($data['deposit_card'])) {
            $this->baoError('开户银行不能为空');
        }

        return $data;
    }



    public function forb($user_id){
        if ($user_id = (int) $user_id) {

                $obj = D('Users');

                $where['user_id']=$user_id;

                if (false !==$obj->where($where)->save(array("frozen"=>1))) {
                    $this->baoSuccess('账号已封禁', U('user/index'));
                }
                $this->baoError('操作失败');

        } else {
            $this->baoError('请选择正确的会员');
        }
    }


    public function allow($user_id){
        if ($user_id = (int) $user_id) {

            $obj = D('Users');

            $where['user_id']=$user_id;

            if (false !==$obj->where($where)->save(array("frozen"=>0))) {
                $this->baoSuccess('账号已解封', U('user/index'));
            }
            $this->baoError('操作失败');

        } else {
            $this->baoError('请选择正确的会员');
        }
    }




    public function duce($user_id){
        if ($user_id = (int) $user_id) {
            $obj = D('Account_log');
            if ($this->isPost()) {
                $data['score']=-$_REQUEST['score'];
                $data['user_id'] = $user_id;
                $data['status']=1;
                $data['remark']="充值";
                if (false !==$obj->add($data)) {
                    $this->baoSuccess('添加成功', U('user/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('user_id', $user_id);
                $this->display();
            }
        } else {
            $this->baoError('请选择正确的会员');
        }
    }


    public function money(){
        $user_id = (int)$this->_get('user_id');
        $savesql=D('Paid');
        if(empty($user_id)) $this->baoError ('请选择用户');
        if(!$detail = D('Users')->find($user_id)){
            $this->baoError('没有该用户！');
        }

        if($this->isPost()){
            $money = (int)  ($this->_post('money') * 100);
            if($money == 0){
                $this->baoError('请输入正确的余额数');
            }

            // $intro =  $this->_post('intro',  'htmlspecialchars');
            if($detail['money'] + $money < 0){
                $this->baoError('余额不足！');
            }

            $arr = array();
            $arr['money'] = $money;
            $arr['user_id'] = $user_id;
            $arr['creatime'] = time();
            $arr['type'] = 1;
            $arr['goon'] = 0;
            $arr['remark'] = '线下充值';
            $arr['is_afect'] = 1;
            //  $Userscash->save($arr);
            $status=$savesql->add($arr);

            if ($status){

                $this->baoSuccess('操作成功',U('user/index'));
            }else{
                $this->baoError('操作失败');
            }

        }else{
            $this->assign('user_id',$user_id);
            $this->display();

        }
    }

    public function notice($user_id){


        if ($user_id = (int) $user_id) {
            $obj = D('Message');
            if ($this->isPost()) {
                $arr['ifread'] = 0;
                $arr['title'] = $_POST['title'];
                $arr['content'] =$_POST['content'];
                $arr['creatime'] = time();
                $arr['user_id'] = $user_id;
                $arr['remark'] = '消息通知';
                if (false !==$obj->add($arr)) {
                    $this->baoSuccess('添加成功', U('user/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('user_id', $user_id);
                $this->display();
            }
        } else {
            $this->baoError('请选择正确的会员');
        }







    }

    private function noticeCheck() {

        $data = $this->checkFields($this->_post('data', false), $this->notice_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        }
        $data['content'] = htmlspecialchars($data['content']);
        if (empty($data['content'])) {
            $this->baoError('说明不能为空');
        }
        return $data;
    }

    public function vipinfo($user_id,$val=0,$remark=null){
        $this->assign('user_id', $user_id);
        $this->assign('val', $val);
        $Userscash = D('Paid');
        import('ORG.Util.Page'); // 导入分页类
        $map['user_id'] = $user_id;
        if($val!=0){
            //具体明细
            if($val==3){
                if($remark=="zhonglei"){
                    $map['remark'] ='中雷';

                }else{

                    $map['remark'] ='用户提现';
                }

            }
            $map['type'] = $val;

        }
        $count = $Userscash->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 15); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Userscash->where($map)->order(array('ID' => 'asc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板

    }


        //待打款
    public function dai_withdraw(){
        $User = D('withdraw');
        import('ORG.Util.Page'); // 导入分页类



        if($business_code= $this->_param('business_code')){

            $map['business_code'] = array('LIKE','%'.$business_code.'%');

            $this->assign('business_code',$business_code);
        }



        if($bg_date = $this->_param('bg_date','htmlspecialchars')){


            if($end_date = $this->_param('end_date','htmlspecialchars')){



                $creatime = strtotime($bg_date);//
                $creatimes = strtotime($end_date."23:59:59");//

                $map['creatime'] = array('GT',$creatime,'LT',$creatimes);



                $this->assign('bg_date',$bg_date);
                $this->assign('end_date',$end_date);

            }



            $creatime = strtotime($bg_date);//
            $map['creatime'] = array('GT',$creatime);
            $this->assign('bg_date',$bg_date);


        }


        if($end_date = $this->_param('end_date','htmlspecialchars')){


            $creatimes = strtotime($end_date."23:59:59");//

            $map['creatime'] = array('LT',$creatimes);
            $this->assign('end_date',$end_date);


        }

        $map['status']=0;
        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        print_r($map);
        foreach($list as $k=>$val){
            $list[$k]['money'] =  $list[$k]['money']/100;
        }

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->display(); // 输出模板

    }

    public function audit_success($id=0){

        if($id!=0){
            $obj=D("withdraw");
            $map['id']=$id;
            $datas['status']=1;
            $datas['withdraw_time']=time();
            if (false !==$obj->where($map)->save($datas)) {

                $this->baoSuccess('操作成功', U('user/dai_withdraw'));
            }

        }

    }
    public function audit_bohui($id=0){
        if($id!=0){
            $obj=D("withdraw");
            $map['id']=$id;
            $datas['status']=2;
            $datas['withdraw_time']=time();
            if (false !==$obj->where($map)->save($datas)) {

                $tixian=$obj->where($map)->find();

                $obj = D('Account_log');
                $data['score']=$tixian['money'];
                $data['user_id'] = $tixian['user_id'];
                $data['status']=1;
                $data['remark']="提现驳回";
                $data['creatime']=time();
                if (false !==$obj->add($data)){
                    $this->baoSuccess('驳回操作成功', U('user/dai_withdraw'));
                }

            }

        }

    }

    public function yi_withdraw(){
        $User = D('withdraw');
        import('ORG.Util.Page'); // 导入分页类


        if($business_code= $this->_param('business_code')){

            $map['business_code'] = array('LIKE','%'.$business_code.'%');

            $this->assign('business_code',$business_code);
        }



        if($bg_date = $this->_param('bg_date','htmlspecialchars')){


            if($end_date = $this->_param('end_date','htmlspecialchars')){



                $creatime = strtotime($bg_date);//
                $creatimes = strtotime($end_date."23:59:59");//

                $map['creatime'] = array('GT',$creatime,'LT',$creatimes);



                $this->assign('bg_date',$bg_date);
                $this->assign('end_date',$end_date);

            }



            $creatime = strtotime($bg_date);//
            $map['creatime'] = array('GT',$creatime);
            $this->assign('bg_date',$bg_date);


        }


        if($end_date = $this->_param('end_date','htmlspecialchars')){


            $creatimes = strtotime($end_date."23:59:59");//

            $map['creatime'] = array('LT',$creatimes);
            $this->assign('end_date',$end_date);


        }

        $map['status']=1;

        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$val){

            $list[$k]['money'] =  $list[$k]['money']/100;
        }

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->display(); // 输出模板



    }


    public function bohui(){
        $User = D('withdraw');
        import('ORG.Util.Page'); // 导入分页类


        if($user_id= $this->_param('user_id')){

            $map['user_id'] = array('LIKE','%'.$user_id.'%');

            $this->assign('user_id',$user_id);
        }



        if($bg_date = $this->_param('bg_date','htmlspecialchars')){


            if($end_date = $this->_param('end_date','htmlspecialchars')){



                $creatime = strtotime($bg_date);//
                $creatimes = strtotime($end_date."23:59:59");//

                $map['creatime'] = array('GT',$creatime,'LT',$creatimes);



                $this->assign('bg_date',$bg_date);
                $this->assign('end_date',$end_date);

            }



            $creatime = strtotime($bg_date);//
            $map['creatime'] = array('GT',$creatime);
            $this->assign('bg_date',$bg_date);


        }


        if($end_date = $this->_param('end_date','htmlspecialchars')){


            $creatimes = strtotime($end_date."23:59:59");//

            $map['creatime'] = array('LT',$creatimes);
            $this->assign('end_date',$end_date);


        }


        $map['status']=2;

        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$val){

            $list[$k]['money'] =  $list[$k]['money']/100;
        }

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->display(); // 输出模板

    }
}

