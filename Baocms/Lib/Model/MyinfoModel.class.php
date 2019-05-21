<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/5/17
 * Time: 9:02
 */
class MyinfoModel extends Model{

    /**获取下面所有人
     * @param array $result
     * @return array
     */
    public function gettolAgent(&$result =array(),$clear=false){
        static $idsinfo =array();
        if($clear)
        {
            $idsinfo = array();
        }
        $ids =array_column($result,'user_id');
        $ids =implode(',',$ids);
        $res =D('Users')->where("pid in (".$ids.")")->field('user_id,shenfen')->select();
        if($res){
            $ress =$this->gettolAgent($res);
            $idsinfo = array_merge($res,$ress);
        }

        return $idsinfo;
    }

}