<?php
/**
 * Description: 文章类文件.
 * Author: momo
 * Date: 2019-09-12
 * Copyright: momo
 */

namespace app\common\model;


use MoCommon\Support\Helper;

class Articles extends Base
{
    public function getArticleTypeAttr($value)
    {
        $status = [0 => '原创', 1 => '转载', 2 => '翻译'];
        return ['val' => $value, 'text' => $status[$value]];
    }
    public function __construct($data = [])
    {
        $data=[
            'id'=>0,
            'article_title'=>'',
            'type_id'=>0,
            'read_num'=>0,
            'is_publish'=>1,
            'is_top'=>0,
            'article_type'=>0,
            'article_msg'=>'',
            'article_digest'=>'',
            'article_tag'=>'',
            'article_img'=>'',
            'create_time'=>0,
            'update_time'=>0,
            'publish_time'=>0,
            'publish_time'=>0,
        ];
        parent::__construct($data);
    }

    //获取数据列表
    public function artList($where=[],$page=1)
    {
        $data = db("articles")
            ->where($where)
            ->order('create_time desc')
            ->page($page, 10)
            ->select();
        for ($i = 0; $i < count($data); $i++) {
            $status = [0 => '原创', 1 => '转载', 2 => '翻译'];
            $data[$i]['article_type'] = $status[$data[$i]['article_type']];
            $data[$i]['type_name'] = db("articletype")
                ->field("type_name")
                ->where("id", $data[$i]['type_id'])
                ->value("type_name");
        }
        return $data;
    }

    //详情数据
    public function artDetail($id)
    {
        $data = db("articles")
            ->where("id", $id)
            ->find();
        $data['type_name'] = db("articletype")
            ->field("type_name")
            ->where("id", $data['type_id'])
            ->value("type_name");
        $next_id = db("articles")
            ->field("id")
            ->where("id", ">", $id)
            ->order("id desc")
            ->value("id");
        $per_id = db("articles")
            ->field("id")
            ->where("id", "<", $id)
            ->order("id desc")
            ->value("id");
        //获取前十条文章评论信息
        $postnum = db("posting")
            ->where("data_id", $id)
            ->where("is_logic_del", 0)
            ->count();
        $posting = db("posting")
            ->where("data_id", $id)
            ->where("is_logic_del", 0)
            ->order("id desc")
            ->page(1, 10)
            ->select();
        $postdata = ["post_num" => $postnum, "posting" => $posting];
        //增加阅读量
        db("articles")
            ->where("id", $id)
            ->setInc("read_num");
        return ["data" => $data, "next_id" => $next_id, "per_id" => $per_id, "post_data" => $postdata];
    }

    //评论列表数据
    public function posting($id,$page)
    {
        $posting = db("posting")
            ->where("data_id", $id)
            ->where("is_logic_del", 0)
            ->order("id desc")
            ->page($page, 10)
            ->select();
        return $posting;
    }

    //提交评论
    public function postingSubmit($id,$msg)
    {
        $ip = Helper::getIp();
        $userdata=self::randData();
        $nickname = $userdata["nick_name"];
        $user_img = $userdata["user_img"];
        $data = [
            "post_content" => $msg,
            "data_id" => $id,
            "post_ip" => $ip,
            "create_time" => time(),
            "nick_name" => $nickname,
            "user_img" => $user_img
        ];
        db("posting")
            ->insert($data);
        return ["nick_name" => $nickname, "user_img" => $user_img, "create_time" => time()];
    }
}
