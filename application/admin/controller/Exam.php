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
    public function courseList($id=0)
    {
        $baseModel = new ExamBase();
        $data = $baseModel->dataList($this->dbconfig, "type", "type_fid=$id", 2);
        $data_new = $data->items();
        //查询课程下的试卷总数
        for ($i = 0; $i < count($data_new); $i++) {
            $test_num = $baseModel->dataCount($this->dbconfig, "type", "type_fid={$data_new[$i]["id"]}");
            $data_new[$i]["test_num"] = $test_num;
        }
        $data->items($data_new);//无效。。。
        $this->assign("page_msg", $data->render());
        $this->assign("courses", $data_new);
        $this->assign("type_fid", $id);
        return $this->fetch();
    }

    /**
     * 添加/编辑课程
     */
    public function addCourse($typefid=0,$id=0)
    {
        $baseModel = new ExamBase();
        $detail = [];
        if($typefid){
            //说明是添加试卷
            //获取课程集合
            $course=$baseModel->dataList($this->dbconfig, "type", "type_fid=0");
            $this->assign("type_list",$course);
        }
        if ($id) {
            //获取编辑的详情数据信息
            $detail = $baseModel->oneDetail($this->dbconfig, "type", ['id' => $id]);
        }
        $this->assign("type_fid",$typefid);
        $this->assign("details", $detail);
        return $this->fetch();
    }

    /**
     * 保存课程信息
     */
    public function doAddCourse()
    {
        $baseModel = new ExamBase();
        $id = input("id", 0);
        $type_name = input("type_name", "");
        $type_fid = input("type_fid", 0);
        $where = "type_name='$type_name' and type_fid=$type_fid";
        if ($id) {
            $where .= " and id!=$id";
        }
        $data = $baseModel->oneDetail($this->dbconfig, "type", $where);
        if ($data) {
            //已经存在
            $this->error("该课程已经存在，请重新输入");
        } else {
            if ($id) {
                //编辑
                $baseModel->updateOne($this->dbconfig, "type", ["type_name" => $type_name, "type_fid" => $type_fid], ["id" => $id]);
            } else {
                //新增
                $baseModel->addOne($this->dbconfig, "type", ["type_name" => $type_name, "type_fid" => $type_fid]);
            }
            $this->success("操作成功", "/admin/course/list/$type_fid");
        }

    }

    /**
     * 删除课程
     * @param $id 课程id
     */
    public function delCourse($id)
    {
        $baseModel = new ExamBase();
        $baseModel->updateOne($this->dbconfig, "type", ["is_logic_del" => 1], ["id" => $id]);
        $this->success('删除成功', '/admin/course/list');
    }

    /**
     * 题目列表
     */
    public function topicList($courseid,$testid)
    {
        $baseModel = new ExamBase();
        $data=$baseModel->dataList($this->dbconfig, "topics", "type_id=$testid",2);
        $this->assign("courseid",$courseid);
        $this->assign("testid",$testid);
        $this->assign("data",$data);
        return $this->fetch();
    }

    /**
     * 题目添加
     * @param $courseid 课程id
     * @param $testid 试卷id
     * @param $id 题目id
     */
    public function addTopic($courseid,$testid,$id)
    {
        $baseModel = new ExamBase();
    }
}
