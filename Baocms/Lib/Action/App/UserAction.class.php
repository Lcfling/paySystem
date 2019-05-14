<?php
const APP_ID = '16237391';
const API_KEY = '1MVA29XY5cBTfxPWEGbRY4kD';
const SECRET_KEY = 'X48Lz9NbLSyMY6r4AbZaQmwOCLfGOsxt';

class  UserAction extends CommonAction{



  // 用户信息
   public function index(){
       $user_id=1;

     $userinfo=D("Users")->where(array("user_id"=>$user_id))->find();

      $erweima=D("erweima")->where(array("user_id"=>$user_id))->select();
      $data['userinfo']=$userinfo;
      $data['erweima']=$erweima;

       $this->ajaxReturn($data,'用户信息');
   }
    
    //  用户开始接单
    public function start(){
        $user_id=$_POST['user_id'];
      //  $usermoney=D("ccount_log")->where(array("user_id"=>$user_id))->field("sum(score)as score")->select();
//        if ($usermoney['score']<=0){
//            $this->ajaxReturn("","余额不足请充值!",0);
//        }
//        //判断在线状态
//        if (D("status")->is_online($user_id)){
//            $this->ajaxReturn("","网络错误!",0);
//
//        }
                $myfile = fopen("start_post.txt", "a+") ;
        fwrite($myfile, var_export($_POST,true));
        fclose($myfile);

        $erweima=D("erweima")->where(array("user_id"=>$user_id))->select();
        if (empty($erweima)){
            $this->ajaxReturn("","请先上传收款码!",0);
        }

        $time=Cac()->get("jiedan_status".$user_id);
        if ($time+3<time()){
            $this->ajaxReturn("","开启失败!",1);
        }

        //用户入接单队列
        Cac()->rPush('jiedans',$user_id);
        D("User")->where(array("user_id"=>$user_id))->save(array("status"=>1));
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

        $user_id=$_POST['user_id'];



        $myfile = fopen("post.txt", "a+") ;
        fwrite($myfile, var_export($_POST,true));
        fclose($myfile);






        // 查看用户积分
        $usermoney=D("account_log")->where(array("user_id"=>$user_id))->field("sum(score)as score")->select();

        //查看用户二维码积分
        //$erweima=D("erweima")->where(array("user_id"=>$user_id))->field("sum(edu) as edu")->select();

//        if ($usermoney[0]['money']-$erweima[0]['edu'] <$edu){
//            $this->ajaxReturn("","额度没有这么多啦!",0);
//        }


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
                    $money=ceil($money);
                }else{
                    $money=ceil($money/100);
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
            $data['creatime']=time();
            $id=D("erweima")->add($data);
            if($id){
                // 二维码存入用户缓冲
                Cac()->rPush('erweimas'.$money.$user_id,$id);
                $this->ajaxReturn("","成功");
            }
        }

    }

    public function erweima(){

        $data=D("Users")->getcode(100);
      //  $data=$this->getcode(100);
        print_r($data);
    }


    // 获取二维码
    public function getcode($money){

        $user_id=Cac()->LPOP("jiedans");
        if ( !$user_id>0){
            $this->ajaxReturn(""," 接单队列没有人!",1);
        }

        $time=Cac()->get("jiedan_status".$user_id);
        if ($time+3<time()){
            $this->getcode($money);
        }

        $erweima_id=Cac()->LPOP("erweimas".$money.$user_id);
        $data=D("erweima")->where(array("id"=>$erweima_id))->find();

        return $data;

    }


    public function is_status(){

      $user_id=$_POST['user_id'];

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
        require_once './AipOcr.php';
        $client = new AipOcr(APP_ID, API_KEY, SECRET_KEY);

        $image = file_get_contents('./erweima/20190514092008.jpg');
        $data=$client->basicGeneral($image);
        print_r($data);

        $images = file_get_contents('./erweima/20190513042031.jpg');
        $datas=$client->basicGeneral($images);
        print_r($datas);


       // echo strpos($data['words_result'][1]['words'],'￥');


     $money= trim(strrchr($data['words_result'][1]['words'], '￥'),'￥');


        if (strstr($money,".")){
            echo ceil($money)."出现点点点";
        }else{
            echo ceil($money)."没有出现点点点";
        }


     //  echo substr($data['words_result'][1]['words'],3);

      // echo substr($data['words_result'][1]['words'],strpos($data['words_result'][1]['words'],'￥'));

//        foreach ($data['words_result'] as $k=>$v){
//
//           echo $v['words'];
//        }

      //  echo $data['words_result'][1]['words'];


    }




}