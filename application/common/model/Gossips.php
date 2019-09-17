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
}
