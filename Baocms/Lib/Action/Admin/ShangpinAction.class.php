<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018-12-24
 * Time: 9:44
 */
class ShangpinAction extends CommonAction{

    public function index() {
        $User = D('Goods');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed'=>array('IN','0,-1'));

        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $jiuyirobotrule=M("jiuyirobotrule");

        foreach($list as $k=>$val){
            $list[$k]['strike_price'] = $list[$k]['strike_price']/100;
            $list[$k]['auction_price']=$list[$k]['auction_price']/100;
            $list[$k]['buyback_price']=$list[$k]['buyback_price']/100;
            $list[$k]['buyback_price_no']=$list[$k]['buyback_price_no']/100;
            $where['goods_id']=$list[$k]['id'];
            $jiqiren=$jiuyirobotrule->where($where)->find();
            if ($jiqiren['open'] == 1){
                $open="开";
            }else{
                $open="关";
            }

            $list[$k]['min']=$jiqiren['min'];
            $list[$k]['max']=$jiqiren['max'];
            $list[$k]['open']=$open;
        }
        // print_r($list);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('ranks',D('Userrank')->fetchAll());
        $this->display(); // 输出模板

    }



    public function  shangjia(){


        $id=$this->_get('id');

        if ( $this->isPost()){
            $shangjia_status=$this->_post('shangjia');//快递单号

            $Goods= M("Goods");

            if ($shangjia_status == 1){
                $where['id']=$id;
                $data['sold_out']=$shangjia_status;
                $Goods->where($where)->save($data);

                $Goods_data=$Goods->where($where)->find();

                Cac()->set('jiuyi_auction_'.$id,serialize($Goods_data));
                $this->baoSuccess('操作成功',U('shangpin/index'));
                //更新缓存.
            }else if ($shangjia_status == 0){

                $where['id']=$id;
                $data['sold_out']=$shangjia_status;
                $Goods->where($where)->save($data);
                $this->baoSuccess('操作成功',U('shangpin/index'));
            }else{
                $this->baoError('操作失败');
            }

        }else{
            $this->assign('id', $id); // 赋值数据集
            $this->display();
        }
    }


    public function jiqiren(){


        $id=$this->_get('id');
        if ($this->isPost()){
            $min=$this->_post('min');//剩余参拍个数最小
            $max=$this->_post('max');//剩余参拍个数最大
            $open=$this->_post('open'); //机器人开关  0 关 1开1

            if($min<=1){
                $this->baoError('最小参拍不能小于1!');
            }

            if ($open >1 ){
                $this->baoError('机器人开关有误!');
            }
            if ($open<0){
                $this->baoError('机器人开关有误!');

            }

            $jiuyirobotrule=M("jiuyirobotrule");

            $where['goods_id']=$id;
            $jiuyirobotrule_data=$jiuyirobotrule->where($where)->find();
            if (!$jiuyirobotrule_data){
                $data['goods_id']=$id;
                $data['min']=$min;
                $data['max']=$max;
                $data['open']=$open;
                $data['creatime']=time();
                $status=$jiuyirobotrule->add($data);
                if ($status){
                    $this->baoSuccess('操作成功',U('shangpin/index'));
                }else{
                    $this->baoError('操作失败');
                }
            }else{
                $data['goods_id']=$id;
                $data['min']=$min;
                $data['max']=$max;
                $data['open']=$open;
                $data['creatime']=time();
                $status=$jiuyirobotrule->where($where)->save($data);
                if ($status){
                    $this->baoSuccess('操作成功',U('shangpin/index'));
                }else{
                    $this->baoError('操作失败');
                }
            }
        }else{
            $this->assign('id', $id); // 赋值数据集
            $this->display();
        }
    }




    public function add()
    {
        $this->display();
    }

    public function add1(){



//    $date=date('Ymdhis');//得到当前时间,如;20070705163148
//    $fileName=$_FILES['file']['name'];//得到上传文件的名字
//    $name=explode('.',$fileName);//将文件名以'.'分割得到后缀名,得到一个数组
//    $newPath=$date.'.'.$name[1];//得到一个新的文件为'20070705163148.jpg',即新的路径
//    $oldPath=$_FILES['file']['tmp_name'];//临时文件夹,即以前的路径
//
//    rename($oldPath,$newPath);

        if ( !file_exists("./upload/" . $_FILES["file"]["name"]))
        {

            $date=date('Ymdhis');//得到当前时间,如;20070705163148
            $fileName=$_FILES['file']['name'];//得到上传文件的名字
            $name=explode('.',$fileName);//将文件名以'.'分割得到后缀名,得到一个数组
            $newPath=$date.'.'.$name[1];//得到一个新的文件为'20070705163148.jpg',即新的路径

            move_uploaded_file($_FILES["file"]["tmp_name"], "./upload/" .$newPath);
        }


        $goods_img= "/upload/".$newPath;
        $goods_header = $this->_post('goods_header');  //商品标题
        $goods_name = $this->_post('goods_name');   //商品名称
        $buyback_price = $this->_post('buyback_price'); //会员回购价格
        $buyback_price_no = $this->_post('buyback_price_no'); //非会员回购价格
        $auction_price = $this->_post('auction_price');  // 竞拍价
        $strike_price = $this->_post('strike_price'); // 成交价
        $sold_out = $this->_post('sold_out'); // 是否上架
        // $inventory_num = $this->_post('inventory_num');//商品库存
        $auction_num = $this->_post('auction_num');//竞拍次数
//          $goods_img = $this->_post('goods_img');//商品图片


        if (empty($goods_name) || empty($goods_img) ||empty($goods_header) || empty($buyback_price) || empty($auction_price) || empty($strike_price) || empty($sold_out) || empty($auction_num)|| empty($buyback_price_no)){
            $this->baoError('操作失败,数据不能为空');
        }
        if (empty($_FILES["file"]["name"])){
            $this->baoError('操作失败,图片不能为空');
        }

        $data['goods_name']=$goods_name;
        $data['goods_header']=$goods_header;
        $data['goods_img']=$goods_img;
        $data['strike_price']=$strike_price*100;
        $data['auction_price']=$auction_price*100;
        $data['buyback_price']=$buyback_price*100;
        $data['buyback_price_no']=$buyback_price_no*100;
        $data['auction_num']=$auction_num;
        $data['inventory_num']=0;
        $data['sold_out']=$sold_out;
        $data['creatime']=time();
        $status=$this->creatagoods($data);
        if ($status){
            $this->baoSuccess('操作成功',U('shangpin/index'));
        }else{
            $this->baoError('操作失败');    }

    }


    /**创建商品
    商品表：goods
    商品id   id
    商品名称  goods_name
    商品标题  goods_header
    商品图 goods_img
    成交价strike_price
    竞拍价auction_price
    竞拍次数auction_num
    库存inventory_num
    是否上架sold_out  默认0  1 上架
    创建时间 creatime
     * @return
     */
    public function creatagoods($data)
    {
        $goods_id =  D('Goods')->add($data);
        if (!$goods_id){
            $this->baoError('操作失败');
        }

        Cac()->set('jiuyi_auction_'.$goods_id,serialize($data));
        Cac()->set('jiuyi_periods_num_'.$goods_id,3);
        $auction_money = ($data['strike_price'] - $data['auction_price'])/$data['auction_num'];

        $newperiods = array(
            'goods_id'=>$goods_id,
            'user_id'=>0,
            'is_auction'=>0,
            'creatime'=>time()
        );
        //生成3期产品
        for($i = 1;$i<4;$i++){
            $newperiods['periods_num']=$i;
            $periodsid= D('Periods')->add($newperiods);

            //该期数的商品抢购队列（用来解决竞拍的并发）
            for($j = 0;$j<$data['auction_num'];$j++){
                Cac()->rPush('jiuyi_auction_list_'.$periodsid,$auction_money);
            }
        }

        return true;
    }



    public function fahuo(){

        $User = D('Fahuo_record');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed'=>array('IN','0,-1'));

        if($danhao= $this->_param('danhao','htmlspecialchars')){

            $map['tracking_no'] = array('LIKE','%'.$danhao.'%');

            $this->assign('danhao',$danhao);
        }
        if($user_id= $this->_param('user_id','htmlspecialchars')){

            $map['user_id'] = array('LIKE','%'.$user_id.'%');

            $this->assign('user_id',$user_id);
        }


        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach($list as $k=>$val){
            if ($list[$k]['ship_time']>1){
                $list[$k]['ship_time']=date("Y-m-d H:i:s",  $list[$k]['ship_time']);
            }
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('ranks',D('Userrank')->fetchAll());
        $this->display(); // 输出模板

    }




    public function danhao(){

        $user_id = $this->_get('user_id');

        $id=$this->_get('id');

        if ( $this->isPost()){
            $tracking_no=$this->_post('danhao');//快递单号

            $Fahuo_record= M("Fahuo_record");
            $where['id']=$id;
            $where['user_id']=$user_id;
            $data['tracking_no']=$tracking_no;
            $data['ship_time']=time();
            $Fahuo_record->where($where)->save($data);
            $this->baoSuccess('操作成功',U('shangpin/fahuo'));

        }else{
            $this->assign('user_id',$user_id);
            $this->assign('id',$id);
            $this->display();
        }


    }

    public function huigou(){


        $User = D('Huigou_record');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('closed'=>array('IN','0,-1'));


        if($user_id= $this->_param('user_id','htmlspecialchars')){

            $map['user_id'] = array('LIKE','%'.$user_id.'%');

            $this->assign('user_id',$user_id);
        }

        if($goods_id= $this->_param('goods_id','htmlspecialchars')){

            $map['goods_id'] = array('LIKE','%'.$goods_id.'%');

            $this->assign('goods_id',$goods_id);
        }


        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id'=>'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach($list as $k=>$val){

            $list[$k]['money']=$list[$k]['money']/100;

            if ($list[$k]['ship_time']>1){
                $list[$k]['ship_time']=date("Y-m-d h:i:s",  $list[$k]['ship_time']);
            }
        }

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('ranks',D('Userrank')->fetchAll());
        $this->display(); // 输出模板
    }

    public function save(){
        $user_id = $this->_get('user_id');
        $id=$this->_get('id');
        $name=$this->_get('name');
        $mobile=$this->_get('mobile');
        $ship_site=$this->_get('ship_site');
        if ( $this->isPost()){

            $name=$this->_post('name');
            $mobile=$this->_post('mobile');
            $ship_site=$this->_post('ship_site');

            $Fahuo_record= M("Fahuo_record");
            $where['user_id']=$user_id;
            $where['id']=$id;
            $save['name']=$name;
            $save['mobile']=$mobile;
            $save['ship_site']=$ship_site;
            $Fahuo_record->where($where)->save($save);
            $this->baoSuccess('操作成功',U('shangpin/fahuo'));


        }else{
            $this->assign('user_id',$user_id);
            $this->assign('id',$id);
            $this->assign('name',$name);
            $this->assign('mobile',$mobile);
            $this->assign('ship_site',$ship_site);
            $this->display(); // 输出模板
        }
    }




}