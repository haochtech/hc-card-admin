<?php

defined('IN_IA') or exit('Access Denied');
define('AMOUSE_WXAPP_CARD', 'amouse_wxapp_card');
define('RES', '../addons/' . AMOUSE_WXAPP_CARD . '/style/');
error_reporting(0);
class Amouse_Wxapp_CardModuleSite extends WeModuleSite
{
	public function doMobileAutoFinishVip()
	{
		global $_GPC;
		$uniacid = $_GPC['i'];
		$sets = pdo_fetchall('SELECT uniacid,collect_tpl,zan_tpl FROM ' . tablename('amouse_wxapp_sysset'));
		load()->func('file');
		foreach ($sets as $set) {
			$sweid = $set['uniacid'];
			if (empty($sweid)) {
				continue;
			}
			$autos = pdo_fetchall('SELECT id,openid,createtime,username,vip FROM ' . tablename('amouse_wxapp_card') . ' where vip=1 and createtime<unix_timestamp()  order by createtime desc ');
			foreach ($autos as $k => $auto) {
				pdo_update('amouse_wxapp_card', array("createtime" => time(), "vip" => 0, "clazz" => "default"), array("id" => $auto['id']));
				$sendFromid = $this->getFormId($uniacid, $auto['openid']);
					$send['phrase1'] = array("value" => "VIP会员", "color" => "#000");
					$send['phrase2'] = array("value" => "普通会员", "color" => "#000");
					$send['date4'] = array("value" => date('Y/m/d H:i:s', time()), "color" => "#000");
					$send['thing5'] = array("value" => "您的会员等级已降级", "color" => "#000");
					$this->sendTplNotice($uniacid, $auto['openid'], $set['zan_tpl'], 'amouse_wxapp_card/pages/card/home/home',  $send);
				
			}
			var_dump($autos);
		}
	}
	private function _get($uniacid, $openId)
	{
		$tplcodes = pdo_fetchall('SELECT * FROM ' . tablename('amouse_wxapp_tplcode') . ' WHERE `uniacid`= :weid and `openid`=:openid and status=0 ', array(":weid" => $uniacid, ":openid" => $openId));
		return $tplcodes;
	}
	public function getFormId($uniacid, $openId)
	{
		$res = $this->_get($uniacid, $openId);
		if ($res) {
			if (!count($res)) {
				return FALSE;
			}
			$result = FALSE;
			for ($i = 0; $i < count($res); $i++) {
				if ($res[$i]['createtime'] > time()) {
					$result = $res[$i]['code'];
				}
			}
			return $result;
		} else {
			return FALSE;
		}
	}
	public function payResult($params)
	{
		global $_W;
		load()->func('logging');
		$order = pdo_fetch('SELECT * FROM ' . tablename('amouse_wxapp_order') . ' WHERE `id`= :id AND `uniacid`= :weid ', array(":id" => $params['tid'], ":weid" => intval($_W['uniacid'])));
		$orderData = array("status" => $params['result'] == 'success' ? 1 : 0);
		if ($params['type'] == 'wechat') {
			$orderData['transid'] = $params['tag']['transaction_id'];
		}
		if ($params['result'] == 'success' && $params['from'] == 'notify') {
			if ($params['fee'] == $order['price']) {
				pdo_update('amouse_wxapp_order', $orderData, array("id" => $params['tid']));
				$openid = $order['openid'];
				$top_day = $order['top_day'];
				$card = pdo_fetch('SELECT * FROM ' . tablename('amouse_wxapp_card') . ' WHERE `openid` = :id AND `uniacid` = :weid ', array(":id" => $openid, ":weid" => intval($_W['uniacid'])));
				if ($card['createtime'] >= time()) {
					$nextWeek = $card['createtime'] + $top_day * 24 * 60 * 60;
				} else {
					$nextWeek = TIMESTAMP + $top_day * 24 * 60 * 60;
				}
				$data2['createtime'] = $nextWeek;
				$data2['vip'] = 1;
				pdo_update('amouse_wxapp_card', $data2, array("id" => $card['id']));
				$sys = pdo_fetch('SELECT `id`,`pay_credit`,`collect_tpl`,`public_credit` FROM ' . tablename('amouse_wxapp_sysset') . ' WHERE `uniacid`= :weid  limit 1 ', array(":weid" => intval($_W['uniacid'])));
				$zan_tpl = pdo_fetchcolumn('SELECT zan_tpl FROM ' . tablename('amouse_wxapp_sysset') . ' where `uniacid`=:uniacid limit 1 ', array(":uniacid" => intval($_W['uniacid'])));

					$end_time = date('Y/m/d H:i:s', $nextWeek);
					$send['phrase1'] = array("value" => "普通会员", "color" => "#000");
					$send['phrase2'] = array("value" => "VIP会员", "color" => "#000");
					$send['date4'] = array("value" => $end_time, "color" => "#000");
					$send['thing5'] = array("value" => "您的会员等级已升级", "color" => "#000");
					$this->sendTplNotice(intval($_W['uniacid']), $card['openid'], $zan_tpl, 'amouse_wxapp_card/pages/cooperation/cooperation',  $send);
				
				if ($sys['pay_credit'] > 0) {
					$this->setFansCredit($order['openid'], 'credit1', $sys['pay_credit'], $sys['limit_credit'], "{$order['openid']}支付成功赠送积分" . $sys['pay_credit']);
				}
			}
		}
	}
	public function setFansCredit($openid, $credittype, $credit, $limit, $remark)
	{
		load()->model('mc');
		load()->func('compat.biz');
		$uid = mc_openid2uid($openid);
		$fans = mc_fetch($uid, array($credittype));
		if (!empty($fans)) {
			$uid = intval($fans['uid']);
			$log = array();
			$log[0] = $uid;
			$log[1] = $remark;
			$log[2] = $this->modulename;
			$date = date('Y-m-d');
			$record = pdo_fetchcolumn('SELECT sum(num) FROM ' . tablename('mc_credits_record') . ' WHERE uniacid = :weid and uid = :uid and date_format(FROM_UNIXTIME(createtime), \'%Y-%m-%d\') = :date', array(":weid" => $_W['uniacid'], ":wid" => $uid, ":date" => $date));
			if ($limit == 0 || $limit > $record) {
				return mc_credit_update($uid, $credittype, $credit, $log);
			}
		}
	}
	protected function sendTplNotice($uniacid, $touser, $template_id, $page = "",  $postdata)
	{
		load()->model('mc');
		load()->func('communication');
		$account_api = WeAccount::create($uniacid);
		$accesstoken = $account_api->getAccessToken();
		if (is_error($accesstoken)) {
			return $accesstoken;
		}
		if (empty($touser)) {
			return error(-1, '参数错误,粉丝openid不能为空');
		}
		if (empty($template_id)) {
			return error(-1, '参数错误,模板标示不能为空');
		}
		if (empty($postdata) || !is_array($postdata)) {
			return error(-1, '参数错误,请根据模板规则完善消息内容');
		}
		$data = array();
		$data['touser'] = $touser;
		$data['template_id'] = trim($template_id);
		$data['page'] = trim($page);
		$data['form_id'] = trim($form_id);
		if ($emphasis_keyword) {
			$send['emphasis_keyword'] = $emphasis_keyword;
		}
		$data['data'] = $postdata;
		$data = json_encode($data);
		$templateUrl = "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$accesstoken}";
		$response = ihttp_request($templateUrl, $data);
		if (is_error($response)) {
			return error(-1, "访问公众平台接口失败, 错误: {$response['message']}");
		}
		$result = @json_decode($response['content'], true);
		WeUtility::logging('sendTplNotice', var_export($result, true));
		if (empty($result)) {
			return error(-1, "接口调用失败, 元数据: {$response['meta']}");
		} else {
			if (!empty($result['errcode'])) {
				return error(-1, "访问微信接口错误, 错误代码: {$result['errcode']}, 错误信息: {$result['errmsg']},信息详情：{$this->error_code($result['errcode'])}");
			}
		}
		return true;
	}
	private function radian($d)
	{
		return $d * PI / 180.0;
	}
	public function distanceBetween($longitude1, $latitude1, $longitude2, $latitude2)
	{
		$radLat1 = $this->radian($latitude1);
		$radLat2 = $this->radian($latitude2);
		$a = $this->radian($latitude1) - $this->radian($latitude2);
		$b = $this->radian($longitude1) - $this->radian($longitude2);
		$s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
		$s = $s * EARTH_RADIUS;
		$s = round($s * 10000) / 10000;
		return $s * 1000;
	}
}