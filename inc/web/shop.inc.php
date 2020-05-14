<?php

global $_W, $_GPC;
load()->func("tpl");
$op = !empty($_GPC["op"]) ? $_GPC["op"] : "display";
$uniacid = intval($_W['uniacid']);
$category = pdo_fetchall("SELECT id,parentid,name FROM " . tablename('amouse_wxapp_category') . " WHERE uniacid = '{$_W['uniacid']}' and type=1 ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
$parent = array();
$children = array();
if (!empty($category)) {
    $children = '';
    foreach ($category as $cid => $cate) {
        if (!empty($cate['parentid'])) {
            $children[$cate['parentid']][] = $cate;
        } else {
            $parent[$cate['id']] = $cate;
        }
    }
}
if ($op == "display") {
    $pindex = max(1, intval($_GPC["page"]));
    $psize = 50;
    $con = " `uniacid`={$uniacid} ";
    $nickname = $_GPC["title"];
    if (!empty($nickname)) {
        $con .= " and shop_name like '%$nickname%'";
    }
    $list = pdo_fetchall("SELECT * FROM ".tablename('amouse_wxapp_card_shop') . "  WHERE uniacid ={$uniacid} $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('amouse_wxapp_card_shop') . "   WHERE uniacid ={$uniacid}  $condition");
    $pager = pagination($total, $pindex, $psize);
}
if ($op == "post") {
    $id = $_GPC["id"];
    if (!empty($id)) {
        $page_data = pdo_fetch("SELECT * FROM " . tablename('amouse_wxapp_card_shop') . " WHERE id ={$id} limit 1 ");
        $pcate = $page_data['category_id'];
        $ccate = $page_data['child_id'];
        $piclist = array();
        $piclist1 = unserialize($page_data['imgs']);
        if (is_array($piclist1)) {
            foreach ($piclist1 as $key => $p) {
                $piclist[] = is_array($p) ? $p['attachment'] : tomedia($p);
            }
        }
        $piclist2 = array();
        $piclist3 = unserialize($page_data['ad']);
        if (is_array($piclist3)) {
            foreach ($piclist3 as $key => $p) {
                $piclist2[] = is_array($p) ? $p['attachment'] : tomedia($p);
            }
        }
    }
    if (checksubmit("submit")) {
        $input = array();
        $input = $_GPC["data"];
        $input["uniacid"] = $uniacid;
        $input["createtime"] = TIMESTAMP;
        $input['imgs'] = serialize($_GPC['imgs']);
        $input['ad'] = serialize($_GPC['ad']);

        $input['status'] =1;
        $input['category_id'] = intval($_GPC['category']['parentid']);
        $input['child_id'] = intval($_GPC['category']['childid']);
        if (!empty($id)) {
            pdo_update('amouse_wxapp_card_shop', $input, array('id' => $id));
        } else {
            pdo_insert('amouse_wxapp_card_shop', $input);
            $id = pdo_insertid();
        }
        message("信息更新成功", $this->createWebUrl('shop', array("op" => "display")), "success");
    }
}

if ($op == "delete") {
    $id = intval($_GPC['id']);
    $order = pdo_fetch("SELECT id  FROM " . tablename('amouse_wxapp_card_shop') . " WHERE id ={$id} AND uniacid=" . $uniacid);
    if (empty($order)) {
        message('抱歉，记录不存在或者已经删除！', $this->createWebUrl('shop', array('op' => 'display')), 'error');
    }
    pdo_delete('amouse_wxapp_card_shop', array('id' => $id));
    message('删除成功！', $this->createWebUrl('shop', array('op' => 'display')), 'success');
}

if ($op == "delete_all") {
    pdo_delete('amouse_wxapp_card_shop', array('uniacid' => $uniacid));
    message("删除成功", $this->createWebUrl($filename, array("op" => "display")), "success");
}

include $this->template('web/shop');

