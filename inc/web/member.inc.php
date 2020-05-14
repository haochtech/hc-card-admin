<?php

global $_W, $_GPC;
$weid = $_W["uniacid"];
$op = $_GPC["op"] ? $_GPC["op"] : "display";
if ($op == "display") {
	$pindex = intval($_GPC["page"]) ? intval($_GPC["page"]) : 1;
	$psize = 20;
	$con = " where m.uniacid = :weid   ";
	$params[":weid"] = $weid;
	$start = ($pindex - 1) * $psize;
	$sql = "SELECT m.uid,f.openid,m.credit1,f.nickname,m.avatar  FROM " . tablename("mc_members") . " as m left join " . tablename("mc_mapping_fans") . " as f on f.uid=m.uid" . $con . "  limit  {$start} ,{$psize} ";
	$list = pdo_fetchall($sql, $params);
	$total = pdo_fetchcolumn("SELECT count(*)  FROM " . tablename("mc_members") . " as m left join " . tablename("mc_mapping_fans") . " as f on f.uid=m.uid " . $con, $params);
	$pager = pagination($total, $pindex, $psize);
} elseif ($op == "ajaxUpdateCredits") {
	if (checksubmit("submit")) {
		if (!empty($_GPC["credit1"]) && empty($_GPC["credit1"])) {
			message("请输入积分");
		}
		$openid = $_GPC["cid"];
		if ($openid) {
			$credit1 = $_GPC["credit1"];
			$this->setFansCredit($openid, "credit1", $credit1, 0, "{$openid}后台赠送积分" . $credit1);
		}
		message("赠送" . $credittxt . "操作成功！", $this->createWebUrl("member", array("op" => "display")), "success");
	}
} elseif ($op == "del") {
	$id = intval($_GPC["id"]);
	if ($id) {
		pdo_delete("amouse_wxapp_member", array("id" => $id));
	}
	message("删除会员数据成功！", $this->createWebUrl("member", array("op" => "display")), "success");
}
include $this->template("web/member");