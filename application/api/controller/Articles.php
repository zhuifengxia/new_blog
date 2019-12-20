<?php
/**
 * Description: 小程序接口相关.
 * Author: momo
 * Date: 2019/11/2
 * Copyright: momo
 */

namespace app\api\controller;

use think\Controller;

class Articles extends Controller
{

    /**
     * 文章列表数据
     */
    public function articleData()
    {
        $typeid = input("typeid", 0);
        $page = input("page", 1);
        if ($typeid) {
            //获取十条文章信息
            $article = db('articles')
                ->field('id,article_title,article_digest,article_img,create_time,read_num')
                ->where('is_logic_del', 0)
                ->where('type_id', $typeid)
                ->order('id desc')
                ->page($page, 10)
                ->select();
            $total = db('articles')
                ->where('is_logic_del', 0)
                ->where('type_id', $typeid)
                ->count();
        } else {
            //获取十条文章信息
            $article = db('articles')
                ->field('id,article_title,article_img,create_time,read_num')
                ->where('is_logic_del', 0)
                ->order('id desc')
                ->page($page, 2)
                ->select();
            $total = db('articles')
                ->where('is_logic_del', 0)
                ->count();
        }
        for ($i = 0; $i < count($article); $i++) {
            $article[$i]['create_time'] = date('Y-m-d H:i', $article[$i]['create_time']);
            //获取文章评论总数
            $article[$i]['comment_num'] = db("posting")
                ->where("is_logic_del", 0)
                ->where("is_audit", 1)
                ->where("data_id", $article[$i]["id"])
                ->count();
            //获取文章点赞总数
            $article[$i]['like_num'] = db("likes")
                ->where("is_logic_del", 0)
                ->where("data_type", 0)
                ->where("data_id", $article[$i]["id"])
                ->count();
        }
        return json(['status' => 0, 'msg' => 'success', 'data' => $article, 'total' => $total]);
    }

    /**
     * 小程序banner数据
     */
    public function bannerData()
    {
        //获取banner数据
        $banner = db('articles')
            ->field('id,article_title,article_img')
            ->where('is_logic_del', 0)
            ->where('is_top', 1)
            ->select();
        for ($i = 0; $i < count($banner); $i++) {
            $banner[$i]["article_img"] = config("app.web_config.web_url") . $banner[$i]["article_img"];
        }
        return json(["status" => 0, "msg" => "success", "data" => $banner]);
    }

    /**
     * 小程序每日分享数据
     */
    public function whisperData()
    {
        //获取最新的十条微语
        $wish = db('gossips')
            ->field('data_msg')
            ->where("data_type", 2)
            ->order('id desc')
            ->column("data_msg");
        return json(["status" => 0, "msg" => "success", "data" => $wish]);
    }

    /**
     * 文章详情
     * @param $artid文章id
     */
    public function articleDetail($artid)
    {
        //增加阅读量
        db("articles")
            ->where("id", $artid)
            ->setInc("read_num");
        $article = db("articles")
            ->where("id", $artid)
            ->find();
        $article['create_time'] = date('Y-m-d H:i', $article['create_time']);
        //获取文章评论总数
        $article['comment_num'] = db("posting")
            ->where("is_logic_del", 0)
            ->where("is_audit", 1)
            ->where("data_id", $article["id"])
            ->count();
        //文章tag
        $article["article_tag"] = explode(",", $article["article_tag"]);
        $article["article_type"]=$article["article_type"]==1?'转载':'原创';
        //获取文章点赞总数
        $article['like_num'] = db("likes")
            ->where("is_logic_del", 0)
            ->where("data_type", 0)
            ->where("data_id", $article["id"])
            ->count();
        return json(["status" => 0, "msg" => "success", "data" => $article]);
    }

}
