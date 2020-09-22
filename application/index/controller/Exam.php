<?php
/**
 * Description: 前台题库.
 * Author: momo
 * Date: 2019-09-19
 * Copyright: momo
 */
namespace app\index\controller;

use app\common\model\ExamBase;

class Exam extends Base
{
    private $dbconfig="db_exam";
    //课程列表
    public function index()
    {
        $baseModel = new ExamBase();
        $data = $baseModel->dataList($this->dbconfig, "type", "type_fid=0");
        //获取课程下面的试卷列表数据
        for ($i = 0; $i < count($data); $i++) {
            $child = $baseModel->dataList($this->dbconfig, "type", "type_fid={$data[$i]["id"]}");
            $data[$i]["child"] = $child;
        }
        $this->assign("course_list", $data);
        $this->assign('page_num', 6);
        return $this->fetch();
    }

    /**
     * 试卷信息
     */
    public function testList($courseid,$testid)
    {
        $baseModel = new ExamBase();
        //获取试卷的题目信息
        $data = $baseModel->dataList($this->dbconfig, "topics", "course_id=$courseid and type_id=$testid", 2,0,"topic_num asc");
        $data_new = $data->items();
        //查询课程下的试卷总数
        for ($i = 0; $i < count($data_new); $i++) {
            //题目选项信息
            $options = $baseModel->dataList($this->dbconfig, "options", "topic_id={$data_new[$i]['id']}",0,1,"id asc");
            $data_new[$i]["options"] = $options;
        }
        $data->items($data_new);//无效。。。
        //获取课程名称，
        $course_name=$baseModel->dataValue($this->dbconfig,"type","type_name","id=$courseid");
        $topic_name=$baseModel->dataValue($this->dbconfig,"type","type_name","id=$testid");
        $this->assign("nav",["course_name"=>$course_name,"course_id"=>$courseid,"topic_name"=>$topic_name,"topic_id"=>$testid]);
        $this->assign("page_msg", $data->render());
        $this->assign("topics", $data_new);
        $this->assign('page_num', 6);
        return $this->fetch();
    }
}
