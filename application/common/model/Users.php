<?php
/**
 * Description: 用户类文件.
 * Author: momo
 * Date: 2019-09-12
 * Copyright: momo
 */

namespace app\common\model;


class Users extends Base
{
    public function __construct($data = ['id'=>0,'user_name'=>'','user_mail'=>'','user_source'=>0])
    {
        parent::__construct($data);
    }
}
