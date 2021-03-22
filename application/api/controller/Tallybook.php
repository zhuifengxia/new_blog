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
        $date = input("date", "");
        $date = $date ?: date("Y-m");
        $userid = $this->getUid();
        //获取当前月份的账单明细
        $baseModel = new ExamBase();
        $where = "record_date like '$date%' and is_logic_del=0";

        if ($typeid) {
            $where .= " and type_id=$typeid";
        }
        $paywhere = " and money_type=1";
        $incomewhere = " and money_type=0";
        //获取总支出
        $pay_count = $baseModel->dataSum($this->dbconfig, "details", "money_num", $where . $paywhere);
        //获取总收入
        $incom_count = $baseModel->dataSum($this->dbconfig, "details", "money_num", $where . $incomewhere);
        $dateList = db("details", $this->dbconfig)
            ->field("record_date")
            ->where($where)
            ->order("record_date desc")
            ->distinct("record_date")
            ->select();
        $result = [];
        foreach ($dateList as $item) {
            $where = "record_date='{$item["record_date"]}'";
            if ($typeid) {
                $where .= " and type_id=$typeid";
            }
            //获得当天的支出
            $pay = $baseModel->dataSum($this->dbconfig, "details", "money_num", $where . $paywhere);
            //获得当天收入
            $income = $baseModel->dataSum($this->dbconfig, "details", "money_num", $where . $incomewhere);
            $data = $baseModel->dataList($this->dbconfig, "details", $where, 0, $page, "create_time desc");
            for ($i = 0; $i < count($data); $i++) {
                $data[$i]["time"] = date("H:i", $data[$i]["create_time"]);
                $data[$i]["type_icon"] = $baseModel->dataValue($this->dbconfig, "type", "type_icon", "id={$data[$i]["type_id"]}");
            }
            $one = [
                "date" => date("m月d日", strtotime($item["record_date"])),
                "date_msg" => transDate($item["record_date"]),
                "pay_count" => number_format($pay, 2),
                "income_count" => number_format($income, 2),
                "details" => $data
            ];
            $result[] = $one;
        }
        $res = [
            "details" => $result ?: null,
            "income_count" => $incom_count,
            "pay_count" => $pay_count,
        ];
        return respondApi($res);
    }

    public function typeList()
    {
        $baseModel = new ExamBase();
        $income_types = $baseModel->dataList($this->dbconfig, "type", ["type_type" => 0], "0", 1, "type_sort asc");
        $pay_types = $baseModel->dataList($this->dbconfig, "type", ["type_type" => 1], "0", 1, "type_sort asc");
        $types = ["income_type" => $income_types, "pay_type" => $pay_types];
        $date_scope = monthData();
        $res = [
            "types" => $types,
            "date_scope" => $date_scope
        ];
        return respondApi($res);
    }

    /**
     * 记账
     */
    public function createData()
    {
        $userid = $this->getUid(0);
        $id = input("id", 0);
        $typeid = input("typeid", 0);
        $number = input("number", 0);
        $remark = input("remark", "");
        $date = input("date", "");
        if ($number) {
            $baseModel = new ExamBase();
            //获取类型的收支类型
            $money_type = $baseModel->dataValue($this->dbconfig, "type", "type_type", "id=$typeid");
            $type_name = $baseModel->dataValue($this->dbconfig, "type", "type_name", "id=$typeid");
            $insert = [
                "type_id" => $typeid,
                "money_num" => $number,
                "account_id" => 1,
                "record_date" => $date,
                "data_remark" => $remark,
                "money_type" => $money_type,
                "type_name" => $type_name
            ];
            if ($id) {
                //编辑
                $baseModel->updateOne($this->dbconfig, "details", $insert, "id=$id");
            } else {
                $insert["user_id"] = $userid;
                $baseModel->addOne($this->dbconfig, "details", $insert);
            }
        }
        return respondApi();
    }

    /**
     * 统计页面数据
     */
    public function statisticData()
    {
        $date = input("date", "");
        $date = $date ?: date("Y-m");
        $userid = $this->getUid();
        //获取当月总支出和总收入
        $baseModel = new ExamBase();
        $where = "record_date like '$date%' and is_logic_del=0";
        $paywhere = " and money_type=1";
        $incomewhere = " and money_type=0";
        //获取总支出
        $pay_count = $baseModel->dataSum($this->dbconfig, "details", "money_num", $where . $paywhere);
        //获取总收入
        $incom_count = $baseModel->dataSum($this->dbconfig, "details", "money_num", $where . $incomewhere);
        //获取当月每个类型的总支出和总收入
        $pay_data = db("details", $this->dbconfig)
            ->field("sum(money_num) as money_num,type_id,type_name,money_type")
            ->where($where . $paywhere)
            ->group("type_id")
            ->order("money_num desc")
            ->select();
        for ($i = 0; $i < count($pay_data); $i++) {
            $percent = round($pay_data[$i]["money_num"] / $pay_count, 2);
            $pay_data[$i]["percent"] = $percent * 100 . "";
            $pay_data[$i]["type_icon"] = $baseModel->dataValue($this->dbconfig, "type", "type_icon", "id={$pay_data[$i]["type_id"]}");
        }
        $income_data = db("details", $this->dbconfig)
            ->field("sum(money_num) as money_num,type_id,type_name,money_type")
            ->where($where . $incomewhere)
            ->group("type_id")
            ->order("money_num desc")
            ->select();
        for ($i = 0; $i < count($income_data); $i++) {
            $percent = round($income_data[$i]["money_num"] / $incom_count, 2);
            $income_data[$i]["percent"] = $percent * 100 . "";
            $income_data[$i]["type_icon"] = $baseModel->dataValue($this->dbconfig, "type", "type_icon", "id={$income_data[$i]["type_id"]}");
        }
        //当月支出top5
        $top_pay = $baseModel->dataList($this->dbconfig, "details", $where . $paywhere, 1, 1, "money_num desc", 5);
        for ($i = 0; $i < count($top_pay); $i++) {
            $top_pay[$i]["time"] = date("m月d日", strtotime($top_pay[$i]["record_date"])) . " " . date("H:i", $top_pay[$i]["create_time"]);
            $top_pay[$i]["type_icon"] = $baseModel->dataValue($this->dbconfig, "type", "type_icon", "id={$top_pay[$i]["type_id"]}");
        }
        $date_scope = monthData();
        $return = [
            "pay_data" => $pay_data ?: null,
            "income_data" => $income_data ?: null,
            "top_pay" => $top_pay ?: null,
            "pay_count" => $pay_count ?: "0.00",
            "incom_count" => $incom_count ?: "0.00",
            "date_scope" => $date_scope
        ];
        return respondApi($return);
    }

    //统计页面点击分类查看数据
    public function typeData()
    {
        $typeid = input("typeid", "");
        $datatype = input("datatype", 0);//收入；1支出数据
        $showtype = input("showtype", 0);//0按金额排序；1支时间排序
        $date = input("date", "");
        $date = $date ?: date("Y-m");
        $userid = $this->getUid();
        //获取当月总支出和总收入
        $baseModel = new ExamBase();
        $where = "record_date like '$date%' and type_id=$typeid and is_logic_del=0";
        $type_name = $baseModel->dataValue($this->dbconfig, "type", "type_name", "id=$typeid");
        if ($datatype) {
            $addwhere = " and money_type=1";
        } else {
            $addwhere = " and money_type=0";
        }
        $ordertype = "money_num desc";
        if ($showtype) {
            $ordertype = "record_date desc";
        }
        //获取总收入/总支出
        $sum_data = $baseModel->dataSum($this->dbconfig, "details", "money_num", $where . $addwhere);
        //获取明细
        $detail_data = $baseModel->dataList($this->dbconfig, "details", $where . $addwhere, 0, 1, $ordertype);
        for ($i = 0; $i < count($detail_data); $i++) {
            $detail_data[$i]["time"] = date("m月d日", strtotime($detail_data[$i]["record_date"])) . " " . date("H:i", $detail_data[$i]["create_time"]);
            $detail_data[$i]["type_icon"] = $baseModel->dataValue($this->dbconfig, "type", "type_icon", "id={$detail_data[$i]["type_id"]}");
        }
        $date_msg = explode("-", $date);
        $result = [
            "sum_data" => $sum_data,
            "detail_data" => $detail_data,
            "title_data" => trim($date_msg[1], "0") . "月" . $type_name . "共" . ($datatype ? "支出" : "收入")
        ];
        return respondApi($result);
    }

    /**
     * 详情信息
     */
    public function tallyDetail()
    {
        $id = input("id", 0);
        $userid = $this->getUid();
        $baseModel = new ExamBase();
        $data = $baseModel->oneDetail($this->dbconfig, "details", "id=$id");
        $data["time"] = date("H:i", $data["create_time"]);
        $data["type_icon"] = $baseModel->dataValue($this->dbconfig, "type", "type_icon", "id={$data["type_id"]}");
        return respondApi($data);
    }

    /**
     * 微信登录
     */
    public function wxLogin()
    {
        $code = input('code');
        $config = config('wechat.mini_program.tally');
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
                //获取账户总余额
                //收入-支出
                //获得所有支出
                $pay = $baseModel->dataSum($this->dbconfig, "details", "money_num", "money_type=1");
                //获得所有收入
                $income = $baseModel->dataSum($this->dbconfig, "details", "money_num", "money_type=0");
                $income_count = $income - $pay;
                $member["income_count"] = $income_count;
                //获取记录笔数（只计算当前登录人的数据）
                $create_count = $baseModel->dataCount($this->dbconfig, "details", "user_id={$member['id']}");
                $member["create_count"] = $create_count;
                //查看当前用户记录多少天了，获得第一笔的时间
                $first_date = $baseModel->dataValue($this->dbconfig, "details", "create_time", "user_id={$member['id']}", "id asc");
                if ($first_date) {
                    $member["create_day"] = ceil((strtotime(date("Y-m-d")) - $first_date) / 86400);
                } else {
                    $member["create_day"] = 0;
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
     * 获取所有数据
     */
    public function getAllData()
    {
        $userid = $this->getUid(0);
        $baseModel = new ExamBase();
        //获取账户总余额
        //收入-支出
        //获得所有支出
        $pay = $baseModel->dataSum($this->dbconfig, "details", "money_num", "money_type=1");
        //获得所有收入
        $income = $baseModel->dataSum($this->dbconfig, "details", "money_num", "money_type=0");
        $income_count = $income - $pay;
        $result["income_count"] = $income_count;
        //获取记录笔数（只计算当前登录人的数据）
        $create_count = $baseModel->dataCount($this->dbconfig, "details", "user_id=$userid");
        $result["create_count"] = $create_count;
        //查看当前用户记录多少天了，获得第一笔的时间
        $first_date = $baseModel->dataValue($this->dbconfig, "details", "create_time", "user_id=$userid", "id asc");
        if ($first_date) {
            $result["create_day"] = ceil((strtotime(date("Y-m-d")) - $first_date) / 86400);
        } else {
            $result["create_day"] = 0;
        }
        return respondApi($result);
    }

    /**
     * 房贷信息自动录入；
     */
    public function insertData()
    {
        $nowmonth=date("Y-m");
        $baseModel = new ExamBase();
        $isdata = $baseModel->oneDetail($this->dbconfig, "details", "type_id=20 and record_date like '$nowmonth%'");
        if (empty($isdata)) {
            $insert = [
                "type_id" => 20,
                "money_num" => 2411.36,
                "account_id" => 1,
                "record_date" => date("Y-m-d"),
                "data_remark" => "",
                "money_type" => 1,
                "type_name" => "房贷"
            ];
            $baseModel->addOne($this->dbconfig, "details", $insert);
        }

        return respondApi();
    }

    /**
     * 删除数据
     */
    public function deleteData()
    {
        $id = input("id", 0);
        $userid = $this->getUid(0);
        $baseModel = new ExamBase();
        $baseModel->updateOne($this->dbconfig, "details", ["is_logic_del" => 1], "id=$id");
        return respondApi();
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