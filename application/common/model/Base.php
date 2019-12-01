<?php
/**
 * Description: 类文件基类.
 * Author: momo
 * Date: 2019-06-08
 * Copyright: momo
 */

namespace app\common\model;

use think\Model;

class Base extends Model
{
    /**
     * @param 获取数据列表
     * @param array $where 查询条件
     * @param int $paginate 分页类型0不分页；1带有前台自动分页的；2普通分页
     * @param int $page
     * @return mixed
     */
    public function dataList($className,$where=[],$paginate=0,$page=1,$order="ID desc")
    {
        $data = $className::where('is_logic_del', 0)
            ->where($where)
            ->order($order);
        if ($paginate == 1) {
            $data = $data->paginate(10);
        } else if ($paginate == 2) {
            $data = $data->page($page, 10)
                ->select()->toArray();
        } else {
            $data = $data->select()->toArray();
        }
        return $data;
    }

    //查询数据id集合
    public function dataIDs($className,$fieldName,$where=[])
    {
        $data = $className::where('is_logic_del', 0)
            ->where($where)
            ->column($fieldName);
        return $data;
    }

    //数据总数
    public function dataCount($className,$where=[])
    {
        $data = $className::where('is_logic_del', 0)
            ->where($where)
            ->count();
        return $data;
    }

    //数据和
    public function dataSum($className,$fieldName,$where=[])
    {
        $data = $className::where('is_logic_del', 0)
            ->where($where)
            ->sum($fieldName);
        return $data;
    }

    //详情数据
    public function oneDetail($className,$where)
    {
        $data = $className::where('is_logic_del',0)
            ->where($where)
            ->find();
        return $data;
    }

    //更新数据
    public function updateOne($className,$data,$where)
    {
        $data['update_time'] = time();
        $className::where($where)
            ->update($data);
    }

    //新增数据
    public function addOne($className,$data)
    {
        $data['create_time']=time();
        $data['update_time']=time();
        $id = $className::where('1=1')
            ->insertGetId($data);
        return $id;
    }

    //删除数据
    public function deleteOne($className,$where)
    {
        $className::where($where)
            ->delete();
    }

    //数据自增
    public function dataInc($className,$where,$fieldName,$num=1)
    {
        $className::where($where)
            ->setInc($fieldName, $num);
    }
    //数据自减
    public function dataDec($className,$where,$fieldName,$num=1)
    {
        $className::where($where)
            ->setDec($fieldName, $num);
    }

    //随机昵称和头像
    public function randData()
    {
        $arr = [
            ["nick_name" => "路飞", "user_img" => "lufei.png"],
            ["nick_name" => "索隆", "user_img" => "suolong.png"],
            ["nick_name" => "娜美", "user_img" => "namei.png"],
            ["nick_name" => "山治", "user_img" => "shanzhi.png"],
            ["nick_name" => "乌索普", "user_img" => "wusuopu.png"],
            ["nick_name" => "乔巴", "user_img" => "qiaoba.png"],
            ["nick_name" => "弗兰克", "user_img" => "fulanke.png"],
            ["nick_name" => "罗宾", "user_img" => "luobin.png"],
            ["nick_name" => "布鲁克", "user_img" => "buluke.png"],
            ["nick_name" => "甚平", "user_img" => "shenping.png"],
            ["nick_name" => "女帝", "user_img" => "nvdi.png"],
            ["nick_name" => "陈近南", "user_img" => "chenjinnan.png"],
            ["nick_name" => "韦小宝", "user_img" => "weixiaobao.png"],
            ["nick_name" => "令狐冲", "user_img" => "linghuchong.png"],
            ["nick_name" => "杨过", "user_img" => "yangguo.png"],
            ["nick_name" => "张无忌", "user_img" => "zhangwuji.png"],
            ["nick_name" => "张三丰", "user_img" => "zhangsanfeng.png"],
            ["nick_name" => "郭靖", "user_img" => "guojing.png"],
            ["nick_name" => "周伯通", "user_img" => "zhoubotong.png"],
            ["nick_name" => "黄药师", "user_img" => "huangyaoshi.png"],
            ["nick_name" => "洪七公", "user_img" => "hongqigong.png"],
            ["nick_name" => "段誉", "user_img" => "duanyu.png"],
            ["nick_name" => "虚竹", "user_img" => "xuzhu.png"],
            ["nick_name" => "乔峰", "user_img" => "qiaofeng.png"],
            ["nick_name" => "达摩祖师", "user_img" => "damozushi.png"],
            ["nick_name" => "一灯大师", "user_img" => "yideng.png"],
            ["nick_name" => "扫地僧", "user_img" => "saodiseng.png"],
            ["nick_name" => "陈家洛", "user_img" => "cehnjialuo.png"],
            ["nick_name" => "袁承志", "user_img" => "yuanchengzhi.png"],
            ["nick_name" => "胡斐", "user_img" => "hufei.png"],
            ["nick_name" => "苗人凤", "user_img" => "miaorenfeng.png"],
            ["nick_name" => "石破天", "user_img" => "shipotian.png"],
            ["nick_name" => "狄云", "user_img" => "diyun.png"],
            ["nick_name" => "胡一刀", "user_img" => "huyidao.png"],
            ["nick_name" => "萧十一郎", "user_img" => "xiaoshiyilang.png"],
        ];
        $num = mt_rand(0, count($arr) - 1);
        return $arr[$num];
    }
}
