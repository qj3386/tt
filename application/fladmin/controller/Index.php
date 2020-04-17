<?php

namespace app\fladmin\controller;

use think\Db;
use think\Validate;
use app\common\lib\ReturnData;
use app\common\lib\Helper;

class Index extends Base
{
    public function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $this->assign('menus', model('Menu')->getPermissionsMenu($this->admin_info['role_id']));

        $this->assign('module_name', request()->module());

        return $this->fetch();
    }

    public function welcome()
    {
        return $this->fetch();
    }

    public function upcache()
    {
        dir_delete(APP_PATH . '../runtime/');
        $this->success('缓存更新成功');
    }

    /**
     * 更新配置文件 / 更新系统缓存
     */
    public function updateconfig()
    {
        $str_tmp = "<?php\r\n"; //得到php的起始符。$str_tmp将累加
        $str_end = "?>"; //php结束符
        $str_tmp .= "//全站配置文件\r\n";

        $param = db("sysconfig")->select();
        foreach ($param as $row) {
            $str_tmp .= 'define("' . $row['varname'] . '","' . $row['value'] . '"); // ' . $row['info'] . "\r\n";
        }

        $str_tmp .= $str_end; //加入结束符
        //保存文件
        $sf = APP_PATH . "common.inc.php"; //文件名
        $fp = fopen($sf, "w"); //写方式打开文件
        fwrite($fp, $str_tmp); //存入内容
        fclose($fp); //关闭文件
        return $sf;
    }

	// 获取充值/提现消息
    public function get_recharge_withdrawal_info()
    {
		$three_minutes = time() - 60; //1分钟自动忽略
		$has_recharge = 0; //有人充值
		$has_withdraw = 0; //有人提现
		$res = model('UserRecharge')->getOne(['status'=>0, 'is_ignore'=>0, 'delete_time'=>0]);
		if ($res) {
			$has_recharge = 1;
			if ($res['add_time'] < $three_minutes) {
				model('UserRecharge')->edit(['is_ignore'=>1], ['id'=>$res['id']]);
			}
		}
		$res = model('UserWithdraw')->getOne(['status'=>1, 'is_ignore'=>0, 'delete_time'=>0]);
		if ($res) {
			$has_withdraw = 1;
			if ($res['add_time'] < $three_minutes) {
				model('UserWithdraw')->edit(['is_ignore'=>1], ['id'=>$res['id']]);
			}
		}
        exit(json_encode(array('code' => 0, 'msg' => '操作成功', 'data' => ['has_recharge'=>$has_recharge, 'has_withdraw'=>$has_withdraw])));
    }

	/**
     * 文件上传
     */
    public function file_upload()
    {
        return $this->fetch();
    }

	/**
     * 银行卡校验
     */
    public function bank_card_verification()
    {
		if (Helper::isPostRequest()) {
            //银行卡三要素验证
            $res = $this->bank_card_certification($_POST['bank_card_number'], $_POST['idcard'], $_POST['true_name']);
            if ($res['code'] != ReturnData::SUCCESS) {
                $this->error('银行卡信息验证不通过');
            }

			$this->success('验证通过');
        }

        return $this->fetch();
    }

	/**
     * 银行卡三要素验证-四川涪擎大数据技术有限公司https://shop922915o2.market.aliyun.com/
     * https://market.aliyun.com/products/57000002/cmapi028807.html
     * {
     * "status": "01",                            状态码:01 验证通过；02 验证不通过；详见状态码说明
     * "msg": "验证通过",                         信息
     * "idCard": "5111261995****1111",            身份证号
     * "accountNo": "621411111****9563",          银行卡号
     * "name": "张三",                            姓名
     * "bank": "中国建设银行",                    银行名称
     * "cardName": "龙卡通",                      银行卡名称
     * "cardType": "借记卡",                      银行卡类型
     * "sex": "男",                               性别
     * "area": "四川省乐山市夹江县",              身份证所在地址(参考)
     * "province": "四川省",                      省(参考)
     * "city": "乐山市",                          市(参考)
     * "prefecture": "夹江县",                    区县(参考)
     * "birthday": "1995-11-11",                  出生年月
     * "addrCode": "511126",                      地区代码
     * "lastCode": "1"                            校验码
     * }
     */
    public function bank_card_certification($bank_card_number, $idcard, $true_name)
    {
        $host = "https://bcard3and4.market.alicloudapi.com";
        $path = "/bank3CheckNew";
        $method = "GET";
        $appcode = sysconfig('CMS_ALIYUN_MARKET_APPCODE');
        $headers = array();
        array_push($headers, "Authorization:APPCODE " . $appcode);
        $querys = "accountNo=" . $bank_card_number . "&idCard=" . $idcard . "&name=" . urlencode($true_name);
        $bodys = "";
        $url = $host . $path . "?" . $querys;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, false);
        //curl_setopt($curl, CURLOPT_HEADER, true);   如不输出json, 请打开这行代码，打印调试头部状态码。
        //状态码: 200 正常；400 URL无效；401 appCode错误； 403 次数用完； 500 API网管错误
        if (1 == strpos("$" . $host, "https://")) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        $out_put = curl_exec($curl);
        $res = json_decode($out_put, true);
        $res['code'] = 1;
        if (isset($res['status']) && $res['status'] == '01') {
            $res['code'] = 0;
        }
        return $res;
    }
}