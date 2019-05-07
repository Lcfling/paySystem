<?php



class FuliAction extends CommonAction {



    public function index() {
        $Admin = D('Admin');
        import('ORG.Util.Page'); // 导入分页类
        $keyword = trim($this->_param('keyword', 'htmlspecialchars'));
        $map = array('closed' => 0);
        if ($keyword) {
            $map['username'] = array('LIKE', '%'.$keyword.'%');
        }
        $count = $Admin->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Admin->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => $val) {
            $val['create_ip_area'] = $this->ipToArea($val['create_ip']);
            $val['last_ip_area']   = $this->ipToArea($val['last_ip']);
            $list[$k] = $Admin->_format($val);
        }
        $this->assign('citys', D('City')->fetchAll());
        $Page->parameter .= 'keyword=' . urlencode($keyword);
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function create() {
        if ($this->isPost()) {
            //$data = $this->createCheck();
            $obj = D('Fuli');
            $data=$_POST;

            $bengin=$data["bengintime"];
            $every=$data["every"];

            if($bengin<time()){
                $this->baoError('开始时间不能小于当前时间');
            }
            if($every<5){
                $this->baoError('间隔时间必须大于5秒');
            }
            $addtime=time();

            $money=(int)$_POST["money"];
            $num=(int)$_POST["num"];
            for ($i=0;$i<$data["totle"];$i++){
                $this->addtask($num,$money,$addtime);
                $addtime+=$every;
            }
            $this->baoSuccess('添加成功', U('admin/index'));
        } else {
            $this->display();
        }
    }
    private function addtask($num,$money,$runtime) {
        $data["num"]=$num;
        $data["money"]=$money;
        $data["runtime"]=$runtime;
        $data["is_over"]=0;
        $data["creatime"]=time();
        D("fuli")->add($data);
        return true;
    }
}
