<?php

class KuaiAction extends CommonAction
{   private $create_fields = array('money','nums', 'interval', 'package','switch');
    private $edit_fields = array('interval', 'package', 'switch');
    public function Alltask()
    {
        $User = D('Kuai');
        import('ORG.Util.Page'); // 导入分页类
        //$map = array('closed' => array('IN', '0,-1'));
        $map="1";
        /*if ($game = $this->_param('game', 'htmlspecialchars')) {
            $map['game'] = array('LIKE', '%' . $game . '%');
            $this->assign('game', $game);
        }
        if($title = $this->_param('title','htmlspecialchars')){
            $map['title'] = array('LIKE','%'.$title.'%');
            $this->assign('title',$title);
        }
        if ($conf_min = $this->_param('conf_min', 'htmlspecialchars')) {
            $map['conf_min'] = array('LIKE', '%' . $conf_min . '%');
            $this->assign('conf_min', $conf_min);
        }
        if($conf_max = $this->_param('conf_max','htmlspecialchars')){
            $map['conf_max'] = array('LIKE','%'.$conf_max.'%');
            $this->assign('conf_max',$conf_max);
        }
        if($is_show = $this->_param('is_show','htmlspecialchars')){
            $map['is_show'] = array('LIKE','%'.$is_show.'%');
            $this->assign('$is_show',$is_show);
        }*/

        $count = $User->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 8); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $User->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $val['reg_ip_area'] = $this->ipToArea($val['reg_ip']);
            $val['last_ip_area'] = $this->ipToArea($val['last_ip']);
            $list[$k] = $val;
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出

        $this->display(); // 输出模板
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('kuai');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('room/allroom'));
            }
            $obj->cleanCache();
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {

        $data = $this->checkFields($this->_post('data', false), $this->create_fields);

        $data['game'] = htmlspecialchars($data['game']);
        if (empty($data['money'])) {
            $this->baoError('金额不能为空');
        } $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['nums'])) {
            $this->baoError('小红包个数不能空');
        } $data['conf_min'] = htmlspecialchars($data['conf_min']);
        if (empty($data['interval'])) {
            $this->baoError('间隔时间不能为空');
        }$data['conf_max'] = htmlspecialchars($data['conf_max']);
        if (empty($data['package'])) {
            $this->baoError('存续个数不能为空');
        }
        $data['switch'] = htmlspecialchars($data['switch']);
        $data['creat']=time();
        return $data;
    }
    public function edit() {
        $room_id=(int)$_GET['id'];
        if ($room_id){
            $obj = D('kuai');
            $detail = $obj->find($room_id);

            if ($this->isPost()){
                $data = $this->editCheck();
                $data['id'] = $room_id;
                if (false !== $obj->save($data)) {
                    //$obj->clearCache();
                    $this->baoSuccess('操作成功', U('kuai/alltask'));
                }else{
                    $this->baoError('存续个数不能为空');
                }

            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        }else{
            $this->baoError('未找到');
        }
    }


    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);

        /*$data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        } $data['conf_min'] = htmlspecialchars($data['conf_min']);
        if (empty($data['conf_min'])) {
            $this->baoError('最小不能为空');
        } $data['conf_max'] = htmlspecialchars($data['conf_max']);
        if (empty($data['conf_max'])) {
            $this->baoError('最大不能为空');
        }$data['is_show'] = htmlspecialchars($data['is_show']);*/

        return $data;
    }


}