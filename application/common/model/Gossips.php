<?php
/**
 * Description: 微语类文件.
 * Author: momo
 * Date: 2019-09-12
 * Copyright: momo
 */

namespace app\common\model;


class Gossips extends Base
{
    public function __construct($data = ['id'=>0,'data_msg'=>'','data_img'=>'','create_time'=>0,'update_time'=>0,'is_logic_del'=>0])
    {
        parent::__construct($data);
    }

    public function gossipList()
    {
        $data=db('gossips')
            ->order('id desc')
            ->select();
        for ($i=0;$i<count($data);$i++){
            $data[$i]['publish_date']=date('Y/m/d',$data[$i]['create_time']);
            $data[$i]['publish_time']=date('H:i',$data[$i]['create_time']);
        }
        return $data;
    }
}
