<?php
const APP_ID = '16237391';
const API_KEY = '1MVA29XY5cBTfxPWEGbRY4kD';
const SECRET_KEY = 'X48Lz9NbLSyMY6r4AbZaQmwOCLfGOsxt';

class  GenericcodeAction extends CommonAction{



    // 码商上码
    public function shangma(){

        $user_id=$this->uid;
        $type=$_POST['type']; //类型
        $username=$_POST['name']; //姓名
        $home=$_POST['home'];   //地址
        $min=$_POST['min'];   // 最小值
        $max=$_POST['max'];   //最大值

//                                $myfile = fopen("shangma.txt", "a+") ;
//                fwrite($myfile,var_export($_POST,true) );
//                fclose($myfile);

        // 查看用户费率
        $userinfo=D("Users")->where(array("user_id"=>$user_id))->find();
        if ( ! $userinfo["rate"]>0){
            $this->ajaxReturn("","请联系上级设置费率".$this->uid,0);
        }




        foreach ($_FILES["uploadfile"]["name"] as $k=>$v){
            if ( !file_exists("./erweima/" . $_FILES["uploadfile"]["name"][$k]))
            {



                 $edu=D("erweima_generic")->where(array("user_id"=>$user_id,"status"=>0,"min"=>$min,"max"=>$max,"type"=>$type))->find();
                if ($edu){
                    $this->ajaxReturn("","此金额二维码已有,请更换图片",0);
                }



                $fileName=$_FILES['uploadfile']['name'][$k];//得到上传文件的名字
                $name=explode('.',$fileName);//将文件名以'.'分割得到后缀名,得到一个数组
                $date=date('Ymdhis');//得到当前时间,如;20070705163148
                $newPath=$date.'.'.$name[1];//得到一个新的文件为'20070705163148.jpg',即新的路径

                move_uploaded_file($_FILES["uploadfile"]["tmp_name"][$k], "./erweima/" .$newPath);
            }


            $data['user_id']=$user_id;
            $data['erweima'] ="./erweima/" .$newPath;
            $data['status']=0;
            $data['type']=$type;
            $data['name']=$username;
            $data['home']=$home;
            $data['max']=$max;
            $data['min']=$min;
            $data['creatime']=time();
            $id=D("erweima_generic")->add($data);
            if($id){
                D('Getcode')->pushcode($id);
                $this->ajaxReturn("","成功");
            }


        }

    }





    //  账号激活

    public function activate(){

        $user_id=$this->uid;
       // $user_id=10007;

        // 查看用户积分
        $tolscore = D('Account')->gettolscore($user_id);
        if ($tolscore<30000){
            $this->ajaxReturn($tolscore,"账户余额不足!",0);
        }

        $userinfo=D("Users")->where(array("user_id"=>$user_id))->find();
        if ($userinfo['jh_status'] == 1){
            $this->ajaxReturn($userinfo,"账号已激活!",0);
        }

        // 积分扣除
        $account=D("account_log");
        $data['user_id']=$user_id;
        $data['score']=-30000;
        $data['status']=7;
        $data['remark']="账户激活";
        $data['creatime']=time();
        $id=$account->add($data);


        // 上级得佣金
        if ($userinfo['pid']>0){

            //佣金   1级
            $account=D("account_log");
            $data['user_id']=$userinfo['pid'];
            $data['score']=15000;
            $data['status']=5;
            $data['remark']="激活佣金";
            $data['creatime']=time();
            $account->add($data);


            $pidinfo=D("Users")->where(array("user_id"=>$userinfo['pid']))->find();

            if ($pidinfo['pid']>0){

                //佣金   2级
                $account=D("account_log");
                $info['user_id']=$pidinfo['pid'];
                $info['score']=5000;
                $info['status']=5;
                $info['remark']="激活佣金";
                $info['creatime']=time();
                $account->add($info);
            }

        }

        if ($id){
            //更改账户状态
            D("Users")->where(array("user_id"=>$user_id))->save(array("jh_status"=>1));
            $this->ajaxReturn($id,"激活成功!");
        }


    }









}