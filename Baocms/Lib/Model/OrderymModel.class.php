<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/14
 * Time: 16:00
 */
class OrderymModel extends Model{
    /**更改二维码为未使用状态
     * @param int $erweima_id
     */
    public function saveuse_status($erweima_id=0){
        if($qrcodelist =D('Erweima_generic')->where(array('id'=>$erweima_id,'use_status'=>1))->find()){
            D('Erweima_generic')->where(array('id'=>$erweima_id,'use_status'=>1))->field('use_status')->save(array('use_status'=>0));
        }

    }

    public function chanum_push($erweima_id,$chanum){
        $numlist =Cac()->lRange('lkcode'.$erweima_id,0,-1);
        if(in_array($chanum,$numlist)){

        }else{
            Cac()->rPush('lkcode'.$erweima_id,$chanum);
        }
    }
}
