<?php
/**
 * Description: 题库类文件基类.
 * Author: momo
 * Date: 2019-06-08
 * Copyright: momo
 */

namespace app\common\model;

use think\Db;
use think\Model;

class ExamBase extends Model
{

    /**
     * @param 获取数据列表
     * @param array $where 查询条件
     * @param int $paginate 分页类型0不分页；1普通分页;2带分页的
     * @param int $page
     * @return mixed
     */
    public function dataList($dbname,$tablename,$where=[],$paginate=0,$page=1,$order="id desc",$num=10)
    {
        $data = db($tablename, $dbname)
            ->where("is_logic_del", 0)
            ->where($where)
            ->order($order);
        if (empty($paginate)) {
            $data = $data->select();
        } else if ($paginate == 1) {
            $data = $data->page($page, $num)
                ->select();
        } else if ($paginate == 2) {
            $data = $data->paginate($num, false, array("query" => $_REQUEST));
        }
        Db::connect()->close();
        return $data;
    }

    //查询数据id集合
    public function dataIDs($dbname,$tablename,$fieldName,$where=[])
    {
        $data = db($tablename, $dbname)
            ->where("is_logic_del", 0)
            ->where($where)
            ->column($fieldName);
        return $data;
    }

    //数据总数
    public function dataCount($dbname,$tablename,$where=[])
    {
        $data = db($tablename, $dbname)
            ->where("is_logic_del", 0)
            ->where($where)
            ->count();
        return $data;
    }

    //数据和
    public function dataSum($dbname,$tablename,$fieldName,$where=[])
    {
        $data = db($tablename, $dbname)
            ->where("is_logic_del", 0)
            ->where($where)
            ->sum($fieldName);
        return $data;
    }

    //详情数据
    public function oneDetail($dbname,$tablename,$where,$isdel=0)
    {
        $data = db($tablename, $dbname)
            ->where($isdel ? "1=1" : "is_logic_del=0")
            ->where($where)
            ->order('id desc')
            ->find();
        return $data;
    }

    //获取某个数据值
    public function dataValue($dbname,$tablename,$fieldName,$where=[],$order="id desc")
    {
        $data = db($tablename, $dbname)
            ->where("is_logic_del", 0)
            ->field($fieldName)
            ->where($where)
            ->order($order)
            ->value($fieldName);
        return $data;
    }

    //更新数据
    public function updateOne($dbname,$tablename,$data,$where)
    {
        $data['update_time'] = time();
        db($tablename, $dbname)
            ->where($where)
            ->update($data);
    }

    //新增数据
    public function addOne($dbname,$tablename,$data)
    {
        $data['create_time'] = time();
        $data['update_time'] = time();
        $id = db($tablename, $dbname)
            ->where('1=1')
            ->insertGetId($data);
        return $id;
    }

    //删除数据
    public function deleteOne($dbname,$tablename,$where)
    {
        db($tablename, $dbname)
            ->where($where)
            ->delete();
    }

    //数据自增
    public function dataInc($dbname,$tablename,$where,$fieldName,$num=1)
    {
        db($tablename, $dbname)
            ->where($where)
            ->setInc($fieldName, $num);
    }
    //数据自减
    public function dataDec($dbname,$tablename,$where,$fieldName,$num=1)
    {
        db($tablename, $dbname)
            ->where($where)
            ->setDec($fieldName, $num);
    }
}
