<?php



class AdminAction extends CommonAction {

    private $create_fields = array('username', 'password', 'role_id', 'mobile','city_id');
    private $edit_fields = array('password', 'role_id', 'mobile','city_id');

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

            $this->createChecks();

//            $business = D('Business');
//            $business_code=$business->add($data);
//            if ($business_code) {
//                $data['business_code']=$business_code;
//                $data = $this->createCheck($data);
//                $admin=D('Admin');
//                if ($admin->add($data)){
//                    $this->baoSuccess('添加成功', U('admin/index'));
//                }else{
//                    $this->baoError('操作失败！');
//                }
//
//            }
//            $this->baoError('操作失败！');
        } else {
            $this->assign('roles', D('Role')->fetchAll());
            $this->display();
        }
    }


    public function createChecks(){



        $data['shenfen'] = (int) $_POST['role_id'];
        if (empty($data['shenfen'])) {
            $this->baoError('角色不能为空');
        }

         $roleinfo=D("role")->where(array("role_id"=>$data['shenfen']))->find();


//-------------------------------------------------------------------------------
        if ($roleinfo['role_name'] == "商户"){
            $data['nickname'] = htmlspecialchars($_POST['nickname']);
            if (empty($data['nickname'])) {
                $this->baoError('商户名不能为空');
            }

            $data['account'] = htmlspecialchars($_POST['account']);
            if (empty($data['account'])) {
                $this->baoError('账号不能为空');
            }

            $business=D("business")->where(array("account"=>$data['account']))->find();

            if (!empty($business)) {
                $this->baoError('账号已存在！');
            }

            if (D('Admin')->getAdminByUsername($data['account'])) {
                $this->baoError('账号已经存在');
            }

            $data['password'] = md5($_POST['password']);
            if (empty($data['password'])) {
                $this->baoError('密码不能为空');
            }


            $data['accessKey'] = md5($_POST['accessKey']);

            if (empty($data['accessKey'])) {
                $this->baoError('密钥不能为空');
            }

            $data['fee'] = htmlspecialchars($_POST['fee']/100);
            if (empty($data['fee'])) {
                $this->baoError('费率不能为空');
            }

            $data['mobile'] = htmlspecialchars($_POST['mobile']);
            if (empty($data['mobile'])) {
                $this->baoError('电话不能为空');
            }

            if (!isMobile($data['mobile'])) {
                $this->baoError('手机格式不正确');
            }

//            $data['shenfen']="商户";
            $data['status']=1;
            $data['paypassword']=md5(123456);
            $data['creatime']=time();


            $business = D('Business');
            $business_code=$business->add($data);

            if ($business_code) {
                $data['business_code']=$business_code;
                $data = $this->createCheck($data);
                $admin=D('Admin');
                if ($admin->add($data)){
                    $this->baoSuccess('添加成功', U('admin/index'));
                }else{
                    $this->baoError('操作失败！');
                }

            }


        }

//----------------------------------------------------------------------------

        if ($roleinfo['role_name'] =="管理员"){


            $data['username'] = htmlspecialchars($_POST['account']);
            if (empty( $data['username'])) {
                $this->baoError('账号不能为空');
            }


            if (D('Admin')->getAdminByUsername($data['username'])) {
                $this->baoError('账号已经存在');
            }



            $data['role_id'] = (int) $data['shenfen'];
            if (empty($data['role_id'])) {
                $this->baoError('角色不能为空');
            }


            $data['mobile'] = htmlspecialchars($_POST['mobile']);
            if (empty($data['mobile'])) {
                $this->baoError('手机不能为空');
            }
            $data['create_time'] = NOW_TIME;
            $data['create_ip'] = get_client_ip();

            $admin=D('Admin');
            if ($admin->add($data)){
                $this->baoSuccess('添加成功', U('admin/index'));
            }else{
                $this->baoError('操作失败！');
            }


        }

       // $data = $this->checkFields($this->_post('data', false), $this->create_fields);


    }



    private function createCheck($data) {

        //-------------------------------------------------------------------------

        $data['username'] = htmlspecialchars($data['account']);
        $data['role_id'] = (int) $data['shenfen'];
        if (empty($data['role_id'])) {
            $this->baoError('角色不能为空');
        }
        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->baoError('手机不能为空');
        }
        $data['create_time'] = NOW_TIME;
        $data['create_ip'] = get_client_ip();
        return $data;
    }







    public function edit($admin_id = 0) {
        if ($admin_id = (int) $admin_id) {
            $obj = D('Admin');
            if (!$detail = $obj->find($admin_id)) {
                $this->baoError('请选择要编辑的管理员');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['admin_id'] = $admin_id;
                if ($obj->save($data)) {
                    $this->baoSuccess('操作成功', U('admin/index'));
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
        if ($this->_admin['role_id'] != 1) { //非超级管理员不允许修改用户的角色信息
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

    public function delete($admin_id = 0) {
        if (is_numeric($admin_id) &&($admin_id = (int) $admin_id)) {
            $obj = D('Admin');
            $obj->save(array('admin_id' => $admin_id, 'closed' => 1));
            $this->baoSuccess('删除成功！', U('admin/index'));
        } else {
            $admin_id = $this->_post('admin_id', false);
            if (is_array($admin_id)) {
                $obj = D('Admin');
                foreach ($admin_id as $id) {
                    $obj->save(array('admin_id' => $id, 'closed' => 1));
                }
                $this->baoSuccess('删除成功！', U('admin/index'));
            }
            $this->baoError('请选择要删除的管理员');
        }
    }

}
