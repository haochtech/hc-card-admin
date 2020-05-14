<?php

global $_W, $_GPC;
$op = !empty($_GPC["op"]) ? $_GPC["op"] : "display";
$weid = $_W["uniacid"];
if ($op == "display") {
	if (!empty($_GPC["displayorder"])) {
		foreach ($_GPC["displayorder"] as $id => $displayorder) {
			$update = array("displayorder" => $displayorder);
			pdo_update("amouse_wxapp_navs", $update, array("id" => $id));
		}
		message("排序更新成功！", "refresh", "success");
	}
	$pindex = max(1, intval($_GPC["page"]));
	$psize = 10;
	$condition = "WHERE uniacid = '{$weid}'";
	$list = pdo_fetchall("SELECT * FROM " . tablename("amouse_wxapp_navs") . " {$condition} ORDER BY displayorder desc  LIMIT " . ($pindex - 1) * $psize . "," . $psize, $params);
	$total = pdo_fetchcolumn("SELECT COUNT(*) FROM " . tablename("amouse_wxapp_navs") . " {$condition} ");
	$pager = pagination($total, $pindex, $psize);
} elseif ($op == "post") {
	load()->func("tpl");
	$id = intval($_GPC["id"]);
	if ($id > 0) {
		$item = pdo_fetch("SELECT * FROM " . tablename("amouse_wxapp_navs") . " WHERE id = :id", array(":id" => $id));
	}
	if (checksubmit("submit")) {
		$data = array("uniacid" => $_W["uniacid"], "title" => trim($_GPC["title"]), "displayorder" => intval($_GPC["displayorder"]), "thumb" => $_GPC["thumb"], "qrcode" => $_GPC["qrcode"], "recommend" => intval($_GPC["recommend"]), "bgcolor" => $_GPC["bgcolor"], "info" => $_GPC["info"], "status" => intval($_GPC["status"]), "click" => intval($_GPC["click"]), "appid" => $_GPC["appid"], "followurl" => trim($_GPC["followurl"]));
		if (empty($id)) {
			pdo_insert("amouse_wxapp_navs", $data);
		} else {
			pdo_update("amouse_wxapp_navs", $data, array("id" => $id));
		}
		message("导航更新成功！", $this->createWebUrl("navs", array("op" => "display")), "success");
	}
} elseif ($op == "delete") {
	$id = intval($_GPC["id"]);
	$row = pdo_fetch("SELECT id FROM " . tablename("amouse_wxapp_navs") . " WHERE id = :id", array(":id" => $id));
	if (empty($row)) {
		message("抱歉，导航不存在或是已经被删除！");
	}
	pdo_delete("amouse_wxapp_navs", array("id" => $id));
	message("删除成功！", referer(), "success");
}
include $this->template("web/nav");