<?php
/**
 * Description: 后台管理.
 * Author: momo
 * Date: 2019-06-08
 * Copyright: momo
 */

namespace app\admin\controller;
use app\api\common\Utils;


class Admin extends Base
{

    //首页数据
    public function index()
    {
        return $this->fetch();
    }
}
