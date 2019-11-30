<?php
/**
 * Description: 文章分类类文件.
 * Author: momo
 * Date: 2019-09-12
 * Copyright: momo
 */

namespace app\common\model;


class Articletype extends Base
{
    public function __construct($data = ['id'=>0,'type_name'=>'','fid'=>0,'is_show'=>0,'is_logic_del'=>0])
    {
        parent::__construct($data);
    }
}
