<?php

/**
 * Description: 后台数据相册管理相关操作.
 * Author: momo
 * Date: 2019-09-16
 * Copyright: momo
 */
namespace app\admin\controller;
use app\common\model\Gossips as GossipModel;
use MoCommon\Support\UploadFiles;

class Album extends Base
{
    /**
     * 照片列表数据
     */
    public function msgList()
    {
        $msgModel = new GossipModel();
        $msgs=$msgModel->dataList(GossipModel::class,["data_type"=>1],1);
        $this->assign('gossips', $msgs);
        return $this->fetch('gossips');
    }

    /**
     * 添加/编辑照片
     */
    public function addMsg($id=0)
    {
        $msgModel = new GossipModel();
        if ($id) {
            //获取编辑的详情数据信息
            $detail = $msgModel->oneDetail(GossipModel::class,['id' => $id]);
        } else {
            $detail = $msgModel->toArray();
        }
        $this->assign("details", $detail);
        return $this->fetch();
    }

    /**
     * 照片信息保存
     */
    public function doAddMsg()
    {
        $msgModel = new GossipModel();
        //是否存在
        $id = input('id', 0);
        $data_msg = input('data_msg', '');
        $data_location = input('data_location', '');
        $data_img = $_FILES['data_img'];
        if (empty($data_img)) {
            $data_img = input("data_img1", "");
        }
        $where = [
            ['data_msg', '=', $data_msg],
        ];
        if ($id) {
            $where[] = ['id', '<>', $id];
        }
        $data = $msgModel->oneDetail(GossipModel::class, $where);
        if ($data) {
            $this->error('已经存在，请重新输入', '/admin/album/add/' . $id);
        } else {
            $insert = [
                'data_msg' => $data_msg,
                'data_location' => $data_location,
                'data_type' => 1
            ];
            if ($data_img['tmp_name']) {
                //有文件上传
                $filepath = env('root_path') . 'public/static/upload/album/' . date('Ymd') . "/";
                $fileresult = UploadFiles::single_file_upload("", $data_img, $filepath, '');
                if ($fileresult['status'] != 0 && !empty($fileresult['data'])) {
                    $this->error($fileresult['msg']);
                }
                $fileurl = $fileresult['data'];
                $insert['data_img'] = '/static/upload/album/' . date('Ymd') . '/' . $fileurl;
            }
            if ($id) {
                $data = $msgModel->oneDetail(GossipModel::class, ['id' => $id]);
                //查看原来的信息是否有图片，有的话删除原来的图片
                if ($data['data_img'] && $data_img['tmp_name']) {
                    //删除原来的图片文件
                    @unlink(env('root_path') . "public" . $data['data_img']);
                }
                $msgModel->updateOne(GossipModel::class, $insert, ['id' => $id]);
            } else {
                $msgModel->addOne(GossipModel::class, $insert);
            }
            $this->success('保存成功', '/admin/album/list');
        }
    }

    /**
     * 删除照片
     * @param $id 照片id
     */
    public function delMsg($id)
    {
        $msgModel = new GossipModel();
        $data = $msgModel->oneDetail(GossipModel::class,['id' => $id]);
        $msgModel->deleteOne(GossipModel::class,['id' => $id]);
        //删除图片文件信息
        if ($data['data_img']) {
            //删除原来的图片文件
            @unlink(env('root_path') . "public" . $data['data_img']);
        }
        $this->success('删除成功', '/admin/album/list');
    }
}
