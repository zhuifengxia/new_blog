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


//转换为日期
if(!function_exists("transDate")) {
    function transDate($time)
    {
        $time = strtotime($time);
        $nowdate = strtotime(date("Y-m-d"));
        if ($time == $nowdate) {
            return "今天";
        } else if ($time == ($nowdate - 86400)) {
            return "昨天";
        } else if ($time < $nowdate && $time >= ($nowdate - 86400 * 6)) {
            $weekarray = ["星期日", "星期一", "星期二", "星期三", "星期四", "星期五", "星期六"];
            $xingqi = date("w", $time);
            return $weekarray[$xingqi];
        } else {
            return "";
        }

    }
}


//获取最近一年月份数据
if(!function_exists("monthData")) {
    function monthData()
    {
        //获取当前年份
        $year = date("Y");
        $now_month = date("m");
        $pre_year = $year - 1;
        $month = [];
        for ($i = 6; $i <= 12; $i++) {
            $key = $pre_year . "-" . $i;
            $msg = $pre_year . "年" . $i . "月";
            $val = $i . "月";
            if ($i < 10) {
                $key = $pre_year . "-0" . $i;
                $msg = $pre_year . "年0" . $i . "月";
            }
            $item = ["key" => $key, "value" => $val, "msg" => $msg];
            $month[] = $item;
        }
        $data = ["year" => $pre_year, "month" => $month];
        $date_scope[] = $data;

        $month = [];
        for ($i = 1; $i <= $now_month; $i++) {
            $key = $year . "-" . $i;
            $msg = $year . "年" . $i . "月";
            $val = $i . "月";
            if ($i < 10) {
                $key = $year . "-0" . $i;
                $msg = $year . "年0" . $i . "月";
            }
            $item = ["key" => $key, "value" => $val, "msg" => $msg];
            $month[] = $item;
        }
        $data = ["year" => $year, "month" => $month];
        $date_scope[] = $data;
        return $date_scope;
    }
}