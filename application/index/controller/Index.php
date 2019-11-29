<?php
/**
 * Description: 前台首页.
 * Author: momo
 * Date: 2019-09-19
 * Copyright: momo
 */
namespace app\index\controller;

use app\common\model\Articles;
use app\common\model\Gossips;

class Index extends Base
{
    public function index($typeid=0)
    {
        $artModel=new Articles();
        //获取博客配置信息
        $config = db('config')
            ->find();
        $this->assign('config', $config);
        //获取网站访问量
        $webcount=db('records')->count();
        $this->assign('traffic_num', $webcount);
        //获取文章总数
        $artcount=db('articles')->count();
        $this->assign('art_num', $artcount);
        $this->assign('page_num', 1);
        //获取分类列表数据
        $types=db('articletype')
            ->select();
        $this->assign('types', $types);
        $this->assign('typeid', $typeid?:0);
        $where[]=['is_logic_del','=',0];
        if($typeid)
        {
            $where[]=['type_id','=',$typeid];
        }
        $artlist=$artModel->artList($where);
        $this->assign('artlist', $artlist);
        return $this->fetch('index');
    }

    //文章下一页
    public function artNext()
    {
        $page = input("page", 1);
        $typeid = input("typeid", 0);
        $where[] = ['is_logic_del', '=', 0];
        $artModel = new Articles();
        if ($typeid) {
            $where[] = ['type_id', '=', $typeid];
        }
        $artlist = $artModel->artList($where, $page);
        return json($artlist);
    }

    /**
     * 详情数据
     * @param $id
     * @return mixed
     */
    public function artDetails($id)
    {
        $artModel = new Articles();
        $artdata = $artModel->artDetail($id);
        $this->assign("artdata", $artdata["data"]);
        $this->assign("postdata", $artdata["post_data"]);
        $this->assign("artids", ["next_id" => $artdata["next_id"], "per_id" => $artdata["per_id"]]);
        $this->assign('page_num', 1);
        return $this->fetch();
    }

    /**
     * 文章评论列表
     */
    public function artPost()
    {
        $artid = input("artid", 0);
        $page = input("page", 1);
        $artModel = new Articles();
        $data = $artModel->posting($artid, $page);
        return json($data);
    }

    /**
     * 提交评论
     */
    public function artPostSubmit()
    {
        $artid = input("artid", 0);
        $postmsg = input("postmsg", "");
        $artModel = new Articles();
        $data = $artModel->postingSubmit($artid, $postmsg);
        return json(["status" => 0, "data" => $data]);
    }

    /**
     * 微语列表数据
     */
    public function whisper()
    {
        $gossipsModel = new Gossips();
        $data = $gossipsModel->gossipList();
        $this->assign('page_num', 2);
        $this->assign('data', $data);
        return $this->fetch();
    }

    //微语下一页数据
    public function whisperNext()
    {
        $page = input("page", 1);
        $gossipsModel = new Gossips();
        $data = $gossipsModel->gossipList($page);
        return json($data);
    }
}
