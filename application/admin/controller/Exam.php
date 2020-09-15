<?php

/**
 * Description: 后台数据题库相关操作.
 * Author: momo
 * Date: 2020-09-16
 * Copyright: momo
 */
namespace app\admin\controller;


use app\common\model\ExamBase;

class Exam extends Base
{
    private $dbconfig="db_exam";
    /**
     * 课程列表数据
     */
    public function courseList()
    {
        $baseModel = new ExamBase();
        $data = $baseModel->dataList($this->dbconfig, "type", "type_fid=0", 2);
        $data_new = $data->items();
        //查询课程下的试卷总数
        for ($i = 0; $i < count($data_new); $i++) {
            $test_num = $baseModel->dataCount($this->dbconfig, "type", "type_fid={$data_new[$i]["id"]}");
            $data_new[$i]["test_num"] = $test_num;
        }
        $data->items($data_new);//无效。。。
        $this->assign("page_msg",$data->render());
        $this->assign("courses", $data_new);
        return $this->fetch();
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
