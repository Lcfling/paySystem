<?php



class AlipayAction extends CommonAction {

    private $create_fields = array('aliuid', 'mobile','limit');
    private $edit_fields = array('is_active', 'limit');

    public function index() {
        $Alipay = D('Alipayaccount');
        import('ORG.Util.Page'); // 导入分页类
        $keyword = trim($this->_param('keyword', 'htmlspecialchars'));
        $map = array('is_active' => 1);
        if ($keyword) {
            $map['username'] = array('LIKE', '%'.$keyword.'%');
        }
        $count = $Alipay->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Alipay->where($map)->limit($Page->firstRow . ',' . $Page->listRows)->select();
        foreach ($list as $k => &$val) {
            $val["creatime"]=date('Y-m-d H:i:s',$val['creatime']);
        }

        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Alipayaccount');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('Alipay/index'));
            }
            $this->baoError('操作失败！');
        } else {
            //$this->assign('roles', D('Role')->fetchAll());
            $this->display();
        }
    }

    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['aliuid'] = htmlspecialchars($data['aliuid']);
        if (empty($data['aliuid'])) {
            $this->baoError('支付宝UID不能空');
        }
        if (D('Alipay')->where("aliuid=".$data['aliuid'])->find()) {
            $this->baoError('用户名已经存在');
        }

        if (!isMobile($data['mobile'])) {
            $this->baoError('手机格式不正确');
        }
        $data['creatime'] = time();
        $data['is_active'] = 1;
        $data['limit'] = 10000;
        return $data;
    }

    public function edit($id = 0) {
        if ($id = (int) $id) {
            $obj = D('Alipayaccount');
            if (!$detail = $obj->find($id)) {
                $this->baoError('请选择要编辑的管理员');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['id'] = $id;
                if ($obj->save($data)) {
                    $this->baoSuccess('操作成功', U('Alipay/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('roles', D('Role')->fetchAll());
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的管理员');
        }
    }

    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);

        if ($data['password'] === '******') {
            unset($data['password']);
        } else {
            $data['password'] = htmlspecialchars($data['password']);
            if (empty($data['password'])) {
                $this->baoError('密码不能为空');
            }
            $data['password'] = md5($data['password']);
        }
        if ($this->_Alipay['role_id'] != 1) { //非超级管理员不允许修改用户的角色信息
            unset($data['role_id']);
        } else {
            $data['role_id'] = (int) $data['role_id'];
            if (empty($data['role_id'])) {
                $this->baoError('角色不能为空');
            }
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('手机不能为空');
        }
        if (!isMobile($data['mobile'])) {
            $this->baoError('手机格式不正确');
        }
        return $data;
    }

    public function delete($Alipay_id = 0) {
        if (is_numeric($Alipay_id) &&($Alipay_id = (int) $Alipay_id)) {
            $obj = D('Alipay');
            $obj->save(array('Alipay_id' => $Alipay_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('Alipay/index'));
        } else {
            $Alipay_id = $this->_post('Alipay_id', false);
            if (is_array($Alipay_id)) {
                $obj = D('Alipay');
                foreach ($Alipay_id as $id) {
                    $obj->save(array('Alipay_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('Alipay/index'));
            }
            $this->baoError('请选择要删除的管理员');
        }
    }

    public function qrcode(){
        $this->display();
    }

}
