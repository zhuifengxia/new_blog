<?php
/**
 * Description: 后台评论管理.
 * Author: momo
 * Date: 2019-06-08
 * Copyright: momo
 */

namespace app\admin\controller;

use app\common\model\Posting as PostModel;
use app\common\model\Articles;

class Posting extends Base
{
    //首页数据
    public function postList($typeid=0)
    {
        $postModel = new PostModel();
        if ($typeid) {
            //文章评论信息
            $where = "data_id!=0";
        } else {
            //网站评论信息
            $where = "data_id=0";
        }
        $post = $postModel->dataList(PostModel::class, $where, 1);
        if ($typeid) {
            for ($i = 0; $i < count($post); $i++) {
                //获取文章标题
                $art = $postModel->oneDetail(Articles::class, ["id" => $post[$i]["data_id"]]);
                $post[$i]["article_title"] = $art["article_title"];
            }
        }
        $this->assign("data", $post);
        $this->assign("typeid", $typeid);
        return $this->fetch();
    }

    //删除数据
    public function delPost($id)
    {
        $postModel = new PostModel();
        $postModel->deleteOne(PostModel::class,["id"=>$id]);
        $this->success('删除成功', '/admin/posting/list');
    }
}
