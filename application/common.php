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