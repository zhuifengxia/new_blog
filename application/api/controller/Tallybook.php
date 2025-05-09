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
    private $dbconfig = "db_tally";

    public function index()
    {
        $page = input("page", 1);
        $typeid = input("typeid", 0);
        $onlydata = input("onlydata", 0);
        $date = input("date", "");
        $date = $date ?: date("Y-m");
        $userid = $this->getUid();
        //获取当前月份的账单明细
        $baseModel = new ExamBase();
        $where = "record_date like '$date%' and is_logic_del=0";

        if ($typeid) {
            $where .= " and type_id=$typeid";
        }
        if ($onlydata) {
            //只查询自己
            $where .= " and user_id=$userid";
        }

        $dataList=$baseModel->dataList($this->dbconfig,"details",$where,0,1,"record_date desc");
        $pay_count=0;//获取总支出
        $incom_count=0;//获取总收入
        $dateList=[];//日期分类集合
        $pay=0;//获得当天的支出
        $income=0;//获得当天收入
        foreach ($dataList as $item){
            $idDate=0;
            foreach ($dateList as $k=>$de){
                if($de["date"]==date("m月d日", strtotime($item["record_date"]))){
                    $idDate=1;
                    break;
                }
            }

            if(empty($idDate)){
                $dateList[]=[
                    "date" => date("m月d日", strtotime($item["record_date"])),
                    "date_msg" => transDate($item["record_date"]),
                    "details" => []
                ];
                $pay=0;
                $income=0;
            }
            if($item["money_type"]==1){
                $pay_count+=$item["money_num"];
                $pay+=$item["money_num"];
            }else{
                $incom_count+=$item["money_num"];
                $income+=$item["money_num"];
            }

            foreach ($dateList as $k=>$de){
                if($de["date"]==date("m月d日", strtotime($item["record_date"]))){
                    $dateList[$k]["pay_count"]=round($pay, 2);
                    $dateList[$k]["income_count"]=round($income, 2);
                    $item["time"] = date("H:i", $item["create_time"]);
                    $dateList[$k]["details"][]=$item;
                    break;
                }
            }

        }
        /*
        $dateList = db("details", $this->dbconfig)
            ->field("record_date")
            ->where($where)
            ->order("record_date desc")
            ->distinct("record_date")
            ->select();*/
        /*$result = [];
        foreach ($dateList as $item) {
            $where = "record_date='{$item}'";
            if ($typeid) {
                $where .= " and type_id=$typeid";
            }
            if ($onlydata) {
                //只查询自己
                $where .= " and user_id=$userid";
            }
            $dataList=$baseModel->dataList($this->dbconfig,"details",$where,0,1,"create_time desc");
            $pay=0;//获得当天的支出
            $income=0;//获得当天收入
            foreach ($dataList as $key=>$oneData){
                if($oneData["money_type"]==1){
                    $pay+=$oneData["money_num"];
                }else{
                    $income+=$oneData["money_num"];
                }
                $dataList[$key]["time"] = date("H:i", $dataList[$key]["create_time"]);
                //$dataList[$key]["type_icon"] = $baseModel->dataValue($this->dbconfig, "type", "type_icon", "id={$dataList[$key]["type_id"]}");
                $dataList[$key]["type_icon"] = "";
            }
            $one = [
                "date" => date("m月d日", strtotime($item)),
                "date_msg" => transDate($item),
                "pay_count" => number_format($pay, 2),
                "income_count" => number_format($income, 2),
                "details" => $dataList
            ];
            $result[] = $one;
        }*/
        $res = [
            "details" => $dateList ?: null,
            "income_count" => round($incom_count, 2),
            "pay_count" => round($pay_count, 2),
        ];
        return respondApi($res);
    }

    public function typeList()
    {
        $baseModel = new ExamBase();
        $income_types = $baseModel->dataList($this->dbconfig, "type", ["type_type" => 0], "0", 1, "type_sort asc");
        $pay_types = $baseModel->dataList($this->dbconfig, "type", ["type_type" => 1], "0", 1, "type_sort asc");
        $child_pay_types = $baseModel->dataList($this->dbconfig, "type", ["type_type" => 2], "0", 1, "type_sort asc");
        $types = ["income_type" => $income_types, "pay_type" => $pay_types, "child_pay_type" => $child_pay_types];
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
                "money_type" => ($money_type == 0 ? 0 : 1),
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
        //获取当前用户的总支出
        $user_pay_count = $baseModel->dataSum($this->dbconfig, "details", "money_num", $where . $paywhere . " and user_id=$userid");

        //获取总收入
        $incom_count = $baseModel->dataSum($this->dbconfig, "details", "money_num", $where . $incomewhere);
        //获取当前用户的总收入
        $user_incom_count = $baseModel->dataSum($this->dbconfig, "details", "money_num", $where . $incomewhere . " and user_id=$userid");
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
        //查看当前是否是元旦以及之后10天
        $now_month = date("m");
        $now_day = date("d");
        $is_show_yearbill = 0;
        if ($now_month == "01" && $now_day <= 10) {
            $is_show_yearbill = 1;//1.1~1.10可以查看年度账单
        }
        if ($userid == 1) {
            $is_show_yearbill = 1;
        }
        $return = [
            "pay_data" => $pay_data ?: null,
            "income_data" => $income_data ?: null,
            "top_pay" => $top_pay ?: null,
            "pay_count" => $pay_count ?: "0.00",
            "incom_count" => $incom_count ?: "0.00",
            "user_pay_count" => $user_pay_count ?: "0.00",
            "user_incom_count" => $user_incom_count ?: "0.00",
            "date_scope" => $date_scope,
            "is_show_yearbill" => $is_show_yearbill
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
        $typeData = $baseModel->oneDetail($this->dbconfig, "type", "id={$data["type_id"]}");
        $data["type_icon"] = $typeData["type_icon"];
        $data["money_type"] = $typeData["type_type"];
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
        $result["income_count"] = round($income_count, 2);
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
     * 年度统计
     */
    public function yearBill()
    {
        $date = input('year', "");
        if (empty($date)) {
            //每年元旦即可查看上一年度账单信息，
            $date = date("Y") - 1;
            //查看当前是否是元旦以及之后10天
            $now_month = date("m");
            $now_day = date("d");
            if ($now_month == "01" && $now_day <= 20) {
                //1.1~1.10查看年度账单去年
            } else {
                //当年
                $date = date("Y");
            }
        }


        $date_list = [];
        $dateNum = date("Y") - 2021;
        for ($i = 0; $i <= $dateNum; $i++) {
            $date_list[] = 2021 + $i;
        }
        rsort($date_list);

        $userid = $this->getUid();
        //获取当月总支出和总收入
        $baseModel = new ExamBase();
        $where = "record_date like '$date%' and is_logic_del=0";
        $paywhere = " and money_type=1";
        $incomewhere = " and money_type=0";
        //获取当年支出笔数
        $pay_num = $baseModel->dataCount($this->dbconfig, "details", $where . $paywhere);
        //获取当年总支出
        $pay_count = $baseModel->dataSum($this->dbconfig, "details", "money_num", $where . $paywhere);
        //当年收入笔数
        $incom_num = $baseModel->dataCount($this->dbconfig, "details", $where . $incomewhere);
        //获取当年总收入
        $incom_count = $baseModel->dataSum($this->dbconfig, "details", "money_num", $where . $incomewhere);
        //获取每个类型的总支出
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
        $income_data = [];
        $month_pay_data = [];
        $month_balance_data = [];
        //获取每月收入
        for ($i = 1; $i <= 12; $i++) {
            $month = $i;
            if ($i < 10) {
                $month = "0" . $i;
            }
            $oneincome = $baseModel->dataSum($this->dbconfig, "details", "money_num", "record_date like '$date-$month%' and money_type=0");
            $income_data[] = $oneincome;
            $onepay = $baseModel->dataSum($this->dbconfig, "details", "money_num", "record_date like '$date-$month%' and money_type=1");
            $month_pay_data[] = $onepay;
            $month_balance_data[] = round($oneincome - $onepay,2);
        }
        $return = [
            "pay_num" => $pay_num,//支出数量
            "pay_data" => $pay_data ?: null,//每种类型的支出
            "pay_count" => $pay_count ?: "0.00",//当年总支出
            "income_num" => $incom_num,//收入数量（计了几次）
            "income_count" => $incom_count ?: "0.00", //当年总收入
            "income_data" => $income_data,//每月收入数据
            "month_pay_data" => $month_pay_data,//每月支出数据
            "month_balance_data" => $month_balance_data,//每月结余数据
            "year" => $date,
            "year_list" => $date_list
        ];
        return respondApi($return);
    }

    /**
     * 房贷信息自动录入；
     */
    public function insertData()
    {
        $nowmonth = date("Y-m");
        $baseModel = new ExamBase();
        $isdata = $baseModel->oneDetail($this->dbconfig, "details", "type_id=20 and record_date like '$nowmonth%'");
        if (empty($isdata)) {
            $insert = [
                "type_id" => 20,
                "money_num" => config("app.web_config.money_sum"),
                "account_id" => 1,
                "record_date" => date("Y-m-d"),
                "data_remark" => "房贷自动添加",
                "money_type" => 1,
                "user_id" => 1,
                "type_name" => "房贷"
            ];
            $baseModel->addOne($this->dbconfig, "details", $insert);
        }

        return respondApi();
    }

    /**
     * 代缴公积金信息自动录入；
     */
    public function insertData2()
    {
        $nowmonth = date("Y-m");
        $baseModel = new ExamBase();
        $isdata = $baseModel->oneDetail($this->dbconfig, "details", "type_id=25 and record_date like '$nowmonth%'");
        if (empty($isdata)) {
            $insert = [
                "type_id" => 25,
                "money_num" => config("app.web_config.gjj_money_sum"),
                "account_id" => 1,
                "record_date" => date("Y-m-d"),
                "data_remark" => "代缴公积金自动添加",
                "money_type" => 1,
                "user_id" => 1,
                "type_name" => "公积金代缴"
            ];
            $baseModel->addOne($this->dbconfig, "details", $insert);
        }

        return respondApi();
    }

    /**
     * 租金信息自动录入；
     */
    public function insertData3()
    {
        $nowmonth = date("Y-m");
        $baseModel = new ExamBase();
        $isdata = $baseModel->oneDetail($this->dbconfig, "details", "type_id=16 and data_remark='车位租金自动添加' and record_date like '$nowmonth%'");
        if (empty($isdata)) {
            $insert = [
                "type_id" => 16,
                "money_num" => config("app.web_config.car_money_sum"),
                "account_id" => 1,
                "record_date" => date("Y-m-d"),
                "data_remark" => "车位租金自动添加",
                "money_type" => 1,
                "user_id" => 1,
                "type_name" => "交通"
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
     * 打卡数据
     */
    public function checkList()
    {
        $userid = $this->getUid(0);
        $date = input("date", "");
        $monthtype = input("monthtype", 0);//0当前日期的数据；1传过来日期的上个月数据；2传过来日期的下个月数据
        $typeid = input("typeid", 0);
        if (empty($date)) {
            $date = date("Y-m");
        }
        if ($monthtype == 1) {
            $date = date("Y-m", strtotime("last month", strtotime($date)));
        } else if ($monthtype == 2) {
            $date = date("Y-m", strtotime('next month', strtotime($date)));
        }
        $baseModel = new ExamBase();
        //获取打卡分类
        $type_data = $baseModel->dataList($this->dbconfig, "check_type", "user_id=$userid or user_id=0");
        if (empty($typeid)) {
            $typeid = $type_data[0]["id"];
        }
        //获取查询月份的打卡情况
        $where = "check_in_date like '$date%' and type_id=$typeid and user_id=$userid";
        $data = $baseModel->dataList($this->dbconfig, "checkin", $where);
        $result_data = [];
        //获取查询日期有多少天
        $days = date("t", strtotime($date));
        for ($i = 1; $i <= $days; $i++) {
            $itemday = $i;
            if ($i < 10) {
                $itemday = "0" . $i;
            }
            $ischeck = 0;
            $isnow = 0;
            foreach ($data as $item) {
                if ($item["check_in_date"] == $date . "-" . $itemday) {
                    $ischeck = 1;
                }
                if ($date . "-" . $itemday == date("Y-m-d")) {
                    $isnow = 1;
                }
            }
            $result_data[] = array(
                "day" => $i,
                "ischeck" => $ischeck,
                "isnow" => $isnow
            );
        }

        $date_arr = explode("-", $date);
        //获取当前打卡分类累计打卡
        $check_count = $baseModel->dataCount($this->dbconfig, "checkin", "user_id=$userid and type_id=$typeid");
        $typename = $baseModel->dataValue($this->dbconfig, "check_type", "type_name", "id=$typeid");
        //查询连续天数
        $sql = "SELECT
    count( 1 )  as days
FROM
    (
    SELECT
        date_sub( a.check_in_date, INTERVAL 1 DAY ) signDate,
        ( @i := DATE_ADD( @i, INTERVAL - 1 DAY ) ) today 
    FROM
        ( SELECT check_in_date FROM tally_checkin where user_id=$userid and type_id=$typeid ORDER BY check_in_date DESC ) a
        INNER JOIN (
        SELECT
            @i := max( check_in_date ) AS signMax 
        FROM
            tally_checkin 
        WHERE
            user_id=$userid and type_id=$typeid
            AND (
                TO_DAYS( check_in_date ) = TO_DAYS(
                curdate()) 
                OR TO_DAYS( check_in_date ) = TO_DAYS( DATE_ADD( curdate(), INTERVAL - 1 DAY ) ) 
            ) 
        ) b 
    WHERE
        b.signMax IS NOT NULL 
        AND TO_DAYS(
        DATE_ADD( @i, INTERVAL - 1 DAY )) = TO_DAYS( date_sub( a.check_in_date, INTERVAL 1 DAY ) ) 
    ) c";
        $continuous_count = db('', $this->dbconfig)->query($sql);
        $continuous_count = $continuous_count[0]["days"];
        $return = [
            "days" => $result_data,
            "first_week" => date("w", strtotime("$date-01")),
            "date" => ["year" => $date_arr[0], "month" => ltrim($date_arr[1], "0"), "date" => $date],
            "type_data" => $type_data,
            "check_count" => $check_count,
            "continuous_count" => $continuous_count,
            "sel_type" => ["id" => $typeid, "type_name" => $typename]
        ];
        return respondApi($return);
    }

    /**
     * 用户打卡
     */
    public function checkIn()
    {
        $userid = $this->getUid(0);
        $date = input("date", "");
        $typeid = input("typeid", 1);
        if (empty($date)) {
            $date = date("Y-m-d");
        }
        if ($date > date("Y-m-d")) {
            //未来时间不能打卡
            $status = Codes::ACTION_FAL;
            $message = "未来时间不能打卡";
            return respondApi("", $status, $message);
        }
        $baseModel = new ExamBase();
        //查询是否已经打卡
        $isdata = $baseModel->oneDetail($this->dbconfig, "checkin", "user_id=$userid and type_id=$typeid and check_in_date='$date'");
        if (empty($isdata)) {
            $insert = [
                "user_id" => $userid,
                "type_id" => $typeid,
                "check_in_date" => $date
            ];

            $baseModel->addOne($this->dbconfig, "checkin", $insert);
        }
        return respondApi();
    }

    /**
     * 获取最新的10条身高记录
     */
    public function recordList()
    {
        $sql = "select * from (select * from tally_height_records order by id desc limit 10)as temp order by data_time asc";
        $data = db('', $this->dbconfig)->query($sql);
        $xdata = [];
        $height_data = [];
        $weight_data = [];
        foreach ($data as $item) {
            $xdata[] = $item["data_date"];
            $height_data[] = $item["height_data"];
            $weight_data[] = $item["weight_data"];
        }
        $result = [
            "xdata" => $xdata,
            "height_data" => $height_data,
            "weight_data" => $weight_data,

        ];
        return respondApi($result);
    }

    /**
     * 身高记录数据保存
     */
    public function recordSave()
    {
        $date = input("date", "");
        $height = input("height", "");
        $weight = input("weight", "");
        if (empty($height) || empty($weight)) {
            $status = Codes::ACTION_FAL;
            $message = "身高或体重不能空";
            return respondApi("", $status, $message);
        }
        if (empty($date)) {
            $date = date("Y-m-d");
        }
        $date_time = strtotime($date);
        $baseModel = new ExamBase();

        $isdata = $baseModel->oneDetail($this->dbconfig, "height_records", "height_data='$height' and weight_data='$weight' and data_date='$date'");
        if (empty($isdata)) {
            $insert = [
                "height_data" => $height,
                "weight_data" => $weight,
                "data_time" => $date_time,
                "data_date" => $date,
            ];
            $baseModel->addOne($this->dbconfig, "height_records", $insert);
        }
        return respondApi();
    }

    /**
     * 古诗词列表
     */
    public function poetryList()
    {
        $page = input("page", 1);
        $is_learn = input("is_learn", -1);
        $where = "1=1";
        if ($is_learn >= 0) {
            $where = "is_learn=$is_learn";
        }
        $baseModel = new ExamBase();
        $data = $baseModel->dataList("", "poetry", $where, 0, $page, "is_learn asc");
        $poetry_count = $baseModel->dataCount("", "poetry", $where);
        return respondApi(["data" => $data, "total" => $poetry_count]);
    }

    public function poetryLearn()
    {
        $id = input("id", 0);
        if (!empty($id)) {
            $baseModel = new ExamBase();
            $baseModel->updateOne("", "poetry", ["is_learn" => 1], "id=$id");
        }
        return respondApi();
    }

    /**
     * 获取用户id
     * @return int 用户id
     */
    protected function getUid($datatype = 0)
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
