<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/18
 * Time: 16:31
 */
class ImsiModel extends Model{

    public function getimsinum($user_id){
        $result = D('Users')->where(array('pid'=>$user_id))->field('user_id')->select();
        $ids =array_column($result,'user_id');
        $ids =implode(',',$ids);
        $imsinum = D('Imsi')->where("pid in (".$ids.")")->count();

        return (int)$imsinum;
    }

    public function getprinum($user_id){
        $imsinum = D('Imsi')->where(array('user_id'=>$user_id))->count();
        return (int)$imsinum;
    }


}