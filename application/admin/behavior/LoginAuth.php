<?php
/**
 * Description: 是否登录验证.
 * Author: momo
 * Date: 2019-06-08
 * Copyright: momo
 */
namespace app\admin\behavior;


class LoginAuth
{
    use \traits\controller\Jump;
    public function run(){
       if(!session('admin_user')){
           return $this->error('请登录！','/admin/auth');
       }
    }
}
