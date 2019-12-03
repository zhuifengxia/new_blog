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
            ->field('id,article_title,article_img,create_time,read_num')
            ->where('is_logic_del', 0)
            ->order('id desc')
            ->page($page, 10)
            ->select();
        for ($i = 0; $i < count($article); $i++) {
            $article[$i]['create_time'] = date('Y-m-d H:i', $article[$i]['create_time']);
            //获取文章评论总数
            $article[$i]['comment_num']=db("posting")
                ->where("is_logic_del",0)
                ->where("data_id",$article[$i]["id"])
                ->count();
            //获取文章点赞总数
            $article[$i]['like_num']=db("likes")
                ->where("is_logic_del",0)
                ->where("data_type",0)
                ->where("data_id",$article[$i]["id"])
                ->count();
        }
        return json(['banner' => $banner, 'wish' => $wish, 'artlist' => $article, 'page' => $page + 1]);
    }

    //登录
    public function login()
    {
        Log::error("ldf:" . json_encode($_POST));
        $username = input('username');
        $userimg = input('userimg');
        $usergender = input('usergender');
        $userpro = input('userpro');
        $usercity = input('usercity');
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
                'user_name' => $username,
                'user_img' => $userimg,
                'user_gender' => $usergender,
                'user_prov' => $userpro,
                'user_city' => $usercity,
                'user_source' => 1,
                'open_id' => $session['openid']
            ];
            if (empty($member['open_id'])) {
                $data["create_time"] = time();
                $data["update_time"] = time();
                //没有此用户新增
                $uid = db("users")->insertGetId($data);
            } else {
                $data["update_time"] = time();
                db("users")->where(["id" => $member['id']])
                    ->update($data);
                $uid = $member['id'];
            }
            $member = db("users")->where(["id" => $uid])
                ->find();
            $member['session_key'] = $session['session_key'];
            // 给用户生成token
            $sign = Helper::get_token($member['id']);

            //存入redis
            $options = config('app.converse');
            $redis = Cache::init($options);
            $redis->set($sign, json_encode($member), $options['expire']);
            $return_data['token'] = $sign;
            $return_data['userInfo'] = $member;
            $status = 0;
            $message = "success";
        }
        return json(["data" => $return_data, "status" => $status, "msg" => $message]);
    }

    //文章详情
    public function artDetail()
    {
        $id = input("artid");
        $page = input("page", 1);
        $userid = input("userid");
        $article = [];
        if ($page == 1) {
            $article = db("articles")
                ->where("id", $id)
                ->find();
            $article['create_time'] = date('Y-m-d H:i', $article['create_time']);
            //获取文章评论总数
            $article['comment_num'] = db("posting")
                ->where("is_logic_del", 0)
                ->where("data_id", $article["id"])
                ->count();
            //获取文章点赞总数
            $article['like_num'] = db("likes")
                ->where("is_logic_del", 0)
                ->where("data_type", 0)
                ->where("data_id", $article["id"])
                ->count();
            //当前用户是否点赞
            $islike = db("likes")
                ->where("is_logic_del", 0)
                ->where("data_type", 0)
                ->where("data_id", $article["id"])
                ->where("user_id", $userid)
                ->find();
            $article["is_like"] = $islike ? 1 : 0;
            //当前用户是否收藏
            $iscollect = db("likes")
                ->where("is_logic_del", 0)
                ->where("data_type", 1)
                ->where("data_id", $article["id"])
                ->where("user_id", $userid)
                ->find();
            $article["is_collect"] = $iscollect ? 1 : 0;
        }
        //获取前十条评论数据
        $commentlst = db("posting")
            ->where("data_id", $id)
            ->where("is_logic_del", 0)
            ->order("id desc")
            ->page($page, 10)
            ->select();
        for ($i = 0; $i < count($commentlst); $i++) {
            if (!strstr($commentlst[$i]['user_img'], 'http')) {
                $commentlst[$i]['user_img'] = config("app.web_config.web_url") . "/static/home/headimgs/" . $commentlst[$i]['user_img'];
            }
            $commentlst[$i]['create_time'] = date('Y-m-d H:i', $commentlst[$i]['create_time']);
        }
        return json(["artdetial" => $article, "commentlst" => $commentlst, "page" => $page]);
    }
}