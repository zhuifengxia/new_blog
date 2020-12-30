<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//题目类型
if(!function_exists("topicType")) {
    function topicType($typeid)
    {
        $msg = "单选题";
        switch ($typeid) {
            case 1:
                $msg = "多选题";
                break;
            case 2:
                $msg = "填空题";
                break;
            case 3:
                $msg = "问答题";
                break;
        }
        return $msg;
    }
}

//题目类型
if(!function_exists("optionMsg")) {
    function optionMsg($optionid)
    {
        $msg = "A";
        switch ($optionid) {
            case 1:
                $msg = "A";
                break;
            case 2:
                $msg = "B";
                break;
            case 3:
                $msg = "C";
                break;
            case 4:
                $msg = "D";
                break;
            case 4:
                $msg = "E";
                break;
        }
        return $msg;
    }
}

//接口统一回复
if (!function_exists('respondApi')) {
    function respondApi($data=[],$status='',$msg='')
    {
        $status = $status ?: \MoCommon\Support\Codes::ACTION_SUC;
        $msg = $msg ?: \MoCommon\Support\Codes::get(\MoCommon\Support\Codes::ACTION_SUC);
        $arr = [
            'statusCode' => $status,
            'msg' => $msg,
            'data' => $data
        ];

        return json($arr);
    }
}