<?php

/**
 * Description: 后台数据微语管理相关操作.
 * Author: momo
 * Date: 2019-09-16
 * Copyright: momo
 */
namespace app\admin\controller;
use app\common\model\Gossips as GossipModel;
use MoCommon\Support\UploadFiles;

class Gossips extends Base
{
    /**
     * 微语列表数据
     */
    public function msgList()
    {
        $msgModel = new GossipModel();
        $msgs=$msgModel->dataList(GossipModel::class,["data_type"=>0],1);
        $this->assign('gossips', $msgs);
        return $this->fetch('gossips');
    }

    /**
     * 添加/编辑微语
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
     * 微语信息保存
     */
    public function doAddMsg()
    {
        $msgModel = new GossipModel();
        //是否存在
        $id = input('id', 0);
        $data_msg = input('data_msg', '');
        $data_img = $_FILES['data_img'];
        if(empty($data_img)){
            $data_img=input("data_img1","");
        }
        $where = [
            ['data_msg', '=', $data_msg],
        ];
        if ($id) {
            $where[] = ['id', '<>', $id];
        }
        $data = $msgModel->oneDetail(GossipModel::class,$where);
        if ($data) {
            $this->error('已经存在，请重新输入', '/admin/gossips/add/' . $id);
        } else {
            $insert = [
                'data_msg' => $data_msg
            ];
            if ($data_img['tmp_name']) {
                //有文件上传
                $filepath = env('root_path') . 'public/static/upload/gossips/' . date('Ymd') . "/";
                $fileresult = UploadFiles::single_file_upload("", $data_img, $filepath, '');
                if ($fileresult['status'] != 0 && !empty($fileresult['data'])) {
                    $this->error($fileresult['msg']);
                }
                $fileurl = $fileresult['data'];
                $insert['data_img'] = '/static/upload/gossips/' . date('Ymd') . '/' . $fileurl;
            }
            if ($id) {
                $data = $msgModel->oneDetail(GossipModel::class,['id'=>$id]);
                //查看原来的信息是否有图片，有的话删除原来的图片
                if ($data['data_img'] && $data_img['tmp_name']) {
                    //删除原来的图片文件
                    @unlink(env('root_path') . "public" . $data['data_img']);
                }
                $msgModel->updateOne(GossipModel::class,$insert, ['id' => $id]);
            } else {
                $msgModel->addOne(GossipModel::class,$insert);
            }
            $this->success('保存成功', '/admin/gossips/list');
        }
    }

    /**
     * 删除微语
     * @param $id 微语id
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
        $this->success('删除成功', '/admin/gossips/list');
    }
}
