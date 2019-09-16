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
    //获取数据列表
    public function dataList($where=[],$paginate=0)
    {
        $tablename = strtolower(self::getName());
        $data = db($tablename)
            ->where($where);
        if ($paginate) {
            $data = $data->paginate(15);
        } else {
            $data = $data->select();
        }
        return $data;
    }

    //详情数据
    public function oneDetail($where)
    {
        $tablename = strtolower(self::getName());
        $data = db($tablename)
            ->where($where)
            ->find();
        return $data;
    }

    //更新数据
    public function updateOne($data,$where)
    {
        $tablename = strtolower(self::getName());
        db($tablename)
            ->where($where)
            ->update($data);
    }

    //新增数据
    public function addOne($data)
    {
        $tablename = strtolower(self::getName());
        $id = db($tablename)
            ->insertGetId($data);
        return $id;
    }

    //删除数据
    public function deleteOne($where)
    {
        $tablename = strtolower(self::getName());
        db($tablename)
            ->where($where)
            ->delete();
    }
}
