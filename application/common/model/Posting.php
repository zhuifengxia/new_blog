<?php
/**
 * Description: 评论类文件.
 * Author: momo
 * Date: 2019-09-12
 * Copyright: momo
 */

namespace app\common\model;


class Posting extends Base
{
    public function __construct($data = [])
    {
        parent::__construct($data);
    }

    //评论数据信息
    public function postList($id=0,$page=1)
    {
        $postnum=0;
        if ($page == 1) {
            //获取前十条文章评论信息
            $postnum = db("posting")
                ->where("data_id", $id)
                ->where("is_logic_del", 0)
                ->where("is_audit", 1)
                ->count();
        }
        $posting = db("posting")
            ->where("data_id", $id)
            ->where("is_logic_del", 0)
            ->where("is_audit", 1)
            ->order("id desc")
            ->page($page, 10)
            ->select();
        return ["post_num" => $postnum, "posting" => $posting];
    }
}
