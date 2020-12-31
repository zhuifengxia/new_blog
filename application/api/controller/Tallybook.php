<?php
/**
 * Description: 小程序接口相关.
 * Author: momo
 * Date: 2019/11/2
 * Copyright: momo
 */

namespace app\api\controller;


use EasyWeChat\Factory;
use MoCommon\Support\Helper;
use think\Controller;
use app\common\model\ExamBase;
use MoCommon\Support\Codes;
use think\facade\Cache;

class Tallybook extends Controller
{
    private $dbconfig="db_tally";
    public function index()
    {
        $page = input("page", 1);
        $typeid = input("typeid", 0);
        $userid = $this->getUid();
        //获取当前月份的账单明细
        $baseModel = new ExamBase();
        $data = $baseModel->dataList($this->dbconfig, "details", [], 1, $page, "record_date desc");
        $types = [];
        if (empty($typeid)) {
            $income_types = $baseModel->dataList($this->dbconfig, "type", ["type_type" => 0]);
            $pay_types = $baseModel->dataList($this->dbconfig, "type", ["type_type" => 1]);
            $types = ["income_type" => $income_types, "pay_type" => $pay_types];
        }
        $res = [
            "details" => $data,
            "types" => $types
        ];
        return respondApi($res);
    }

    /**
     * 微信登录
     */
    public function wxLogin()
    {
        $code = input('code');
        $config = [
            'app_id' => 'wxd9ec25fd88069a76',
            'secret' => '79f25933063eb0e032b6de334a15d1e1',
            'token' => '',
            'aes_key' => '',
        ];
        $app = Factory::miniProgram($config);
        $session = $app->auth->session($code);
        $return_data = [];
        $status = Codes::ACTION_FAL;
        if (!isset($session['session_key'])) {
            $message = '小程序session_key获取错误';
        } else {
            $baseModel = new ExamBase();
            $member = $baseModel->oneDetail($this->dbconfig, "members", ["user_openid" => $session['openid']]);
            if (empty($member)) {
                //没有账号
                $insertdata = [
                    "user_phone" => "",
                    "nick_name" => "",
                    "user_openid" => $session['openid']
                ];
                $userid = $baseModel->addOne($this->dbconfig, "members", $insertdata);
                $member = $baseModel->oneDetail($this->dbconfig, "members", ["id" => $userid]);
            }

            if ($member) {
                if (empty($member["user_phone"])) {
                    $member["user_phone"] = "";
                }
                $member['session_key'] = $session['session_key'];
                // 给用户生成token
                $sign = Helper::get_token($member['id']);
                //存入redis
                $options = config('app.converse');
                $redis = Cache::init($options);
                $redis->set($sign, json_encode($member), $options['expire']);
                $member["token"] = $sign;
                $return_data['user_info'] = $member;
                $status = Codes::ACTION_SUC;
                $message = Codes::get(Codes::ACTION_SUC);
            }
        }
        return respondApi($return_data, $status, $message);
    }
    /**
     * 获取用户id
     * @return int 用户id
     */
    protected function getUid($datatype=0)
    {
        $options = config('app.converse');
        $redis = Cache::init($options);
        $userid = 0;
        if (($sign = input('token')) && ($json_info = $redis->get($sign))) {

            $userid = json_decode($json_info, true)['id'];

        }
        if (($sign = cookie("token")) && ($json_info = $redis->get($sign))) {
            $userid = json_decode($json_info, true)['id'];
        }

        if (empty($userid)) {
            if ($datatype) {
                return 0;
            } else {
                //用户未登录
                $result = array(
                    'status' => Codes::NO_SIGNIN,
                    'msg' => Codes::get(Codes::NO_SIGNIN),
                    'data' => []
                );
                echo json_encode($result);
                exit;
            }

        } else {
            return $userid;
        }
    }
}