<?php
class UserAction extends CommonAction {
    private $create_fields = array('account', 'password','rank_id', 'face','mobile','email','nickname','face','ext0');
    private $edit_fields = array('account', 'password','rank_id','face', 'mobile','email','nickname','face','ext0');
    private $notice_fields = array('title', 'content');
    //推广金奖励
//获取分销信息数据
//获取分销级
//匹配字符串，获取分销数额
    private $numLevel;
    private $globalEdu;//会员充值额度;
    private $priceAry=array();
    private $vipAry=array();
    private $priceString;
    private $current_userid;
    private $len;
    private $pidAry=array();
    private $gametype;
    public function fzmoney(){
        $EX = D('Usersex');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('frozen_money'=>array('GT',0));
        if($is_no_frozen = (int)$this->_param('is_no_frozen')){
            if($is_no_frozen == 1){
                $map['is_no_frozen'] = 1;
            }else{
                $map['is_no_frozen'] = 0;
            }
            $this->assign('is_no_frozen',$is_no_frozen);
        }    
        if($is_tui_money = (int)$this->_param('is_tui_money')){
            if($is_tui_money == 1){
                $map['is_tui_money'] = 1;
            }else{
                $map['is_tui_money'] = 0;
            }
            $this->assign('is_tui_money',$is_tui_money);
        }  
        $count = $EX->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $EX->where($map)->order(array('user_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $user_ids = $invites_id = array();
        foreach($list as $k=>$val){
            $user_ids[$val['user_id']] = $val['user_id'];
        }
        $users = D('Users')->itemsByIds($user_ids);
        foreach($users as $v){
            if(!empty($v['invite1']))$invites_id[$v['invite1']] = $v['invite1'];
            if(!empty($v['invite2']))$invites_id[$v['invite2']] = $v['invite2'];
            if(!empty($v['invite3']))$invites_id[$v['invite3']] = $v['invite3'];
            if(!empty($v['invite4']))$invites_id[$v['invite4']] = $v['invite4'];
            if(!empty($v['invite5']))$invites_id[$v['invite5']] = $v['invite5'];
            if(!empty($v['invite6']))$invites_id[$v['invite6']] = $v['invite6'];
        }
        $inviteUsers = D('Users')->itemsByIds($invites_id);
        $inviteUsersex = $EX -> itemsByIds($invites_id);
        $this->assign('inviteUsers',$inviteUsers);
        $this->assign('inviteUsersex',$inviteUsersex);
        $this->assign('users',$users);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }


    //结算
    public function fzmoneyyes(){
        $user_id = (int)$this->_param('user_id');
        if(!$detail = D('Usersex')->find($user_id)){
            $this->error('没有要发放奖励的记录');
        }
        if(empty($detail['frozen_money'])|| $detail['is_tui_money'] ==1){
            $this->error('没有要发放奖励的记录');
        }
        $user = D('Users')->find($user_id);
        if(empty($user)){
            $this->error('没有要发放奖励的用户');
        }
        $userids = array();
        if(!empty($user['invite1'])) $userids[] = $user['invite1'];
        if(!empty($user['invite2'])) $userids[] = $user['invite2'];
        if(!empty($user['invite3'])) $userids[] = $user['invite3'];
        if(!empty($user['invite4'])) $userids[] = $user['invite4'];
        if(!empty($user['invite5'])) $userids[] = $user['invite5'];
        if(!empty($user['invite6'])) $userids[] = $user['invite6'];
       if(empty($userids)){
            D('Usersex')->save(array('user_id'=>$user_id,'is_tui_money'=>1));    
        }else{
            $ids =array();
            $userexs = D('Usersex')->itemsByIds($userids);
            foreach($userexs as $k=>$v){
                if(!empty($v['frozen_money'])) {
                    $ids[$v['user_id']]=$v['user_id']; 
                }
            }
            if(!empty($ids)){
                if(D('Usersex')->save(array('user_id'=>$user_id,'is_tui_money'=>1))){
                    if($this->_CONFIG['quanming']['money6'] && $user['invite6'] && isset($ids[$user['invite6']])){
                        D('Users')->addMoney($user['invite6'],$this->_CONFIG['quanming']['money6']*100,'推广员提成');
                    }
                    if($this->_CONFIG['quanming']['money5'] && $user['invite5'] && isset($ids[$user['invite5']])){
                        D('Users')->addMoney($user['invite5'],$this->_CONFIG['quanming']['money5']*100,'推广员提成');
                    }
                    if($this->_CONFIG['quanming']['money4'] && $user['invite4'] && isset($ids[$user['invite4']])){
                        D('Users')->addMoney($user['invite4'],$this->_CONFIG['quanming']['money4']*100,'推广员提成');
                    }
                    if($this->_CONFIG['quanming']['money3'] && $user['invite3'] && isset($ids[$user['invite3']])){
                        D('Users')->addMoney($user['invite3'],$this->_CONFIG['quanming']['money3']*100,'推广员提成');
                    }
                    if($this->_CONFIG['quanming']['money2'] && $user['invite2'] && isset($ids[$user['invite2']])){
                        D('Users')->addMoney($user['invite2'],$this->_CONFIG['quanming']['money2']*100,'推广员提成');
                    }
                    if($this->_CONFIG['quanming']['money1'] && $user['invite1'] && isset($ids[$user['invite1']])){
                        D('Users')->addMoney($user['invite1'],$this->_CONFIG['quanming']['money1']*100,'推广员提成');
                    }
                }
            }else{
                D('Usersex')->save(array('user_id'=>$user_id,'is_tui_money'=>1));      
            }
        }
        $this->success('发放奖励成功', U('user/fzmoney'));
    }
   

    public function index() {
        $User = D('Users');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed'=>array('IN','0,-1'));

        if($user_id= $this->_param('user_id','htmlspecialchars')){

            $map['user_id'] = array('LIKE','%'.$user_id.'%');

            $this->assign('user_id',$user_id);
        }

        if($account = $this->_param('account','htmlspecialchars')){
            $map['account'] = array('LIKE','%'.$account.'%');
            $this->assign('account',$account);
        }

        if($nickname = $this->_param('nickname','htmlspecialchars')){
            $map['nickname'] = array('LIKE','%'.$nickname.'%');
            $this->assign('nickname',$nickname);
        }
		
		if($mobile = $this->_param('mobile','htmlspecialchars')){
            $map['mobile'] = array('LIKE','%'.$mobile.'%');
            $this->assign('mobile',$mobile);
        }


        if($rank_id = (int)$this->_param('rank_id')){
            $map['rank_id'] = $rank_id;
            $this->assign('rank_id',$rank_id);
        }


        if($ext0 = $this->_param('ext0','htmlspecialchars')){
            $map['ext0'] = array('LIKE','%'.$ext0.'%');
            $this->assign('ext0',$ext0);
        }

        
        $count = $User->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('user_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$val){

            if ($val['vip'] == 1){
                $val['vip']="是";

            }else{
                $val['vip']="否";

            }

            $val['reg_ip_area'] = $this->ipToArea($val['reg_ip']);
            $val['last_ip_area']   = $this->ipToArea($val['last_ip']);
            $list[$k] = $val;
        }
      //  print_r($list);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('ranks',D('Userrank')->fetchAll());
        $this->display(); // 输出模板

    }
    public function viplist() {
        $User = D('Users');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed'=>array('IN','0,-1'));

        if($user_id= $this->_param('user_id','htmlspecialchars')){

            $map['user_id'] = array('LIKE','%'.$user_id.'%');

            $this->assign('user_id',$user_id);
        }

        if($account = $this->_param('account','htmlspecialchars')){
            $map['account'] = array('LIKE','%'.$account.'%');
            $this->assign('account',$account);
        }

        if($nickname = $this->_param('nickname','htmlspecialchars')){
            $map['nickname'] = array('LIKE','%'.$nickname.'%');
            $this->assign('nickname',$nickname);
        }

        if($mobile = $this->_param('mobile','htmlspecialchars')){
            $map['mobile'] = array('LIKE','%'.$mobile.'%');
            $this->assign('mobile',$mobile);
        }


        if($rank_id = (int)$this->_param('rank_id')){
            $map['rank_id'] = $rank_id;
            $this->assign('rank_id',$rank_id);
        }


        if($ext0 = $this->_param('ext0','htmlspecialchars')){
            $map['ext0'] = array('LIKE','%'.$ext0.'%');
            $this->assign('ext0',$ext0);
        }

        $map['vip']=1;
        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('user_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$val){

            if ($val['vip'] == 1){
                $val['vip']="是";

            }else{
                $val['vip']="否";

            }

            $val['reg_ip_area'] = $this->ipToArea($val['reg_ip']);
            $val['last_ip_area']   = $this->ipToArea($val['last_ip']);
            $list[$k] = $val;
        }
        //  print_r($list);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('ranks',D('Userrank')->fetchAll());
        $this->display(); // 输出模板

    }

    public function jiqiren_hb() {
        $User = D('Users');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed'=>array('IN','0,-1'));

        if($user_id= $this->_param('user_id','htmlspecialchars')){

            $map['user_id'] = array('LIKE','%'.$user_id.'%');

            $this->assign('user_id',$user_id);
        }

        if($account = $this->_param('account','htmlspecialchars')){
            $map['account'] = array('LIKE','%'.$account.'%');
            $this->assign('account',$account);
        }

        if($nickname = $this->_param('nickname','htmlspecialchars')){
            $map['nickname'] = array('LIKE','%'.$nickname.'%');
            $this->assign('nickname',$nickname);
        }

        if($mobile = $this->_param('mobile','htmlspecialchars')){
            $map['mobile'] = array('LIKE','%'.$mobile.'%');
            $this->assign('mobile',$mobile);
        }


        if($rank_id = (int)$this->_param('rank_id')){
            $map['rank_id'] = $rank_id;
            $this->assign('rank_id',$rank_id);
        }


        if($ext0 = $this->_param('ext0','htmlspecialchars')){
            $map['ext0'] = array('LIKE','%'.$ext0.'%');
            $this->assign('ext0',$ext0);
        }

        $map['is_robot']=1;
        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('user_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$val){
            if ($val['vip'] == 1){
                $val['vip']="是";

            }else{
                $val['vip']="否";

            }
            $val['reg_ip_area'] = $this->ipToArea($val['reg_ip']);
            $val['last_ip_area']   = $this->ipToArea($val['last_ip']);
            $list[$k] = $val;
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('ranks',D('Userrank')->fetchAll());
        $this->display(); // 输出模板

    }

    public function jiqiren_91() {
        $User = D('Users');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed'=>array('IN','0,-1'));

        if($user_id= $this->_param('user_id','htmlspecialchars')){

            $map['user_id'] = array('LIKE','%'.$user_id.'%');

            $this->assign('user_id',$user_id);
        }

        if($account = $this->_param('account','htmlspecialchars')){
            $map['account'] = array('LIKE','%'.$account.'%');
            $this->assign('account',$account);
        }

        if($nickname = $this->_param('nickname','htmlspecialchars')){
            $map['nickname'] = array('LIKE','%'.$nickname.'%');
            $this->assign('nickname',$nickname);
        }

        if($mobile = $this->_param('mobile','htmlspecialchars')){
            $map['mobile'] = array('LIKE','%'.$mobile.'%');
            $this->assign('mobile',$mobile);
        }


        if($rank_id = (int)$this->_param('rank_id')){
            $map['rank_id'] = $rank_id;
            $this->assign('rank_id',$rank_id);
        }


        if($ext0 = $this->_param('ext0','htmlspecialchars')){
            $map['ext0'] = array('LIKE','%'.$ext0.'%');
            $this->assign('ext0',$ext0);
        }

        $map['is_robot']=2;
        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('user_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$val){
            if ($val['vip'] == 1){
                $val['vip']="是";

            }else{
                $val['vip']="否";

            }
            $val['reg_ip_area'] = $this->ipToArea($val['reg_ip']);
            $val['last_ip_area']   = $this->ipToArea($val['last_ip']);
            $list[$k] = $val;
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('ranks',D('Userrank')->fetchAll());
        $this->display(); // 输出模板

    }
    public function select(){
        $User = D('Users');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed'=>array('IN','0,-1'));
        if($account = $this->_param('account','htmlspecialchars')){
            $map['account'] = array('LIKE','%'.$account.'%');
            $this->assign('account',$account);
        }
        if($nickname = $this->_param('nickname','htmlspecialchars')){
            $map['nickname'] = array('LIKE','%'.$nickname.'%');
            $this->assign('nickname',$nickname);
        }
        if($ext0 = $this->_param('ext0','htmlspecialchars')){
            $map['ext0'] = array('LIKE','%'.$ext0.'%');
            $this->assign('ext0',$ext0);
        }
        $count = $User->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 8); // 实例化分页类 传入总记录数和每页显示的记录数
        $pager = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('user_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $pager); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function selectapp(){
        $User = D('Users');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed'=>array('IN','0,-1'));
        if($account = $this->_param('account','htmlspecialchars')){
            $map['account'] = array('LIKE','%'.$account.'%');
            $this->assign('account',$account);
        }
        if($nickname = $this->_param('nickname','htmlspecialchars')){
            $map['nickname'] = array('LIKE','%'.$nickname.'%');
            $this->assign('nickname',$nickname);
        }
        if($ext0 = $this->_param('ext0','htmlspecialchars')){
            $map['ext0'] = array('LIKE','%'.$ext0.'%');
            $this->assign('ext0',$ext0);
        }
		$join = ' inner join '.C('DB_PREFIX').'app_user a on a.user_id = '.C('DB_PREFIX').'users.user_id';
        $count = $User->where($map)->join($join)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 8); // 实例化分页类 传入总记录数和每页显示的记录数
        $pager = $Page->show(); // 分页显示输出
        $list = $User->where($map)->join($join)->order(array(C('DB_PREFIX').'users.user_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $pager); // 赋值分页输出
        $this->display(); // 输出模板
    }


    public function create() {
        if ($this->isPost()) {


            $data = $this->createCheck();
            $obj = D('Users');
            $user_id=$obj->add($data);
            if($user_id>0){
                //$obj->addmoney($user_id,388,1,1,"注册赠送",0);
                $this->baoSuccess('添加成功'.$user_id, U('user/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('ranks',D('Userrank')->fetchAll());
            $this->display();
        }
    }

    public function create91() {
        if ($this->isPost()) {
            $this->baoError('操作失败！');

            $data = $this->createCheck();
            $obj = D('Users');
            $data['is_robot']=2;
            $user_id=$obj->add($data);
            if($user_id>0){
                $this->baoSuccess('添加成功', U('user/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('ranks',D('Userrank')->fetchAll());
            $this->display();
        }
    }

    public function inrobotuidredis(){


        Cac()->del('jiuyi_robot_uid');
        $robotuids = D('Users')->where(array('is_robot'=>2))->select();
        foreach ($robotuids as $v){
            Cac()->rPush('jiuyi_robot_uid',$v['user_id']);
        }
        Cac()->lRange('jiuyi_robot_uid',0,-1);
        $this->baoSuccess('添加成功', U('user/jiqiren_91'));

    }


    private function createCheck() {
       // $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['account'] = $_POST['account'];
        if (empty($data['account'])) {
            $this->baoError('账户不能为空');
        } 
//        if(D('Users')->getUserByAccount($data['account'])){
//            $this->baoError('该账户已经存在！');
//        }
        $data['password'] = $_POST['password'];
        if (empty($data['password'])) {
            $this->baoError('密码不能为空');
        } 
        $data['password'] = md5($data['password']);
        $data['nickname'] = $_POST['nickname'];
        if (empty($data['nickname'])) {
            $this->baoError('昵称不能为空');
        }
        $data['mobile']=$_POST['mobile'];
        $data['is_robot']=$_POST['is_robot'];
        if ($data['is_robot'] == 1){
            Cac()->delete("randUserList");
        }
//        $data['rank_id'] = (int)$data['rank_id'];
//		$data['email'] = htmlspecialchars($data['email']);
        $data['face'] ="img/avatar.png";
//        $data['ext0'] = htmlspecialchars($data['ext0']);
       $data['money']=0;
       $data['frozen']=0;
       $data['reg_time']=time();
       $data['last_ip']=get_client_ip();
       $data['email']="";
       $data['pid']=$_POST['pid'];
       $data['token']="";


        $data['reg_ip'] = get_client_ip();
        $data['reg_time'] = NOW_TIME;
        return $data;
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
  $data['pid'] = $_POST['pid'];
//        if (empty($data['pid'])) {
//
//            $this->baoError('pid不能为空');
//        }
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

     public function audit($user_id = 0) {
        if (is_numeric($user_id) && ($user_id = (int) $user_id)) {
            $obj = D('Users');
            $obj->save(array('user_id'=>$user_id,'closed'=>0));
            $this->baoSuccess('审核成功！', U('user/index'));
        } else {
            $user_id = $this->_post('user_id', false);
            if (is_array($user_id)) {
                $obj = D('Users');
                foreach ($user_id as $id) {
                    $obj->save(array('user_id'=>$id,'closed'=>0));
                }
                $this->baoSuccess('审核成功！', U('user/index'));
            }
            $this->baoError('请选择要审核的会员');
        }
    }

    public function integral(){
       $user_id = (int)$this->_get('user_id'); 
       if(empty($user_id)) $this->baoError ('请选择用户');
       if(!$detail = D('Users')->find($user_id)){
           $this->baoError('没有该用户！');
       }
       if($this->isPost()){
           $integral = (int)  $this->_post('integral');
           if($integral == 0){
               $this->baoError('请输入正确的积分数');
           }
           $intro =  $this->_post('intro',  'htmlspecialchars');
           if($detail['integral'] + $integral < 0){
               $this->baoError('积分余额不足！');
           }
           D('Users')->save(array(
               'user_id'=>$user_id,
               'integral'=> $detail['integral'] + $integral
           ));

           D('Userintegrallogs')->add(array(
               'user_id' => $user_id,
               'integral'=>$integral,
               'intro' => $intro,
               'create_time' => NOW_TIME,
               'create_ip'  => get_client_ip()
           ));
           $this->baoSuccess('操作成功',U('userintegrallogs/index'));
       }else{
           $this->assign('user_id',$user_id);
           $this->display();
       }       
    }

    public function gold(){
       $user_id = (int)$this->_get('user_id'); 
       if(empty($user_id)) $this->baoError ('请选择用户');
       if(!$detail = D('Users')->find($user_id)){
           $this->baoError('没有该用户！');
       }
       if($this->isPost()){
           $gold = (int)  $this->_post('gold');
           if($gold == 0){
               $this->baoError('请输入正确的金块数');
           }
           $intro =  $this->_post('intro',  'htmlspecialchars');
           if($detail['gold'] + $gold < 0){
               $this->baoError('金块余额不足！');
           }
           D('Users')->save(array(
               'user_id'=>$user_id,
               'gold'=> $detail['gold'] + $gold
           ));
           D('Usergoldlogs')->add(array(
               'user_id' => $user_id,
               'gold'=>$gold,
               'intro' => $intro,
               'create_time' => NOW_TIME,
               'create_ip'  => get_client_ip()
           ));
           $this->baoSuccess('操作成功',U('usergoldlogs/index'));
       }else{
           $this->assign('user_id',$user_id);
           $this->display();
       }       
    }

    public function manage(){
       $user_id = (int)$this->_get('user_id'); 
       if(empty($user_id)) $this->error ('请选择用户');
       if(!$detail = D('Users')->find($user_id)){
           $this->error('没有该用户！');
       }
       setUid($user_id);
       header("Location:".U('member/index/index'));
       die;
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

    public function vip(){
        $user_id = (int)$this->_get('user_id');
        $savesql=D('Users');
        if(empty($user_id)) $this->baoError ('请选择用户');
        if(!$detail = D('Users')->find($user_id)){
            $this->baoError('没有该用户！');
        }

        if($this->isPost()){
            $vip = (int)  ($this->_post('vip') );
            if($vip >1 || $vip<0){
                $this->baoError('请输入正确的信息');
            }

            if ($vip == 1){

                $arr['vip'] = $vip;
                $where['user_id']=$user_id;
                $status=$savesql->where($where)->save($arr);
                if ($status){
                    // 会员升级返佣
                    $order=M('order');
                    $data1['user_id']=$user_id;
                    $data1['out_trade_no']= $user_id.time().rand(1000,9999);
                    $data1['total_amount']= 598*100;
                    $data1['subject']='后台充值vip';
                    $data1['notify_time']=time();
                    $data1['status']='1';
                    $data1['zhifubao']=0;

                    $order->add($data1);

                    $this->fanyong($user_id,598*100,'vipbuy');
                    $this->baoSuccess('操作成功',U('user/index'));
                }else{
                    $this->baoError('操作失败');
                }
            }else{
                $arr['vip'] = $vip;
                $where['user_id']=$user_id;
                $status=$savesql->where($where)->save($arr);
                if ($status){
                    $this->baoSuccess('操作成功',U('user/index'));
                }else{
                    $this->baoError('操作失败');
                }
            }
        }else{
            $this->assign('user_id',$user_id);
            $this->display();
        }
    }
    //线上购买会员列表
    public function viprecord_s(){
        $Userscash = D('Order');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('status'=>1);
        $map['subject']='购买vip';

        $count = $Userscash->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Userscash->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
       // print_r($list);
        foreach($list as $k=>$val){
            // $val['money_yu']=D('Users')->getUserMoney($val['user_id']);

            $val['total_amount']= $val['total_amount']/100;
//            $val['bank_userName']=$this->getbankInfo($val['user_id'],1);
//            $val['bank_info']=$this->getbankInfo($val['user_id'],2);
            $list[$k] = $val;
        }


        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->display(); // 输出模板


    }
    //线下购买会员列表
    public function viprecord_x(){

        $Userscash = D('Order');
        import('ORG.Util.Page'); // 导入分页类
         $map = array('status'=>1);
        $map['subject']='后台充值vip';

        $count = $Userscash->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Userscash->where($map)->order(array('order_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
      //  print_r($list);
        foreach($list as $k=>$val){
            // $val['money_yu']=D('Users')->getUserMoney($val['user_id']);

            $val['total_amount']= $val['total_amount']/100;
//            $val['bank_userName']=$this->getbankInfo($val['user_id'],1);
//            $val['bank_info']=$this->getbankInfo($val['user_id'],2);
            $list[$k] = $val;
        }


        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->display(); // 输出模板


    }


    public function fanyong($user_id,$mianis_edu,$type){



        //先判断自身 是否 是vip 不是 则不执行
        $selfSql=D("Users");
        $where['user_id']=$user_id;
        $slef=$selfSql->where($where)->find();
        if($slef["vip"]==0){
            return;
        }

        global $current_userid,$globalEdu,$gametype;
        $current_userid=$user_id;
        $globalEdu=$mianis_edu;
        $gametype=$type;
        $distribution=D("distribution");
        $where['ID']='4';
        $line=$distribution->where($where)->find();

        global $numLevel;
        $numLevel=$line['numRen'];
        global $len;
        $len=$numLevel;

        $priceString=$line['price'];
        global $priceAry;
        $priceAry = explode(",",$priceString);//分销额度存入数组

        $this->allPid($current_userid);

    }


//获取uid的所有vip上级
    function allPid($curId){

        global $len;

        if($len==0){

            $this->filter();//过滤无效的pid
            return;
        }else{
            $len--;
        }

        $users=D('Users');
        $where['user_id']=$curId;
        $line_pid=$users->where($where)->find();

        $next_userid=$line_pid["pid"];//当前上级ID
        global $pidAry;
        array_push($this->pidAry,$next_userid);

        //当前上级ID 身份状态
        $map['user_id']=$next_userid;
        $line_vip=$users->where($map)->find();
        $next_user_vip=$line_vip["vip"];//当前上级身份
        global $vipAry;
        array_push($this->vipAry,$next_user_vip);
        //----------------------
        $this->allPid($next_userid);

    }
//过滤pid
    public function filter(){
        global $pidAry,$vipAry;
        //获取真实vip的数组长度
        $vipAryLen=count($this->vipAry);
        for($i=0;$i<$vipAryLen;$i++){
            if($this->vipAry[$i]==0){
                //移除对应的pid数组
                unset($this->pidAry[$i]);
            }

        }
        $this->pidAry=array_values($this->pidAry);
        $this->addFenYong();//数据保存

    }

//对应返佣额度
    private function addFenYong(){

        global $len,$pidAry,$numLevel,$priceAry,$globalEdu,$current_userid,$gametype;
        //获取真实pid的数组长度

        $pidAryLen=count($this->pidAry);

        if ($pidAryLen==$len){
            return;
        }


        $newPrice=$priceAry[$len];
        $p1id=$this->pidAry[$len];

        $fanyong=D('fanyong');
        $data['fabao_id']=$current_userid;
        $data['miansi_edu']=$globalEdu;
        $data['fenyong_id']=$p1id;
        $data['fenyong_edu']=$newPrice*$globalEdu/100;
        $data['type']=$gametype;
        $data['Lv']=$len+1;
        $data['fyDate']=time();
        $fanyong->add($data);
        //佣金插入现金表
        $paid=D('Paid');
        $map['money']=$newPrice*$globalEdu/100;
        $map['user_id']=$p1id;
        $map['creatime']=time();
        $map['type']=13;
        $map['remark']='下级购买会员佣金到账';
        $map['is_afect']=1;
        $paid->add($map);
        $len++;
        $this->addFenYong();
    }





    public function notice(){
        $user_id=(int)$_GET['user_id'];
        $this->assign('vars', $user_id);
        $savesql=D('Message');
        if ($user_id){
            if ($this->isPost()){
                $data=$this->noticeCheck();
                $arr = array();
                $arr['ifread'] = 0;
                $arr['title'] = $data['title'];
                $arr['content'] =$data['content'];
                $arr['time'] = time();
                $arr['user_id'] = $user_id;
                $arr['remark'] = '驳回通知';
                $savesql->add($arr);

                $this->baoSuccess('操作成功',U('user/index'));

            } else {
                $this->display();
            }


        }else{

            $this->baoError('请选择要编辑的会员');
        }

    }

    private function noticeCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->notice_fields);
        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        } $data['content'] = htmlspecialchars($data['content']);
        if (empty($data['content'])) {
            $this->baoError('说明不能为空');
        }
        return $data;
    }
    public function robot() {
        $User = D('Users');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('is_robot'=>1);

        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('user_id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach($list as $k=>$val){
            $val['reg_ip_area'] = $this->ipToArea($val['reg_ip']);
            $val['last_ip_area']   = $this->ipToArea($val['last_ip']);
            $list[$k] = $val;
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('ranks',D('Userrank')->fetchAll());
        $this->display(); // 输出模板

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


    /**
     * @return array
     */
    public function vipshang()
    {
        $user_id=(int)$_GET['user_id'];
        $this->assign('user_id', $user_id); // 赋值数据集
        $Userscash = D('Users');

        $map['user_id'] = $user_id;
      //  $list = $Userscash->where($map)->select();
        $data = $Userscash->where($map)->find($map);
        $this->assign('pid', $data['pid']);
        //根据pid 查询信息
        import('ORG.Util.Page'); // 导入分页类
        $map['user_id'] = $data['pid'];
        $list = $Userscash->where($map)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->display(); // 输出模板


    }
    public function vipxia()
    {
        $user_id=(int)$_GET['user_id'];
        $this->assign('user_id', $user_id); // 赋值数据集
        $Userscash = D('Users');


        //根据pid 查询信息
        import('ORG.Util.Page'); // 导入分页类
        $map['pid'] = $user_id;
        $list = $Userscash->where($map)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->display(); // 输出模板


    }

}

