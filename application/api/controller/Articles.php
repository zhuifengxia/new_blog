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
     * 首页数据
     */
    public function index()
    {

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
            ->where("data_type", 0)
            ->order('id desc')
            ->column();
        return json(["status" => 0, "msg" => "success", "data" => $wish]);
    }

}
