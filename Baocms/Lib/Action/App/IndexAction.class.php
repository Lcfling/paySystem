<?php


class IndexAction extends CommonAction
{
	public function index()
	{
	    //$gameInfo=D('Room')->
        $data=array(
            0=>array(
                'title'=>'扫雷',
                'game'=>'saolei',
                'img'=>'img/game1.png'
            ),
            1=>array(
                'title'=>'接龙',
                'game'=>'jielong',
                'img'=>'img/game2.png'
            ),
            2=>array(
                'title'=>'大小单双',
                'game'=>'danshuang',
                'img'=>'img/game3.png'
            )
        );
        $this->ajaxReturn($data,'success',1);

	}

	public function test1(){
        Gateway::$registerAddress = '127.0.0.1:1238';

	    $data=array(
	        'roomid'=>3735273,
            'm'=>1,
            'data'=>array(
                'username'=>'美女',
                'user_id'=>'1675547',
                'avatar'=>'http://www.baidu.com',
                'hongbao_id'=>'38',
                'money'=>'500',
                'bom_num'=>5
            )
        );
	    $data=json_encode($data);
        Gateway::sendToAll($data);
        echo '发送完毕';


    }
    public function test4(){
        Gateway::$registerAddress = '127.0.0.1:1238';

        $data=array(
            'roomid'=>3735273,
            'm'=>1,
            'data'=>array(
                'username'=>'别人的',
                'user_id'=>'1675590',
                'avatar'=>'http://www.baidu.com',
                'hongbao_id'=>'38',
                'money'=>'2000',
                'bom_num'=>9
            )
        );
        $data=json_encode($data);
        Gateway::sendToAll($data);
        echo '发送完毕';
    }
    public function test2(){
        Gateway::$registerAddress = '127.0.0.1:1238';

        $data=array(
            'roomid'=>3735273,
            'm'=>2,
            'data'=>array(
                'username'=>'美女',
                'user_id'=>'1675547',
                'aword'=>'50',
                'hongbao_id'=>'38',
                'money'=>'500',
                'bom_num'=>7,
                'type'=>1

            )
        );
        $data=json_encode($data);
        Gateway::sendToAll($data);
        echo '发送完毕';

    }
    public function test3(){
        Gateway::$registerAddress = '127.0.0.1:1238';

        $data=array(
            'roomid'=>3735273,
            'm'=>3,
            'data'=>array(
                'username'=>'美女',
                'user_id'=>'1675547',
                'hongbao_id'=>'38',
                'money'=>'600',
                'bom_num'=>3
            )
        );
        $data=json_encode($data);
        Gateway::sendToAll($data);
        echo '发送完毕';
    }
    public function redis(){

        $s=Cac()->lRange('kickback_queue_1',0,-1);
        print_r($s);
    }
    public function test22(){
	    $this->uid=1675552;
        echo D('Users')->getUserMoney($this->uid);
    }

    public function test33(){

        $con=new FanyongModel();
        $con->fanyong("1675552","100","saolei");
        var_dump($con);
    }

    public function jiedong(){

        $hongbao_id='860';
        $money=1000;
        $where['hb_id']=$hongbao_id;
        $where['user_id']=array('NEQ','0');
        $kickInfo=D('Kickback_jielong')->where($where)->select();

        $jl=D('Jielong');
        foreach ($kickInfo as $k=>$v){
            $jl->unfrozen($kickInfo[$k]['user_id'],$money);
        }
    }

    public function Gameover(){

        $room=D('Room');
        $where['game']="jielong";
        $room_id=$room->where($where)->find();


            $hongbaoModel=D('Jielong');
            $hongbao=D('Hongbao_jielong');
            $where1['roomid']=$room_id['room_id'];
            $where1['is_over']=array('EQ',0);

            $list=$hongbao->where($where1)->order('id desc')->limit(1)->find();



            if(empty($list)){
                $hongbaoModel->delete_start($list['roomid']);
                //  $this->sendnotify($list);
                $this->ajaxReturn('','游戏结束!',1);
            }

            $hongbao_id=(int)$list['id'];

            if (!$hongbaoModel->isfinish($hongbao_id)){

                $timediff =time()-$list['creatime'];
                //计算分钟数
                $mins = intval($timediff/60);

                if ($mins >3){

                    $hb_where['id']=$list['id'];
                    $hb_where['roomid']=$list['roomid'];
                    $hb_save['is_over']=1;
                    $hb_save['overtime']=time();
                    $hongbao->where($hb_where)->save($hb_save);



                    if ($list['is_start'] !='1'){
                        $where['hb_id']=$hongbao_id;
                        $where['is_robot']=array('NEQ',1);
                        $where['user_id']=array('EQ','0');
                        $kickInfo=D('Kickback_jielong')->where($where)->select();
                        //退回红包剩余金额
                        foreach ($kickInfo as $k=>$v){
                            D('Users')->addmoney($list['user_id'],$kickInfo[$k]['money'],2,1,'接龙红包退回');
                        }
                    }
                    //解冻领取人的金额
                    $hongbaoModel->jiedong($hongbao_id,1000);

                    $hongbaoModel->delete_start($list['roomid']);
                    //  $this->sendnotify($list);
                    $this->ajaxReturn('','游戏结束!',1);
                }else{
                    $this->ajaxReturn('','游戏继续!',1);
                }

            }

    }

    public function pid_team(){
        $uid=$_POST['pid'];
        $counts=$_POST['counts'];
        $uid='1675554';
        $user=D('Users');
        $where['pid']=$uid;
        $data=$user->where($where)->select();

        foreach ($data as $k=>$v){
            $where1['pid']=$data[$k]['user_id'];
            $count=$user->where($where1)->count();
            $data[$k]['count']=$count;

        }

        $data['counts']=$counts++;
   print_r($data);

    }
    public function shouyi(){
        $user_id='1675553';
        $user=D('Users');
        $data=$user->sum_money($user_id);
        print_r($data);
    }

    public function sum_money(){
        $user_id='1675554';
        $fanyong=M('Fanyong');
        $where['fenyong_id']=$user_id;
        //收益总金额
        $sum_money=$fanyong->where($where)->field('sum(fenyong_edu) as sum_money')->select();


        $begintime=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y')));

      echo"kaishi=".$catime = strtotime($begintime); //开始时间搓
        $endtime=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1);
     echo "jieshu=". $catime1 = strtotime($endtime); //结束时间搓

        //今日总收益
      //  $str=date("Y-m-d",time());//当前日期
        $where1['fyDate']=array('EGT',$catime);
       // $where1['fyDate']=array('ELT',$catime1);
        $where1['fenyong_id']=$user_id;
        $money=  $fanyong->where($where1)->field('sum(fenyong_edu) as money')->select();

        //收益明细
        $list=$fanyong->where($where)->select();


        import('ORG.Util.Page');

        $count = $fanyong->where($where)->count();
        $Page = new Page($count, 25);
        $show = $Page->show(); // 分页显示输出

        $users=D('Users');
        foreach ($list as &$v){
            $user=$users->getUserByUid($v['fabao_id']);
            $v['nickname']=$user['nickname'];
        }

        $data['sum_money']=$sum_money[0]['sum_money'];  // 收益总金额
        $data['money']=$money[0]['money']; //今日收益总金额
        $data['list']=$list;  //收益明细
        $data['show']=$show; //   分页显示

        print_r($data);
    }
    public function versionios(){
        $v=$_POST['currentVersion'];
        if($v!="1.1.0"){
            $data="https://www.darkhorse.vip/xiazai/download.html";
            $this->ajaxReturn($data,$_POST['currentVersion'],"success");
        }else{
            $data="https://www.darkhorse.vip/xiazai/download.html";
            $this->ajaxReturn($data,$_POST['currentVersion'],"faild");
        }
    }
    public function reg(){
        $pid=$_GET['pid'];
        $url="https://www.dhwangluo.info/xiazai/registerAPP.html?pid=".$pid;

        header("Location:".$url);
    }
    public function money(){
        $money="100";
        $user_money="10000";
        if (($money+$money*0.01)*100>$user_money){
            $data['msg']='提现金额大于剩余金额';
            $data['status']=0;

        }else{
            $data['msh']='111';
        }
       print_r($data);
    }

    public function clearLock(){
	    $ids=D('Users')->where("1")->field('user_id')->select();
	    foreach($ids as $v){
	        Cac()->delete('txLock'.$v['user_id']);
        }
    }
    public function clearroom(){
	    Cac()->delete('roomlist_jielong');
    }
    public function notice(){
        $cate= D('Article');
        $where['cate_id']=3;
        $data=$cate->where($where)->order(array('article_id'=>'desc'))->limit(1)->select();
        $this->ajaxReturn($data,'公告');
    }
    public function wx(){
        $user_id=6667101;
        Cac()->delete('userinfo_'.$user_id,null);

    }
    public function setwx(){
        $user=D('Users');
        $user_id=6667101;
        $data=$user->getUserByUid($user_id);
        print_r($data);
    }
    public function clearby(){
        $id=$_GET['num'];
        $user=D('Users')->getUserByMobile($id);
            Cac()->delete('txLock'.$id);
    }
    public function mobilehide(){
        $tel = '12345678910';
//1.字符串截取法
        $new_tel1 = substr($tel, 0, 3).'****'.substr($tel, 7);

    }
    public function testmy(){
	    $m=D("Room");

        $data=$m->where(array('game'=>'saolei','is_show'=>1))->select();
        print_r($data);
	    $m->clearCache();
    }
}