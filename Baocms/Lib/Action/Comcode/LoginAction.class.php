<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/29
 * Time: 15:47
 */

class LoginAction extends CommonAction{


    //todo 用户登录20180709     Lee_zhj
    public function login(){


        $account=(int)$_POST['account'];
        $password=$_POST['password'];

        //判断用户名密码
        if($account!=""&&$password!=""){
            $tb_userlogin = M('users');
            $where['account']=$account;
            $where['password']=md5($password);
           $userinfo=$tb_userlogin->where($where)->find();

            if($userinfo){

                if ($userinfo['frozen'] == 1){
                    $this->ajaxReturn($userinfo,'此账号已被封禁!',0);
                }

                $save['token']=md5(rand_string(6,1));
                $tb_userlogin->where(array("account"=>$account))->save($save);
                $userinfo['token']=$save['token'];


                $this->ajaxReturn($userinfo,"成功");

            }else{
                $this->ajaxReturn('','用户名或密码错误',0);
            }
        }else{
            $this->ajaxReturn($account,'用户名或密码为空',0);
        }

    }

      //  用户注册
    public function mobile(){
        $mobile=(int)$_POST['mobile'];
        $code=$_POST['code'];
        $password=$_POST['pwd'];
        $repassword=$_POST['repwd'];
//        if(empty(D("Users")->getUserByMobile($mobile))){
//            $this->jsonout('faild','账号不存在！');
//        }
        if(!isMobile($mobile)){
            $this->ajaxReturn('','手机号码格式错误',0);
        }
        if($password!=$repassword){
            $this->ajaxReturn('','密码不一致',0);

        }
        if(strlen($password)<6){
            $this->ajaxReturn('','密码不能小于6位',0);

        }

        //判断邀请码是否存在
       $codeinfo= D("imsi")->where(array("code"=>$code))->find();

        if (empty($codeinfo)){
            $this->ajaxReturn('','邀请码不存在',0);
        }

        if ($codeinfo['status'] == 1){
            $this->ajaxReturn('','邀请码已被占用'.$code,0);
        }

        //判断用户是否存在
        $userModel = D('Users');
        $userInfo=$userModel->where(array("account"=>$mobile))->find();

        if(!empty($userInfo)){
            $this->ajaxReturn('','账号已存在！',0);
        }else{
            //不存在 入库用户信息
            $user_id=$userModel->insertUserInfo($mobile,$codeinfo,$password);
            if(empty($user_id)){
                $this->ajaxReturn('','注册失败！',0);

            }else{
                D("imsi")->where(array("code"=>$code))->save(array("bind_id"=>$user_id,"status"=>1,"zytime"=>time()));
                $user_info=D("Users")->where(array("user_id"=>$user_id))->find();
                $this->ajaxReturn($user_info,'注册成功！');
            }

        }


    }


    //  业务用户注册
    public function mobiles(){
        $mobile=(int)$_POST['mobile'];  //手机号码
        $code=(int)$_POST['code'];   //验证码
        $name=$_POST['name'];   //  姓名
//        $idcard=$_POST['idcard'];   // 身份证号码
        $hb_id=$_POST['hb_id'];//
        $pid=(int)$_POST['pid'];



        if(!($pid>0)){
            $pid=0;
        }
        if(!isMobile($mobile)){
            $this->jsonout('faild','手机号码格式错误！');
        }

        $userModel = D('Users');
        $userInfo=$userModel->getUserByMobile($mobile);
        //print_r($userInfo);
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
        $Cachecode=$redis->get('login_code_'.$mobile);
        if($code!=$Cachecode){
            $this->jsonout('faild','验证码错误！');
        }

        //判断用户是否存在


        if(!empty($userInfo)){
            //   账号已存在 查询产品信息
           $newsinfo= D("news")->where(array("id"=>$hb_id))->find();
            if ($newsinfo['lb']==1){
                //信用卡
                $tb_credit = M('creditcard');
                $data['fromxyktongdao'] = $newsinfo['title'];
                $data['logo']=$newsinfo['logo'];
            }else{
                // 贷款
                $tb_credit = M('credit');
                $data['fromtongdao'] = $newsinfo['title'];
                $data['logo']=$newsinfo['logo'];

            }

            $data['user_id'] = $userInfo['user_id'];   //  用户id
            $data['username'] = $name;    //
//            $data['idcard'] = $idcard;
            $data['phonenum'] = $mobile;
            $data['pid']=$pid;
            $data['ip']=$_SERVER['REMOTE_ADDR'];
            $data['userdate'] = time();
            //数据存入数据库
            $status = $tb_credit->add($data);
            if ($status){
                    $list['url']=$newsinfo['url'];
                $this->jsonout("success",'成功！',$list);
            }else{

                $this->jsonout("faild",'失败1,请重新申请！'.$newsinfo['title'],$data);
            }

        }else{
            //不存在 入库用户信息
            $userInfo=$userModel->insertUserInfos($mobile,$pid);
            if(empty($userInfo)){
                $this->jsonout('faild','注册失败！');
            }else{
                $newsinfo= D("news")->where(array("id"=>$hb_id))->find();
                if ($newsinfo['lb']==1){
                    //信用卡
                    $tb_credit = M('creditcard');
                    $data['fromxyktongdao'] = $newsinfo['title'];
                   // $data['logo']=$newsinfo['logo'];
                }else{
                   // 贷款
                    $tb_credit = M('credit');
                    $data['fromtongdao'] = $newsinfo['title'];
                    //$data['logo']=$newsinfo['logo'];

                }
                $data['user_id'] = $userInfo;   //  用户id
                $data['username'] = $name;    //
                $data['pid']=$pid;
                $data['ip']=$_SERVER['REMOTE_ADDR'];
                $data['phonenum'] = $mobile;
                $data['fromtongdao'] = $newsinfo['title'];
                $data['userdate'] = time();
                //数据存入数据库
                $status = $tb_credit->add($data);
                if ($status){
                    $list['url']=$newsinfo['url'];
                    $this->jsonout("success",'成功！',$list);
                }else{
                    $this->jsonout("faild",'失败2,请重新申请！',$data);
                }
            }
        }
    }





    //  手机登陆
    public function mobilelogin(){
        $mobile=(int)$_POST['mobile'];
        $code=(int)$_POST['code'];


        if(!isMobile($mobile)){
            $this->ajaxReturn('','手机号码格式错误！',0);

        }



        $Cachecode=Cac()->get('login_code_'.$mobile);
        if($code!=$Cachecode){
            $this->ajaxReturn('','验证码错误！',0);
        }


        $userModel = D('Users');
        $userInfo=$userModel->where(array("account"=>$mobile))->find();

        if(!empty($userInfo)){
            $tb_userlogin=D("Users");
            $save['token']=md5(rand_string(6,1));
            $tb_userlogin->where(array("account"=>$mobile))->save($save);
            $userinfo['token']=$save['token'];

            $this->ajaxReturn($userInfo,'登陆成功！');
        }else{
            $this->ajaxReturn("",'账号不存在！',0);
        }
    }



        //发送验证码
    public function sendcode(){
        $mobile=(int)$_POST['mobile'];

        if(!isMobile($mobile)){
            $this->ajaxReturn('','手机号码格式错误！',0);
        }


        $code=rand_string(6,1);
        Cac()->set('login_code_'.$mobile,$code,300);

        //todo 发送短信
        //Sms:LoginCodeSend($mobile,$code);
        $res=D("Sms")->dxbsend($mobile,$code);

        if($res=="0"){
            $this->ajaxReturn('success','短信发送成功！',1);
        }elseif($res=="123"){
            $this->ajaxReturn('faild','一分钟只能发送一条',0);
        }else{
            $this->ajaxReturn('faild','失败！请联系管理员:'.$res,0);
        }
    }






    public function sendmobile(){
        $mobile=(int)$_POST['mobile'];
        if(!isMobile($mobile)){
            $this->jsonout('','手机号码格式错误！');
        }
//        $yzm = $this->_post('yzm');
//        if(strtolower($yzm) != strtolower(session('verify'))){
//            session('verify',null);
//            $this->jsonout('','图形验证码错误！');
//        }
        $code=rand_string(6,1);
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
        $redis->set('login_code_'.$mobile,$code,300);
        //todo 发送短信
        //Sms:LoginCodeSend($mobile,$code);
        $res=D("Sms")->dxbsend($mobile,$code);
        if($res=="0"){
            $this->jsonout('success','短信发送成功！',1);
        }elseif($res=="123"){
            $this->jsonout('faild','一分钟只能发送一条',0);
        }else{
            $this->jsonout('faild','失败！请联系管理员:'.$res,0);
        }
    }

    //产生一个指定长度的随机字符串,并返回给用户
    private function genRandomString($len = 6) {
        $chars = array(
            "a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",
            "l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",
            "w", "x", "y", "z", "A", "B", "C", "D", "E", "F", "G",
            "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R",
            "S", "T", "U", "V", "W", "X", "Y", "Z", "0", "1", "2",
            "3", "4", "5", "6", "7", "8", "9"
        );
        $charsLen = count($chars) - 1;
        // 将数组打乱
        shuffle($chars);
        $output = "";
        for ($i = 0; $i < $len; $i++) {
            $output .= $chars[mt_rand(0, $charsLen)];
        }
        return $output;
    }

    public function getcodeview(){
        $mobile=(int)$_GET['mobile'];
        if(!isMobile($mobile)){
            $this->ajaxReturn('','手机号码格式错误！',0);
        }
        $redis = new \Redis();
        $redis->connect('127.0.0.1',6379);
        $code=$redis->get('login_code_'.$mobile);
        echo $code;
    }

}