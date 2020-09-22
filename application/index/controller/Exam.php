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

    /**
     * 记录答题记录
     */
    public function setRecord()
    {
        $user = session("web_index_user");
        $user_id = $user["id"];
        $topic_id = input("topic_id", "");
        $user_right = input("user_right", 0);
        $baseModel = new ExamBase();
        $topic = $baseModel->oneDetail($this->dbconfig, "topics", "id=$topic_id");
        $course_id = $topic["course_id"];
        $test_id = $topic["type_id"];
        //查询用户是否答过这个题
        $where = "user_id=$user_id and course_id=$course_id and test_id=$test_id and topic_id=$topic_id";
        $isdata = $baseModel->oneDetail($this->dbconfig, "records", $where);
        if ($isdata) {
            //更新答题次数
            if ($user_right) {
                //答对了
                $fieldname = "right_num";
            } else {
                //答错了
                $fieldname = "wrong_num";
            }
            $baseModel->dataInc($this->dbconfig, "records", $where, $fieldname);
        } else {
            //新增
            if ($user_right) {
                //答对了
                $right_num = 1;
                $wrong_num = 0;
            } else {
                //答错了
                $right_num = 0;
                $wrong_num = 1;
            }
            $insert = [
                "user_id" => $user_id,
                "course_id" => $course_id,
                "test_id" => $test_id,
                "topic_id" => $topic_id,
                "right_num" => $right_num,
                "wrong_num" => $wrong_num
            ];
            $baseModel->addOne($this->dbconfig, "records", $insert);
        }
        return json(["code" => 0, "msg" => "记录成功"]);
    }


    /**
     * 用户登录
     */
    public function userLogin()
    {
        $user_name = input("user_name", "");
        $user_pwd = input("user_pwd", "");
        $login_type = input("login_type", 0);
        if (empty($login_type)) {
            //登录
            $data = db("users")
                ->where("user_name", $user_name)
                ->where("user_pwd", md5($user_pwd))
                ->where("user_source", 0)
                ->find();
            if ($data) {
                session("web_index_user", $data);
                $result = ["code" => 0, "msg" => "登录成功"];
            } else {
                $result = ["code" => 1, "msg" => "账号或密码错误"];
            }
        } else {
            //注册
            $data = db("users")
                ->where("user_name", $user_name)
                ->where("user_source", 0)
                ->find();
            if ($data) {
                $result = ["code" => 2, "msg" => "该用户已经存在"];
            } else {
                $insert = [
                    "user_name" => $user_name,
                    "user_pwd" => md5($user_pwd),
                    "user_source" => 0,
                    "create_time" => time(),
                    "update_time" => time()
                ];
                db("users")
                    ->insert($insert);
                $data = db("users")
                    ->where("user_name", $user_name)
                    ->where("user_source", 0)
                    ->find();
                session("web_index_user", $data);
                $result = ["code" => 0, "msg" => "注册成功"];
            }
        }
        return json($result);
    }
}
