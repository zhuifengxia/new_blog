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
            if($id){
                //查询题目总数
                $test_num = $baseModel->dataCount($this->dbconfig, "topics", "type_id={$data_new[$i]["id"]}");
            }else{
                $test_num = $baseModel->dataCount($this->dbconfig, "type", "type_fid={$data_new[$i]["id"]}");
            }

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
    public function topicList($courseid=0,$testid=0)
    {
        $baseModel = new ExamBase();
        if($testid){
            $where="type_id=$testid";
        }else{
            $where="1=1";
        }
        $data=$baseModel->dataList($this->dbconfig, "topics",$where ,2);
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
    public function addTopic($courseid,$testid,$id=0)
    {
        $baseModel = new ExamBase();
        $detail = ["topic_type" => 0];
        if ($id) {
            $detail = $baseModel->oneDetail($this->dbconfig, "topics", ['id' => $id]);
            //获取选项列表
            $options = $baseModel->dataList($this->dbconfig, "options", "topic_id=$id", 0, "1", "id asc");
            $detail["options"] = $options;
        }
        //获取课程列表信息
        $course = $baseModel->dataList($this->dbconfig, "type", "type_fid=0");
        //所选课程的试卷列表
        if (empty($testid)) {
            $testid = $course[0]['id'];
        }
        $test_list = $baseModel->dataList($this->dbconfig, "type", $testid ? "type_fid=$testid" : "type_fid=$testid");
        $this->assign("course_list", $course);
        $this->assign("test_list", $test_list);
        $this->assign("courseid", $courseid);
        $this->assign("testid", $testid);
        $this->assign("data", $detail);
        return $this->fetch();
    }

    /**
     * 题目 保存
     */
    public function doAddTopic()
    {
        $id=input("id",0);
        $courseid=input("course_id",0);
        $testid=input("type_id",0);
        $topic_title=input("topic_title","");
        $topic_type=input("topic_type","");
        $option_name=input("option_name","");
        $topic_parsing=input("topic_parsing","");
        $baseModel = new ExamBase();
        $where = "topic_title='$topic_title' and type_id=$testid and course_id=$courseid";
        if ($id) {
            $where .= " and id!=$id";
        }
        $data = $baseModel->oneDetail($this->dbconfig, "topics", $where);
        if($data){
            $this->error("该题目已经存在，请重新输入");
        }else{
            $insertdata=[
                "topic_title"=>$topic_title,
                "course_id"=>$courseid,
                "type_id"=>$testid,
                "topic_type"=>$topic_type,
                "topic_parsing"=>$topic_parsing
            ];
            if ($id) {
                //编辑
                $baseModel->updateOne($this->dbconfig, "topics",$insertdata, ["id" => $id]);
            } else {
                //新增
                $id=$baseModel->addOne($this->dbconfig, "topics", $insertdata);
            }
            //更新选项信息
            $baseModel->deleteOne($this->dbconfig,"options","topic_id=$id");
            for ($i=0;$i<count($option_name);$i++){
                if($option_name[$i]){
                    $is_right_data=input($i."is_right","0");
                    $insertdata=[
                        "option_name"=>$option_name[$i],
                        "is_right"=>$is_right_data,
                        "topic_id"=>$id,
                    ];
                    $baseModel->addOne($this->dbconfig, "options", $insertdata);
                }
            }
            $this->success("操作成功", "/admin/topic/list/$courseid/$testid");
        }
    }

    /**
     * 删除题目
     */
    public function delTopic($id)
    {
        $baseModel = new ExamBase();
        $data = $baseModel->oneDetail($this->dbconfig, "topics", "id=$id");
        $baseModel->updateOne($this->dbconfig, "topics", ["is_logic_del" => 1], ["id" => $id]);
        $this->success('删除成功', "/admin/topic/list/{$data['course_id']}/{$data['type_id']}");
    }

    //获取课程下的试卷信息
    public function childCourse($id)
    {
        $baseModel = new ExamBase();
        $test_list = $baseModel->dataList($this->dbconfig, "type", "type_fid=$id");
        return json(["status" => 0, "data" => $test_list]);
    }
}
