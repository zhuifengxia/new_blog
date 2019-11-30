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
            ["nick_name" => "路飞", "user_img" => "default.png"],
            ["nick_name" => "索隆", "user_img" => "default.png"],
            ["nick_name" => "娜美", "user_img" => "default.png"],
            ["nick_name" => "山治", "user_img" => "default.png"],
            ["nick_name" => "乌索普", "user_img" => "default.png"],
            ["nick_name" => "乔巴", "user_img" => "default.png"],
            ["nick_name" => "弗兰克", "user_img" => "default.png"],
            ["nick_name" => "罗宾", "user_img" => "default.png"],
            ["nick_name" => "布鲁克", "user_img" => "default.png"],
            ["nick_name" => "甚平", "user_img" => "default.png"],
        ];
        $num = mt_rand(0, count($arr) - 1);
        return $arr[$num];
    }
}
