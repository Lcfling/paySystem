<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 11:49
 */
class MycenterAction extends CommonAction{
    /**
     *我的账户
     */
    public function  getaccount(){
        $user_id = $this->uid;
        $tolscore = D('Account')->gettolscore($user_id);
        $tolbrokerage = D('Account')->gettolbrokerage($user_id);
        $daybrokerage = D('Account')->getdaybrokerage($user_id,5);
        $wxQRnum =D('Erweima')->where(array('user_id'=>$user_id,'status'=>0,'type'=>1))->count();
        $zfbQRnum =D('Erweima')->where(array('user_id'=>$user_id,'status'=>0,'type'=>2))->count();
        $data =array(
            'tolscore'=>$tolscore/100,
            'tolbrokerage'=>$tolbrokerage/100,
            'daybrokerage'=>$daybrokerage/100,
            'wxQRnum'=>$wxQRnum,
            'zfbQRnum'=>$zfbQRnum
        );
        $this->ajaxReturn($data,'请求成功!',1);
    }
    /**
     *个人信息
     */
    public function  getuserinfo(){
        $this->ajaxReturn($this->member,'请求成功!',1);
    }

    /**
     * 充值记录
     */
    public function recharge_list(){
        if($this->isPost()){
            $user_id = $this->uid;
            $list =D('Account_log')->where(array('user_id'=>$user_id,'status'=>1))->order('creatime desc')->field('score,status,creatime')->select();
            foreach ($list as $k=>&$v){
                $v['money'] = $v['score']/100;
                $v['creatime']= date('Y/m/d H:i:s',$v['creatime']);
            }
            $this->ajaxReturn($list,'请求成功!',1);
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    /**
     * 提现校验
     */
    public function withdraw_check(){
        if($this->isPost()){
            $user_id = $this->uid;
            $money = $_POST['money'] * 100;
            $tolscore = D('Account')->gettolscore($user_id);
            $userinfo =D('users')->where(array('user_id'=>$user_id))->find();
            if($money > $tolscore){
                $this->ajaxReturn('','提现金额大于总金额!',0);
            }
            if(empty($userinfo['zf_pwd'])){
                $this->ajaxReturn('','未设置支付密码,请去设置!',2);
            }
            if($userinfo['take_status'] == 1){
                $this->ajaxReturn('','你处于接单状态,无法提现!',0);
            }
            $this->ajaxReturn($_POST,'请求成功!',1);
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }

    }

    /**
     * 提现
     */
    public function withdraw(){
        if($this->isPost()){
            $user_id = $this->uid;
            $money = $_POST['money'] * 100;
            $zf_pwd = md5($_POST['zf_pwd']);
            $tolscore = D('Account')->gettolscore($user_id);
            $userinfo =D('users')->where(array('user_id'=>$user_id))->find();
            if($money > $tolscore){
                $this->ajaxReturn('','提现金额大于总金额!',0);
            }
            if(empty($userinfo['zf_pwd'])){
                $this->ajaxReturn('','未设置支付密码,请去设置!',2);
            }
            if($userinfo['take_status'] == 1){
                $this->ajaxReturn('','你处于接单状态,无法提现!',0);
            }
            if($userinfo['zf_pwd'] != $zf_pwd){
                $this->ajaxReturn('','密码错误,请重新输入!',0);
            }
            $data =array(
                'user_id'=>$user_id,
                'order_no'=>$this->getrequestId(),
                'mobile'=>$userinfo['mobile'],
                'money'=>$money,
                'wx_name'=>$userinfo['wx_name'],
                'name'=>$userinfo['name'],
                'deposit_name'=>$userinfo['deposit_name'],
                'deposit_card'=>$userinfo['deposit_card'],
                'creatime'=>time(),
            );
            $res = D('Withdraw')->add($data);
            if($res){
                $data1 = array(
                    'user_id'=>$user_id,
                    'score'=>-$money,
                    'status'=>6,
                    'remark'=>"提现",
                );
                D('Account_log')->add($data1);
                $this->ajaxReturn('','已提交!',1);
            }
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }

    }

    /**
     * 提现列表
     */
    public function withdraw_list(){
        if($this->isPost()){
            $user_id = $this->uid;
            $list = D('Withdraw')->where(array('user_id'=>$user_id))->order('creatime desc')->select();
            foreach ($list as $k=>&$v){
                $v['money']=$v['money']/100;
                $v['creatime']= date('Y/m/d H:i:s',$v['creatime']);
                $v['withdraw_time']= date('Y/m/d H:i:s',$v['withdraw_time']);
            }
            $this->ajaxReturn($list,'请求成功!',1);

        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }

    }

    /**
     * 设置支付密码
     */
    public function setpass(){
        if($this->isPost()){
            $user_id = $this->uid;
            $pass = md5($_POST['pass']);
            $savestatus = D('Users')->where(array('user_id'=>$user_id))->field('zf_pwd')->save(array('zf_pwd'=>$pass));
            if($savestatus){
                $this->ajaxReturn('','设置成功!',1);
            }else{
                $this->ajaxReturn('','设置失败!',0);
            }


        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    /**
     * 设置登录密码
     */
    public function setlogpass(){
        if($this->isPost()){
            $user_id = $this->uid;
            $logpass = md5($_POST['logpass']);
            $savestatus = D('Users')->where(array('user_id'=>$user_id))->field('password')->save(array('password'=>$logpass));
            if($savestatus){
                $this->ajaxReturn('','设置成功!',1);
            }else{
                $this->ajaxReturn('','设置失败!',0);
            }


        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    /**
     * 实名认证
     */
    public function real_name(){
        if($this->isPost()){
            $data = $_POST;
            $user_id = $this->uid;
            if(empty($data['mobile'])){
                $this->ajaxReturn('','手机号不能为空!',0);
            }
            if(empty($data['wx_name'])){
                $this->ajaxReturn('','微信名称不能为空!',0);
            }
            if(empty($data['name'])){
                $this->ajaxReturn('','真实姓名不能为空!',0);
            }
            if(empty($data['deposit_name'])){
                $this->ajaxReturn('','银行卡名称不能为空!',0);
            }
            if(empty($data['deposit_card'])){
                $this->ajaxReturn('','银行卡号不能为空!',0);
            }
            $savestatus = D('Users')->where(array('user_id'=>$user_id))->field('mobile,wx_name,name,deposit_name,deposit_card')->save($data);
            if($savestatus){
                $this->ajaxReturn('','更改成功!',1);
            }else{
                $this->ajaxReturn('','更改失败!',0);
            }

        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }

    }

    /**
     * 二维码管理展示
     */
    public function qrcode(){
        if($this->isPost()){
            $user_id = $this->uid;
            $listkey = $_POST['list'];
            $type =$_POST['type'];
            $qrcodeinfo = D('Erweima')->where(array('status'=>0,'user_id'=>$user_id,'list'=>$listkey,'type'=>$type))->select();
            if($qrcodeinfo){
                foreach ($qrcodeinfo as $k=>&$v){
                    $v['edu'] = number_format($v['edu']/100,2);
                    $v['creatime'] = date('Y/m/d H:i:s',$v['creatime']);
                }
                $this->ajaxReturn($qrcodeinfo,'请求成功!',1);
            }else{
                $this->ajaxReturn('','暂无数据!',0);
            }

        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }
    /**
     *二维码删除
     */
    public function qrcodedel(){
        if($this->isPost()){
            $user_id = $this->uid;
            $id = $_POST['id'];
            if($list =D('Erweima')->where(array('id'=>$id,'user_id'=>$user_id,'status'=>0))->find()){
                $savestatus = D('Erweima')->where(array('id'=>$id,'user_id'=>$user_id,'status'=>0))->field('status,savetime')->save(array('status'=>1,'savetime'=>time()));
                $moneys = $list['list'];
                Cac()->lRem("erweimas".$moneys.$user_id,0,$id);
                if($savestatus){
                    $this->ajaxReturn('','删除成功!',1);
                }else{
                    $this->ajaxReturn('','删除失败!',0);
                }
            }
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }


    /**
     * 设置 更改密码  验证手机验证码
     */
    public function verification(){
        $code=(int)$_POST['code'];   //验证码
        $useinfo =$this->member;
        $mobile = $useinfo['account'];
        $this->verifycat($mobile,'zfpass_code_',$code);
    }
    /**
     * 设置 更改密码验证码
     */
    public function sendcode(){
        $useinfo =$this->member;
        $mobile = $useinfo['account'];
        $this->csendcode($mobile,'zfpass_code_',1);
    }
    /**
     * 设置登录密码 验证手机验证码
     */
    public function logpassverify(){
        $code=(int)$_POST['code'];   //验证码
        $useinfo =$this->member;
        $mobile = $useinfo['account'];
        $this->verifycat($mobile,'logpass_code_',$code);
    }
    /**
     * 设置登录密码 发送验证码
     */
    public function logpasssendcode(){
        $useinfo =$this->member;
        $mobile = $useinfo['account'];
        $this->csendcode($mobile,'logpass_code_',2);
    }

    /**
     * 创建邀请码
     */
    public function createcode(){
        if($this->isPost()){
            $user_id = $this->uid;
            $useinfo =$this->member;
            $imsinum =D('Imsi')->getprinum($user_id);
            if($useinfo['shenfen']>2){
                $this->ajaxReturn('','无权限!',0);
            }
            if((int)$imsinum > (int)$useinfo['imsi_num']){
                $this->ajaxReturn('','生成码已达到上限!',0);
            }
            if($useinfo['shenfen']==1){
                $code =$this->generateCode(2,1);
            }else{
                $code =$this->generateCode(2,3);
            }
            $data=array(
                'user_id'=>$user_id,
                'code'=>$code,
                'grade'=>$useinfo['shenfen'] + 1,
                'status'=>0,
                'creatime'=>time()
            );

            $addtatus = D('Imsi')->field('user_id,code,grade,status,creatime')->add($data);
            if($addtatus){
                $this->ajaxReturn($code,'生成成功!',1);
            }else{
                $this->ajaxReturn('','生成失败!',0);
            }

        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    /**
     * 邀请码列表
     */
    public function codelist(){
        if($this->isPost()){
            $user_id = $this->uid;
            $imsilist = D('Imsi')->where(array('user_id'=>$user_id,'status'=>0))->select();
            foreach ($imsilist as $k=>&$v){
                $v['creatime'] = date('Y/m/d H:i:s',$v['creatime']);
            }
            $this->ajaxReturn($imsilist,'请求成功!',1);
        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    /**
     * 下发码数量
     */
     public function issuecode(){
         if($this->isPost()){
             $user_id = $this->uid;
             $useinfo =$this->member;
             $issuenum = (int)$_POST['issuenum'];
             $bind_id = $_POST['bind_id'];
             $imsinum =D('Imsi')->getimsinum($user_id);
             $imsiprinum =D('Imsi')->getprinum($user_id);
             if(empty($issuenum)){
                 $this->ajaxReturn('','未填写邀请码数量!',0);
             }
             if(empty($bind_id)){
                 $this->ajaxReturn('','下发的代理商id未填写!',0);
             }
             if((int)$issuenum > $useinfo['imsi_num'] - (int)$imsinum - (int)$imsiprinum){
                 $this->ajaxReturn('','邀请码数量不够!',0);
             }
             D('Users')->where(array('user_id'=>$bind_id))->setInc('imsi_num',$issuenum);
             $savestatus =D('Users')->where(array('user_id'=>$user_id))->setDec('imsi_num',$issuenum);
             if($savestatus){
                 $this->ajaxReturn('','下发成功!',1);
             }else{
                 $this->ajaxReturn('','下发失败!',0);
             }

         }else{
             $this->ajaxReturn('','请求数据异常!',0);
         }
     }
    /**
     * 更改分润
     */
    public function changepro(){
        if($this->isPost()){
            $useinfo =$this->member;
            $pronum = (double)$_POST['pronum'];
            $bind_id = (int)$_POST['bind_id'];
            if($pronum > $useinfo['rate']){
                $this->ajaxReturn('','费率不能超过自己的!',0);
            }
            $saverate =D('Users')->where(array('user_id'=>$bind_id))->field('rate')->save(array('rate'=>$pronum));
            if($saverate){
                $this->ajaxReturn('','费率更改成功!',1);
            }else{
                $this->ajaxReturn('','费率更改失败!',0);
            }

        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    /**
     * 分润更改列表
     */
    public function prosavelist(){
        if($this->isPost()){
            $useinfo =$this->member;
            $bind_id = (int)$_POST['bind_id'];
            $bind_rate =D('Users')->where(array('user_id'=>$bind_id))->getField('rate');
            $rate =$useinfo['rate'] * 10000;
            $num =(int)($rate - $bind_rate * 10000)/1000;
            $ratearr =array();
            for ( $i=0;$i< $num-1;$i++ ){
                $ratearr[$i]= ($bind_rate *1000 + $i )/1000;
            }
            if(!empty($ratearr)){
                $this->ajaxReturn($ratearr,'请求成功!',1);
            }else{
                $this->ajaxReturn('','当前分润已无法更改!',0);
            }

        }else{
            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    /**
     * 团队列表
     */
    public function agent_list(){
        if($this->isPost()){
            $user_id = $this->uid;
            $useinfo =$this->member;
            $agentlist =D('Users')->where(array('pid'=>$user_id))->select();
            $pernum =D('Users')->where(array('pid'=>$user_id))->count();
            $list = D('Myinfo')->gettolAgent($agentlist,true);
            $imsinum =D('Imsi')->getprinum($user_id);
            $tolnum = (int)$pernum + count($list);
            $surimsinum = $useinfo['imsi_num']-$imsinum;
            if($agentlist){
                $data =array(
                    'pernum'=>$pernum,
                    'tolnum'=>$tolnum,
                    'surimsinum'=>$surimsinum,
                    'rate'=>$useinfo['rate'],
                    'agentlist'=>$agentlist
                );
                $this->ajaxReturn($data,'请求成功!',1);
            }else{
                $data =array(
                    'pernum'=>0,
                    'tolnum'=>0,
                    'surimsinum'=>$surimsinum,
                    'rate'=>$useinfo['rate'],
                    'agentlist'=>$agentlist
                );
                $this->ajaxReturn($data,'暂无代理商数据!',1);
            }

        }else{

            $this->ajaxReturn('','请求数据异常!',0);
        }
    }

    /**生成随机码
     * @param $nums
     * @param $num
     * @return string
     */
    private  function generateCode($nums,$num){

        $strs="abcdefghjkmnpqrstuvwxyz";

        $str="123456789";

        $keys = "";

        for($t=0;$t<$nums;$t++){

            $keys .= $strs{mt_rand(0,18)};

        }

        $key = "";

        for($i=0;$i<31;$i++){

            $key .= $str{mt_rand(0,31)};

        }

        $time  = substr($this->getMillisecond(), 10,3);

        $key = substr($key,3,$num);

        $res = $keys.$key.$time;


        $info = D('Imsi')->where(array(['code'=>$res]))->find();

        if(!empty($info)){

            $this->generateCode($nums,$num);

        }else{

            return $res;

        }



    }

    /**生成毫秒级时间戳
     * @return float
     */
    private function getMillisecond() {

        list($t1, $t2) = explode(' ', microtime());

        return (float)sprintf('%.0f',(floatval($t1)+floatval($t2))*1000);

    }

    /**
     * 验证手机验证码
     */
    private function verifycat($mobile,$key,$code){

        if(!isMobile($mobile)){
            $this->ajaxReturn('','手机号码格式错误!',0);
        }
        $Cachecode=Cac()->get($key.$mobile);
        if($code==$Cachecode){
            $this->ajaxReturn('','验证成功!',1);
        }else{
            $this->ajaxReturn('','验证码错误!',0);
        }
    }
    /**
     * 发送验证码
     */
    private function csendcode($mobile,$key,$type){
        if(!isMobile($mobile)){
            $this->ajaxReturn('','手机号码格式错误!',0);
        }
        $code=rand_string(6,1);
        Cac()->set($key.$mobile,$code,300);

        //todo 发送短信
        $res=D("Sms")->dxbsend($mobile,$code);

        if($res=="0"){
            $this->storecode($code,$mobile,$type);
            $this->ajaxReturn('','发送成功!',1);
        }elseif($res=="123"){
            $this->ajaxReturn('faild','一分钟只能发送一条!',0);
        }else{
            $this->ajaxReturn('faild','失败！请联系管理员:'.$res,0);
        }
    }

    private function storecode($code,$mobile,$type){
        $data=array(
            'code'=>$code,
            'phone'=>$mobile,
            'type'=>$type,
            'creatime'=>time()
        );
        D('Verificat')->add($data);
    }
    /**生成唯一订单号
     * @return string
     */
    private function getrequestId(){
        list($s1, $s2)	=	explode(' ', microtime());
        list($ling, $haomiao)=	explode('.', $s1);
        $haomiao    =	substr($haomiao,0,3);
        $requestId  =	date("YmdHis",$s2).$haomiao; //商户订单号(out_trade_no).必填(建议是英文字母和数字,不能含有特殊字符)
        return $requestId;
    }

}