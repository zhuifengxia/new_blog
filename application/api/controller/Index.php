<?php
/**
 * Description: 小程序接口相关.
 * Author: momo
 * Date: 2019/11/2
 * Copyright: momo
 */

namespace app\api\controller;

use think\Controller;

class Index extends Controller
{
    public function index()
    {
        $page = input('page', 1);
        if ($page == 1) {
            //获取banner数据
            $banner = db('articles')
                ->field('id,article_title,article_img')
                ->where('is_logic_del', 0)
                ->where('is_top', 1)
                ->select();
            //获取最新的十条微语
            $wish = db('gossips')
                ->field('id,data_msg')
                ->where("data_type",0)
                ->order('id desc')
                ->limit(10)
                ->select();
        }

        //获取十条文章信息
        $article = db('articles')
            ->field('id,article_title,article_img,update_time,read_num')
            ->where('is_logic_del', 0)
            ->order('id desc')
            ->page($page, 10)
            ->select();
        for ($i = 0; $i < count($article); $i++) {
            $article[$i]['update_time'] = date('Y-m-d H:i', $article[$i]['update_time']);
        }
        return json(['banner' => $banner, 'wish' => $wish, 'artlist' => $article, 'page' => $page + 1]);
    }
}