<?php
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');
$weid = intval($_W['uniacid']);
$category = pdo_fetchall("SELECT id,parentid,name FROM " . tablename('amouse_wxapp_category') . " WHERE uniacid= '{$_W['uniacid']}' and `type`=1 ORDER BY parentid DESC, displayorder DESC, id DESC ", array(), 'id');
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
$cards= pdo_fetchall("SELECT * FROM ".tablename('amouse_wxapp_card') . "  WHERE uniacid ={$weid}   ORDER BY id DESC ");
if ($operation == 'display') {
    $condition = '';
    $stat = $_GPC['status'];
    if (checksubmit('submit1') && !empty($_GPC['delete'])) {
        pdo_delete('amouse_wxapp_info', " id  IN  ('" . implode("','", $_GPC['delete']) . "')");
        message('批量处理成功！', $this->createWebUrl('info', array('page' => $_GPC['page'])));
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    if (!empty($_GPC['mobile'])) {
        $condition .= " AND mobile LIKE '%{$_GPC['mobile']}%'";
    }
    if (!empty($_GPC['linkman'])) {
        $condition .= " AND linkman LIKE '%{$_GPC['linkman']}%'";
    }
    $list = pdo_fetchall("SELECT * FROM ".tablename('amouse_wxapp_info') . "  WHERE uniacid ={$weid} $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('amouse_wxapp_info') . "   WHERE uniacid ={$weid}  $condition");
    $pager = pagination($total, $pindex, $psize);
    foreach ($list as $key => $card) {
        $list[$key]['avater'] = tomedia($card['avater']);
        $imgs = iunserializer($card['img']);
        foreach ($imgs as $k => $imgid) {
            $imgs[$k] = tomedia($imgid);
        }
        $list[$key]['imgs'] = $imgs;
        $cate = pdo_fetch("SELECT * FROM " . tablename('amouse_wxapp_category') . "  where id=:id and uniacid=:uniacid ", array(":id" => $card['cate_id'], ":uniacid" => $weid));
        $list[$key]['categoryName'] = $cate['name'];
        $c = pdo_fetch("SELECT * FROM " . tablename('amouse_wxapp_card') . "  where id=:id and uniacid=:uniacid ", array(":id" => $card['user_id'], ":uniacid" => $weid));
        $list[$key]['avater'] = tomedia($c['avater']);
    }
} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $item = pdo_fetch("SELECT *  FROM " . tablename('amouse_wxapp_info') . " WHERE id =:id AND uniacid=:weid limit 1", array(":id" => $id, ":weid" => $weid));
        $piclist = array();
        $piclist1 = unserialize($item['img']);
        if (is_array($piclist1)) {
            foreach ($piclist1 as $key => $p) {
                $piclist[] = is_array($p) ? $p['attachment'] : tomedia($p);
            }
        }
        $item['createtime'] = $item['createtime'] == 0 ? time() : $item['createtime'];
        $pcate = $item['cate_id'];
        $ccate = $item['child_cate_id'];
    } else {
        $item['createtime'] = time();
    }

    if (checksubmit('submit')) {
        $data2['uniacid'] = $weid;
        $data2['mobile'] = trim($_GPC['mobile']);
        $data2['linkman'] = trim($_GPC['linkman']);
        $data2['user_id'] = $_GPC['user_id'];
        $data2['views'] = intval($_GPC['views']); $data2['hot'] = intval($_GPC['hot']);
        $data2['createtime'] = time();
        $data2['img'] = serialize($_GPC['img']);
        $data2['status'] = $_GPC['status'];
        $data2['address'] = $_GPC['address'];
        $data2['lat'] = trim($_GPC['lat']);
        $data2['lng'] = trim($_GPC['lng']);
        $data2['cate_id'] = intval($_GPC['category']['parentid']);
        $data2['child_cate_id'] = intval($_GPC['category']['childid']);
        $data2['details'] = htmlspecialchars_decode($_GPC['details'], ENT_QUOTES);
        if (!empty($id)) {
            pdo_update('amouse_wxapp_info', $data2, array('id' => $id));
        } else {
            var_dump($data2);
            pdo_insert('amouse_wxapp_info', $data2);
            $id = pdo_insertid();
        }
        message('更新信息成功！', $this->createWebUrl('info', array('op' => 'display')), 'success');
    }

} elseif ($operation == 'delete') {
    $id = intval($_GPC['id']);
    $order = pdo_fetch("SELECT id  FROM " . tablename('amouse_wxapp_info') . " WHERE id = $id AND uniacid=" . $weid);
    if (empty($order)) {
        message('抱歉，记录不存在或者已经删除！', $this->createWebUrl('info', array('op' => 'display')), 'error');
    }
    pdo_delete('amouse_wxapp_info', array('id' => $id));
    message('删除成功！', $this->createWebUrl('info', array('op' => 'display')), 'success');
} elseif ($operation == 'setstatus') {
    $id = intval($_GPC['id']);
    $data = intval($_GPC['data']);
    $type = $_GPC['type'];
    $data = ($data == 1 ? '0' : '1');
    pdo_update('amouse_wxapp_info', array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
    die(json_encode(array( 'result' => 1, 'data' => $data )));
}
include $this->template('web/info');