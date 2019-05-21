<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/18
 * Time: 16:31
 */
class ImsiModel extends Model{

    public function getimsinum($user_id){

       $imsinum = D('Imsi')->where(array('user_id'=>$user_id))->count();

        return $imsinum;
    }


}