<?php
/**
 * Description: 前台控制器基类.
 * Author: momo
 * Date: 2019-09-19
 * Copyright: momo
 */

namespace app\index\controller;

use MoCommon\Support\Helper;
use think\Controller;

class Base extends Controller
{
    public function initialize()
    {
        //访问记录
        //获取ip地址
        $celentip=Helper::getIp();
        $data=db('records')
            ->where('addr_ip',$celentip)
            ->find();
        if(empty($data)){
            //记录ip
            db('records')
                ->insert(['addr_ip'=>$celentip,'create_time'=>time()]);
        }
        parent::initialize();
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

}
