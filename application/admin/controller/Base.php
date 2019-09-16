<?php
/**
 * Description: 后台控制器基类.
 * Author: momo
 * Date: 2019-06-08
 * Copyright: momo
 */

namespace app\admin\controller;

use think\Controller;
use think\facade\Hook;

class Base extends Controller
{
    public function initialize()
    {
        parent::initialize();
        //验证登陆
        Hook::listen('CheckAuth');
        #渲染面包屑
        $this->setCrumbs();
    }

    #获取当前url；type=0 自定义路由；type=1模块/控制器/方法
    protected function getUrlStr($type=0)
    {
        $urldata=request()->routeInfo();
        if($type){
            return ($urldata['route']);
        }else{
            return $urldata['rule'];
        }
    }

    const URL_ARR=[
        'admin/index'=>"首页",

    ];

    //面包屑
    public function setCrumbs()
    {
        $urlStr = $this->getUrlStr();
//        $this->assign(['action'=>self::URL_ARR[$urlStr]]);
    }



}
