<?php


class UsersAction extends CommonAction
{



// 用户信息
    public function index(){
        $user_id=1;
        echo "11111111111";
      

        $userinfo=D("Users")->where(array("user_id"=>$user_id))->find();

        $erweima=D("erweima")->where(array("user_id"=>$user_id))->select();
        $data['userinfo']=$userinfo;
        $data['erweima']=$erweima;

        $this->ajaxReturn($data,'用户信息');
    }

    //  用户开始接单
    public function start(){
        $user_id=1;
        $usermoney=D("ccount_log")->where(array("user_id"=>$user_id))->field("sum(score)as score")->select();
        if ($usermoney['score']<=0){
            $this->ajaxReturn("","余额不足请充值!",0);
        }
        //判断在线状态
        if (D("status")->is_online($user_id)){
            $this->ajaxReturn("","网络错误!",0);

        }
        //用户入接单队列
        Cac()->rPush('jiedan',$user_id);
        D("Users")->where(array("user_id"=>$user_id))->save(array("status"=>1));
        $this->ajaxReturn("","开始成功!");

    }

    // 用户关闭接单
    public function over(){
        $user_id=1;
        //用户移除接单队列
        Cac()->lRem("jiedan",0,$user_id);
        D("Users")->where(array("user_id"=>$user_id))->save(array("status"=>0));
    }

    // 码商上码
    public function shangma(){

        $user_id=1;
        $edu=3000;

        // 查看用户积分
        $usermoney=D("account_log")->where(array("user_id"=>$user_id))->field("sum(score)as score")->select();

        //查看用户二维码积分
        $erweima=D("erweima")->where(array("user_id"=>$user_id))->field("sum(edu) as edu")->select();

        if ($usermoney[0]['money']-$erweima[0]['edu'] <$edu){
            $this->ajaxReturn("","额度没有这么多啦!",0);
        }

        if ( !file_exists("./erweima/" . $_FILES["logo"]["name"]))
        {
            $fileName=$_FILES['logo']['name'];//得到上传文件的名字
            $name=explode('.',$fileName);//将文件名以'.'分割得到后缀名,得到一个数组
            $date=date('Ymdhis');//得到当前时间,如;20070705163148
            $newPath=$date.'.'.$name[1];//得到一个新的文件为'20070705163148.jpg',即新的路径

            move_uploaded_file($_FILES["logo"]["tmp_name"], "./erweima/" .$newPath);
        }



        $data['erweima'] ="./erweima/" .$newPath;
        $data['edu']=$edu;
        $data['status']=0;
        $data['creatime']=time();
        $id=D("erweima")->add($data);

        // 二维码存入用户缓冲
        Cac()->rPush('erweima'.$user_id,$id);

    }

}