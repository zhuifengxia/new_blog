<?php
/**
 * Description: 小程序接口相关.
 * Author: momo
 * Date: 2019/11/2
 * Copyright: momo
 */

namespace app\api\controller;

use EasyWeChat\Factory;
use MoCommon\Support\Helper;
use think\Controller;
use think\facade\Cache;
use think\facade\Log;

class Index extends Controller
{
    public function index()
    {
        $page = input('page', 1);
        $banner=[];
        $wish=[];
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
            //获取文章评论总数
            $article[$i]['comment_num']=db("posting")
                ->where("is_logic_del",0)
                ->where("data_id",$article[$i]["id"])
                ->count();
            //获取文章点赞总数
            $article[$i]['like_num']=db("likes")
                ->where("is_logic_del",0)
                ->where("data_id",$article[$i]["id"])
                ->count();
        }
        return json(['banner' => $banner, 'wish' => $wish, 'artlist' => $article, 'page' => $page + 1]);
    }

    //登录
    public function login()
    {
        Log::error("ldf:".json_encode($_POST));
        $code = input('code');
        $config = config('wechat.mini_program.default');
        $app = Factory::miniProgram($config);
        $session = $app->auth->session($code);
        $return_data = [];
        $status = -1;
        if (!isset($session['session_key'])) {
            $message = '小程序session_key获取错误';
        } else {
            //将openid保存到数据库中
            $member = db("users")->where(["open_id" => $session['openid']])
                ->find();
            $data = [
                'nick_name' => '',
                'head_url' => '',
                'open_id' => $session['openid'],
                'create_time' => time()
            ];
            if (empty($member['open_id'])) {
                //没有此用户新增
                $uid = db("users")->insertGetId($data);
                $member = db("users")->where(["user_id" => $uid])
                    ->find();
            }

            $member['session_key'] = $session['session_key'];
            // 给用户生成token
            $sign = Helper::gentoken($member['id']);

            //存入redis
            $options = config('app.converse');
            $redis = Cache::init($options);
            $redis->set($sign, json_encode($member), $options['expire']);
            $return_data['token'] = $sign;
            $return_data['user_info'] = $member;
            $status = 0;
            $message = "success";
        }
        return json(["data" => $return_data, "status" => $status, "msg" => $message]);
    }
}