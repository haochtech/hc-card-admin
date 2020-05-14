<?php

global $_W, $_GPC;
$operation = !empty($_GPC["op"]) ? $_GPC["op"] : "display";
load()->func("tpl");
$weid = intval($_W["uniacid"]);
$plist = pdo_getall("core_paylog", array("module" => "amouse_wxapp_card"));
if ($operation == "display") {
	if (checksubmit("submit1") && !empty($_GPC["delete"])) {
		pdo_delete("amouse_wxapp_order", " id  IN  ('" . implode("','", $_GPC["delete"]) . "')");
		message("批量处理成功！", $this->createWebUrl("orders", array("page" => $_GPC["page"])));
	}
	$condition = '';
	$starttime = empty($_GPC["date"]["start"]) ? strtotime("-2 month") : strtotime($_GPC["date"]["start"]);
	$endtime = empty($_GPC["date"]["end"]) ? TIMESTAMP : strtotime($_GPC["date"]["end"]) + 86399 * 2;
	$stat = $_GPC["status"];
	if ($stat != '') {
		$status = intval($stat);
		$condition .= " AND o.status = '{$status}'";
	}
	if (!empty($_GPC["date"]["start"]) && !empty($_GPC["date"]["end"])) {
		$condition .= " AND  o.createtime>={$starttime}  AND  o.createtime<={$endtime}";
	}
	$pindex = max(1, intval($_GPC["page"]));
	$psize = 10;
	$sql = "SELECT o.*,c.username,c.mobile,c.id as cid FROM " . tablename("amouse_wxapp_order") . " as o left join " . tablename("amouse_wxapp_card") . " as c on c.openid=o.openid WHERE o.uniacid =:weid and o.module='amouse_wxapp_card' {$condition} ORDER BY o.createtime DESC LIMIT " . ($pindex - 1) * $psize . "," . $psize;
	$list = pdo_fetchall($sql, array(":weid" => $weid));
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("amouse_wxapp_order") . " as o left join " . tablename("amouse_wxapp_card") . " as c on c.openid=o.openid   WHERE o.uniacid = {$weid} and o.module='amouse_wxapp_card' {$condition}");
	$pager = pagination($total, $pindex, $psize);
} elseif ($operation == "detail") {
	$id = intval($_GPC["id"]);
	$item = pdo_fetch("SELECT * FROM " . tablename("amouse_wxapp_order") . " WHERE id = :id and uniacid = :weid", array(":id" => $id, ":weid" => $weid));
	$card = pdo_fetch("SELECT * FROM " . tablename("amouse_wxapp_card") . " WHERE openid = :openid and uniacid = :weid", array(":openid" => $item["openid"], ":weid" => $weid));
} elseif ($operation == "delete") {
	$id = intval($_GPC["id"]);
	$order = pdo_fetch("SELECT id  FROM " . tablename("amouse_wxapp_order") . " WHERE id = {$id} AND uniacid=" . $weid);
	if (empty($order)) {
		message("抱歉，订单不存在，或者已经删除！", $this->createWebUrl("orders", array("op" => "display")), "error");
	}
	pdo_delete("amouse_wxapp_order", array("id" => $id));
	message("订单删除成功！", $this->createWebUrl("orders", array("order" => "display")), "success");
}
include $this->template("web/order");