<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/16
 * Time: 14:52
 */
class AccountModel extends Model{

    public function gettolscore($user_id){
        $score =D('Account_log')->where(array('user_id'=>$user_id))->sum('score');
        return (int)$score;
    }

    public function gettolbrokerage($user_id){
        $score =D('Account_log')->where(array('user_id'=>$user_id,'status'=>5))->sum('score');
        return (int)$score;
    }

    public function getdaybrokerage($user_id,$status){
        $bg_time = strtotime(TODAY);
        $score =  D('Account_log')->where(array('user_id' => $user_id, 'status' =>$status , 'creatime' => array(array('ELT', NOW_TIME), array('EGT', $bg_time))))->sum('score');
        return (int)$score;
    }

}