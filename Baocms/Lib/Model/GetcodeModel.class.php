<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/6/22
 * Time: 17:01
 */
class GetcodeModel extends Model{
    /**获取数量
     * @param $code_id
     * @return string
     */
    public function getcode($code_id){
        $chanum =Cac()->lPop('lkcode'.$code_id);
        return (int)$chanum;
    }

    /**存储缓存
     * @param $code_id
     * @return array
     */
    public function pushcode($code_id){
        $numarr =array(0,1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,-1,-2,-3,-4,-5,-6,-7,-8,-9);
        Cac()->delete('lkcode'.$code_id);
        shuffle($numarr);
        for ($i=0;$i<count($numarr);$i++){
            Cac()->rPush('lkcode'.$code_id,$numarr[$i]);
        }
        $chanum =Cac()->lRange('lkcode'.$code_id,0,-1);
        return $chanum;
    }

}