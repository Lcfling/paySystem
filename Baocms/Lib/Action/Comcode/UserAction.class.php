<?php
const APP_ID = '16237391';
const API_KEY = '1MVA29XY5cBTfxPWEGbRY4kD';
const SECRET_KEY = 'X48Lz9NbLSyMY6r4AbZaQmwOCLfGOsxt';

class  UserAction extends CommonAction{



  // 用户信息
   public function index(){



     //  Cac()->rPush('ceshi',1);

       $list =Cac()->lRange('jiedan',0,-1);
       print_r($list);
   }
    
    //  用户开始接单
    public function start(){
        $user_id=$this->uid;


        $Userinfo=D("Users")->where(array("user_id"=>$user_id))->find();

        if ($Userinfo['jh_status'] == 0){

            $this->ajaxReturn("","账号未激活!",0);
        }


        if ($Userinfo["take_status"]==1){

            //用户移除接单队列
            Cac()->lRem("jiedans",$user_id,0);
            D("Users")->where(array("user_id"=>$user_id))->save(array("take_status"=>0));
            $this->ajaxReturn("","关闭接单!");
        }


        // 查看用户积分
        $tolscore = D('Account')->gettolscore($user_id);
        if ($tolscore <=100000){
            $this->ajaxReturn($tolscore,"额度没有这么多啦!",0);
        }

        $erweima=D("erweima_generic")->where(array("user_id"=>$user_id))->select();
        if (empty($erweima)){
            $this->ajaxReturn("","请先上传收款码!",0);
        }

//        $time=Cac()->get("jiedan_status".$user_id);
//        if ($time+3<time()){
//            $this->ajaxReturn("","开启失败!",1);
//        }

        //用户入接单队列
        Cac()->rPush('jiedans',$user_id);
        D("Users")->where(array("user_id"=>$user_id))->save(array("take_status"=>1));

        $this->ajaxReturn("","开始成功!");

    }



    // 码商上码
    public function shangma(){

        $user_id=$this->uid;
        $type=$_POST['type'];
        $username=$_POST['name'];
        $home=$_POST['home'];


        $myfile = fopen("shangma_post.txt", "a+") ;
        fwrite($myfile, var_export($_POST,true));
        fclose($myfile);




        // 查看用户费率
        $userinfo=D("Users")->where(array("user_id"=>$user_id))->find();
        if ( ! $userinfo["rate"]>0){
            $this->ajaxReturn("","请联系上级设置费率!",0);
        }




        foreach ($_FILES["uploadfile"]["name"] as $k=>$v){
            if ( !file_exists("./erweima/" . $_FILES["uploadfile"]["name"][$k]))
            {

                require_once './AipOcr.php';
                $client = new AipOcr(APP_ID, API_KEY, SECRET_KEY);

                $image = file_get_contents($_FILES["uploadfile"]["tmp_name"][$k]);
                $data=$client->basicGeneral($image);
//                                $myfile = fopen("AipOcr.txt", "a+") ;
//                fwrite($myfile,var_export($data,true) );
//                fclose($myfile);


                $money= trim(strrchr($data['words_result'][1]['words'], '￥'),'￥');

                if (strstr($money,".")){
                    $moneys=ceil($money);
                }else{
                    $moneys=ceil($money/100);
                    $money=$money/100;
                }

                 $edu=D("erweima")->where(array("user_id"=>$user_id,"edu"=>$money*100,"status"=>0))->find();
                if ($edu){
                    $this->ajaxReturn("","此金额二维码已有,请更换图片",0);
                }

                if ($money>=5000 || $money<=99){
                    $this->ajaxReturn("","此金额数额不对,请核实！",0);
                }


//                $myfile = fopen("FILES_s.txt", "a+") ;
//                fwrite($myfile,$_FILES["uploadfile"]["name"][$k] );
//                fclose($myfile);

                $fileName=$_FILES['uploadfile']['name'][$k];//得到上传文件的名字
                $name=explode('.',$fileName);//将文件名以'.'分割得到后缀名,得到一个数组
                $date=date('Ymdhis');//得到当前时间,如;20070705163148
                $newPath=$date.'.'.$name[1];//得到一个新的文件为'20070705163148.jpg',即新的路径

                move_uploaded_file($_FILES["uploadfile"]["tmp_name"][$k], "./erweima/" .$newPath);
            }


            $data['user_id']=$user_id;
            $data['erweima'] ="./erweima/" .$newPath;
            $data['status']=0;
            $data['list']=$moneys;
            $data['edu']=$money*100;
            $data['type']=$type;
            $data['name']=$username;
            $data['home']=$home;
            $data['creatime']=time();
            $id=D("erweima")->add($data);
            if($id){
                // 二维码存入用户缓冲
                Cac()->rPush('erweimas'.$moneys.$user_id,$id);
                $this->ajaxReturn("","成功");
            }
        }

    }


    //分配额度
    public function  addscore(){
        $user_id=$this->uid;
       $score=(int)$_POST['score'];
       $erweima_id=(int)$_POST['erweima_id'];

       $erweima=D("erweima_generic");
       // 二维码添加积分
       $erweima->where(array("user_id"=>$user_id,"id"=>$erweima_id))->setInc("limits",$score);
       //增加积分记录
        $erweima_log=D("erweima_log");
        $data['user_id']=$user_id;
        $data['erweima_id']=$erweima_id;
        $data['score']=$score*100;
        $data['creatime']=time();
        $id=$erweima_log->add($data);
        if ($id){
            $this->ajaxReturn($id,"分配成功!");
        }else{
            $this->ajaxReturn($id,"分配失败!",0);
        }
    }


    // 二维码信息
    public function erweima_info(){

        $user_id=$this->uid;
        $erweima_id=(int)$_POST['erweima_id'];




        // 分配记录
       $erweima_log=D("erweima_log")->where(array("user_id"=>$user_id,"erweima_id"=>$erweima_id))->order(array('id'=>'desc'))->select();
       foreach ($erweima_log as &$v){
           $v['score']=$v['score']/100;
           $v['creatime']=date("Y-m-d : H:i:s",$v['creatime']);
       }

       //跑分记录
        $account_log=D("account_log")->where(array("user_id"=>$user_id,"erweima_id"=>$erweima_id,"status"=>2))->order(array('id'=>'desc'))->select();

        foreach ($account_log as &$v){
            $v['score']=$v['score']/100;
            $v['creatime']=date("Y-m-d : H:i:s",$v['creatime']);
        }

        //当前剩余
       $erweima=D("erweima_generic")->where(array("user_id"=>$user_id,"id"=>$erweima_id))->find();

       //已跑总额
        $sumscore=D("account_log")->where(array("user_id"=>$user_id,"erweima_id"=>$erweima_id,"status"=>2))->field("sum(score) as score")->select();


        $data['fenpei']=$erweima_log;
        $data['paofen']=$account_log;
        $data['shengyu']=$erweima['limit'];
        $data['yipao']=$sumscore[0]['score']/100;

        $this->ajaxReturn($data,"二维码信息!");

    }

    //二维码金额列表
    public function erweima_list(){

        $data= D("erweima_list")->where(array("is_show"=>1))->order(array('min'=>'asc'))->select();
        if (!empty($data)){
            $this->ajaxReturn($data,"二维码金额列表!");
        }
    }




    // 获取二维码
    public function getcode($money){

        $user_id=Cac()->LPOP("jiedans");
        if ( !$user_id>0){
            $this->ajaxReturn(""," 接单队列没有人!",1);
        }

        $time=Cac()->get("jiedan_status".$user_id);
        if ($time+6<time()){
            $this->getcode($money);
        }

        $erweima_id=Cac()->LPOP("erweimas".$money.$user_id);
        $data=D("erweima")->where(array("id"=>$erweima_id))->find();

        return $data;

    }


    public function is_status(){

      $user_id=$this->uid;

//                $myfile = fopen("is_status.txt", "a+") ;
//        fwrite($myfile, $user_id);
//        fclose($myfile);

      //存入用户最后在线时间
      Cac()->set("jiedan_status".$user_id,time());
    }

    public function get_status(){

        include_once('./qrReader/lib/QrReader.php');
        $qrcode = new QrReader('./20190511084729.jpg');  //图片路径
        $text = $qrcode->text(); //返回识别后的文本
        echo $text;

    }

    public function aip(){


//        $user_ids=10081;
//        $time=Cac()->get("jiedan_status".$user_ids);
//
//        echo $time;



//        Cac()->flushAll();
//        die();

//        Cac()->LPOP("jiedans");
//
        $list =Cac()->lRange('jiedans',0,-1);
        print_r($list);


//      $data=D("Users")->getGeneric_code(100,1,1);
//            print_r($data);
        die();












    }




}