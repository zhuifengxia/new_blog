<?php

/**
 * Description: 后台数据古诗词管理相关操作.
 * Author: momo
 * Date: 2019-09-16
 * Copyright: momo
 */

namespace app\admin\controller;

use app\common\model\Poetry as PoetryModel;

class Poetry extends Base
{
    /**
     * 古诗词列表
     */
    public function poetryList()
    {
        $poetryModel = new PoetryModel();
        $poetrys = $poetryModel->dataList(PoetryModel::class, [], 1);
        $this->assign('poetrys', $poetrys);
        return $this->fetch('poetrys');
    }

    /**
     * 添加/编辑古诗词
     */
    public function addPoetry($id = 0)
    {
        $poetryModel = new PoetryModel();
        if ($id) {
            //获取编辑的详情数据信息
            $detail = $poetryModel->oneDetail(PoetryModel::class, ['id' => $id]);
        } else {
            $detail = $poetryModel->toArray();
        }
        $this->assign("details", $detail);
        return $this->fetch();
    }

    /**
     * 古诗词保存
     */
    public function doAddPoetry()
    {
        $poetryModel = new PoetryModel();
        //是否存在
        $id = input('id', 0);
        $poetry_content = input('poetry_content', '');
        $where = [
            ['poetry_content', '=', $poetry_content],
        ];
        if ($id) {
            $where[] = ['id', '<>', $id];
        }
        $data = $poetryModel->oneDetail(PoetryModel::class, $where);
        if ($data) {
            $this->error('已经存在，请重新输入', '/admin/poetry/add/' . $id);
        } else {
            if ($id) {
                $_POST["is_learn"] = (isset($_POST["is_learn"]) == 1 ? 1 : 0);
                $poetryModel->updateOne(PoetryModel::class, $_POST, ['id' => $id]);
            } else {
                $poetryModel->addOne(PoetryModel::class, $_POST);
            }
            $this->success('保存成功', '/admin/poetry/list');
        }
    }

    /**
     * 删除古诗词
     * @param $id 古诗词id
     */
    public function delPoetry($id)
    {
        $poetryModel = new PoetryModel();
        $poetryModel->deleteOne(PoetryModel::class, ['id' => $id]);
        $this->success('删除成功', '/admin/poetry/list');
    }
}
