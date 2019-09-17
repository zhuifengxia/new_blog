<?php

/**
 * Description: 后台数据文章管理相关操作.
 * Author: momo
 * Date: 2019-09-16
 * Copyright: momo
 */
namespace app\admin\controller;
use app\common\model\ArticleType as TypeModel;
use app\common\model\Articles as ArtModel;
class Articles extends Base
{
    /**
     * 文章列表数据
     */
    public function artList()
    {
        $artModel = new ArtModel();
        $types=$artModel->dataList([],1);
        $this->assign('articles', $types);
        return $this->fetch('articles');
    }

    /**
     * 添加/编辑分类
     */
    public function addType($id=0)
    {
        $typeModel = new TypeModel();
        if ($id) {
            //获取编辑的详情数据信息
            $detail = $typeModel->oneDetail(['id' => $id]);
        } else {
            $detail = $typeModel->toArray();
        }
        $this->assign("details", $detail);
        return $this->fetch();
    }

    /**
     * 分类信息保存
     */
    public function doAddType()
    {
        $typeModel = new TypeModel();
        //是否存在
        $id = input('id', 0);
        $type_name = input('type_name', '');
        $where = [
            ['type_name', '=', $type_name],
        ];
        if ($id) {
            $where[] = ['id', '<>', $id];
        }
        $data = $typeModel->oneDetail($where);
        if ($data) {
            $this->error('已经存在，请重新输入', '/admin/types/add/' . $id);
        } else {
            if ($id) {
                $typeModel->updateOne($_POST, ['id' => $id]);
            } else {
                $typeModel->addOne($_POST);
            }
            $this->success('保存成功', '/admin/types/list');
        }
    }

    /**
     * 删除文章分类
     * @param $id 分类id
     */
    public function delType($id)
    {
        $typeModel = new TypeModel();
        $typeModel->deleteOne(['id' => $id]);
        $this->success('删除成功', '/admin/types/list');
    }
}
