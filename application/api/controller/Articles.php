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

class Articles extends Controller
{
    /**
     * 微信小程序登陆
     */
    public function login()
    {
        $code = input('code','');
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
                'user_name' => '',
                'user_img' => '',
                'user_gender' => '',
                'user_prov' => '',
                'user_city' => '',
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

    /**
     * 评论列表数据
     * @param $id 文章id
     */
    public function commentData($artid)
    {
        $page=input("page",1);
        //获取前十条评论数据
        $commentlst = db("posting")
            ->where("data_id", $artid)
            ->where("is_audit", 1)
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
        return json(["status"=>0,"msg"=>"success","data"=>$commentlst]);
    }

}
