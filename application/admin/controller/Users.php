<?php

/**
 * Description: 后台数据用户管理相关操作.
 * Author: momo
 * Date: 2019-09-12
 * Copyright: momo
 */
namespace app\admin\controller;
use app\common\model\Users as UserModel;

class Users extends Base
{
    /**
     * 用户列表数据
     */
    public function userList()
    {
        $userModel = new UserModel();
        $users=$userModel->dataList([],1);
        $this->assign('users', $users);
        return $this->fetch('users');
    }

    /**
     * 添加/编辑用户
     */
    public function addUser($id=0)
    {
        $userModel = new UserModel();
        if ($id) {
            //获取编辑的详情数据信息
            $detail = $userModel->oneDetail(['id' => $id]);
        } else {
            $detail = $userModel->toArray();
        }
        $this->assign("details", $detail);
        return $this->fetch();
    }

    /**
     * 用户信息保存
     */
    public function doAddUser()
    {
        $userModel = new UserModel();
        //是否存在
        $id = input('id', 0);
        $username = input('user_name', '');
        $usersource = input('user_source', '');
        $where = [
            ['user_name', '=', $username],
            ['user_source', '=', $usersource],
        ];
        if ($id) {
            $where[] = ['id', '<>', $id];
        }
        $data = $userModel->oneDetail($where);
        if ($data) {
            $this->error('已经存在，请重新输入', '/admin/users/add/' . $id);
        } else {
            if ($id) {
                $_POST['update_time'] = time();
                $userModel->updateOne($_POST, ['id' => $id]);
            } else {
                $_POST['create_time'] = time();
                $_POST['update_time'] = time();
                $userModel->addOne($_POST);
            }

            $this->success('保存成功', '/admin/users/list');
        }
    }

    /**
     * 删除用户
     * @param $id 用户id
     */
    public function delUser($id)
    {
        $userModel = new UserModel();
        $userModel->deleteOne(['id'=>$id]);
        $this->success('删除成功', '/admin/users/list');
    }
}
