<?php
/**
 * Description: 古诗词类文件.
 * Author: momo
 * Date: 2019-09-12
 * Copyright: momo
 */

namespace app\common\model;


class Poetry extends Base
{
    public function __construct($data = ['id'=>0,'poetry_age'=>'','poetry_title'=>'','poetry_author'=>'','poetry_content'=>'','is_logic_del'=>0])
    {
        parent::__construct($data);
    }
}
