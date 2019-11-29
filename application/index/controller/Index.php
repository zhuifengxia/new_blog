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

    public function artDetails()
    {

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
