<?php
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');
$weid = intval($_W['uniacid']);
if ($operation == 'display') {
    $condition = '';
    $stat = $_GPC['status'];
    if (checksubmit('submit1') && !empty($_GPC['delete'])) {
        pdo_delete('amouse_wxapp_zhandui', " id  IN  ('" . implode("','", $_GPC['delete']) . "')");
        message('批量处理成功！', $this->createWebUrl('cards', array('page' => $_GPC['page'])));
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    if (checksubmit('submit2') && !empty($_GPC['delete'])) {
        $ids = $_GPC['delete'];
        include_once IA_ROOT . '/addons/amouse_wxapp_zhandui/AliyunSms.class.php';
        $sms = new \AliyunSms();
        foreach ($ids as $key => $id) {
            $card = pdo_fetch("SELECT `username`,`mobile`,`id` FROM " . tablename('amouse_wxapp_zhandui') . " WHERE id = $id AND uniacid=" . $weid);
            $username= $card['username'];
            $set = pdo_fetch("SELECT `sms_type`,`sms_user`,`sms_secret`,`sms_free_sign_name`,`notice_sms_code` FROM " . tablename('amouse_wxapp_sysset') . " WHERE `uniacid`= :weid  limit 1 ", array(':weid' => $weid));
            $sms_param = "{\"uname\":\"$username\"}";
            if ($set['sms_type'] == 1) {
                $acsResponse =$sms->_sendNewDySms($card['mobile'], $set['sms_user'], $set['sms_secret'], $set['sms_free_sign_name'], $set['notice_sms_code'], $sms_param, $code);
            }else{ //阿里大于老接口
                $acsResponse = $sms->_sendAliDaYuSms($card['mobile'], $set['sms_user'], $set['sms_secret'], $set['sms_free_sign_name'], $set['notice_sms_code'], $sms_param);
            }
        }
        message('批量发送短信成功！', $this->createWebUrl('cards', array('page' => $_GPC['page'])));
    }
    if (!empty($_GPC['mobile'])) {
        $condition .= " AND a.mobile LIKE '%{$_GPC['mobile']}%'";
    }
    if (!empty($_GPC['username'])) {
        $condition .= " AND a.username LIKE '%{$_GPC['username']}%'";
    }
    $list = pdo_fetchall("SELECT a.* FROM " . tablename('amouse_wxapp_zhandui') . " as a WHERE a.uniacid = $weid $condition ORDER BY a.id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('amouse_wxapp_zhandui') . " as a WHERE a.uniacid = $weid  $condition");
    $pager = pagination($total, $pindex, $psize);
    if (!empty($list)) {
        foreach ($list as $key => $card) {
            $list[$key]['avater'] = tomedia($card['avater']);
            $imgs = iunserializer($card['imgs']);
            foreach ($imgs as $k => $imgid) {
                $imgs[$k] = tomedia($imgid);
            }
            $list[$key]['imgs'] = $imgs;

            $cate = pdo_fetch("SELECT * FROM " . tablename('amouse_wxapp_category') . "  where id=:id and uniacid=:uniacid ", array(":id" => $card['categoryId'], ":uniacid" => $weid));
            $list[$key]['categoryName'] = $cate['name'];
        }
    }

} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $item = pdo_fetch("SELECT *  FROM " . tablename('amouse_wxapp_zhandui') . " WHERE id =:id AND uniacid=:weid limit 1", array(":id" => $id, ":weid" => $weid));
        $piclist = array();
        $piclist1 = unserialize($item['imgs']);
        if (is_array($piclist1)) {
            foreach ($piclist1 as $key => $p) {
                $piclist[] = is_array($p) ? $p['attachment'] : tomedia($p);
            }
        }
        $item['createtime'] = $item['createtime'] == 0 ? time() : $item['createtime'];
    } else {
        $item['createtime'] = time();
    }

    if (checksubmit('submit')) {
        $data2['uniacid'] = $weid;
        $data2['mobile'] = trim($_GPC['mobile']);
        $data2['username'] = trim($_GPC['username']);
        $data2['email'] = trim($_GPC['email']);
        $data2['weixin'] = $_GPC['weixin'];
        $data2['zan'] = intval($_GPC['zan']);
        $data2['view'] = intval($_GPC['view']);
        $data2['collect'] = trim($_GPC['collect']);
        $data2['company'] = trim($_GPC['company']);
        $data2['job'] = trim($_GPC['job']);
        $data2['vip'] = intval($_GPC['vip']);
        $data2['createtime'] = strtotime($_GPC['createtime']);
        $data2['imgs'] = serialize($_GPC['imgs']);
        $data2['avater'] = $_GPC['avater'];
        $data2['weixinImg'] = $_GPC['weixinImg'];
        $data2['categoryId'] = intval($_GPC['categoryId']);
        $data2['desc'] = $_GPC['desc'];
        if (!empty($id)) {
            pdo_update('amouse_wxapp_zhandui', $data2, array('id' => $id));
        } else {
            pdo_insert('amouse_wxapp_zhandui', $data2);
            $id = pdo_insertid();
        }
        message('更新信息成功！', $this->createWebUrl('wings', array('op' => 'display')), 'success');
    }

} elseif ($operation == 'delete') {
    $id = intval($_GPC['id']);
    $order = pdo_fetch("SELECT id  FROM " . tablename('amouse_wxapp_zhandui') . " WHERE id = $id AND uniacid=" . $weid);
    if (empty($order)) {
        message('抱歉，记录不存在或者已经删除！', $this->createWebUrl('cards', array('op' => 'display')), 'error');
    }
    pdo_delete('amouse_wxapp_zhandui', array('id' => $id));

    message('删除成功！', $this->createWebUrl('wings', array('op' => 'display')), 'success');
} elseif ($operation == 'clear') {
    $id = intval($_GPC['id']);
    $order = pdo_fetch("SELECT * FROM " . tablename('amouse_wxapp_zhandui') . " WHERE id = $id AND uniacid=" . $weid);
    load()->func('file');
    if (!empty($order)) {
        if ($_W['setting']['remote']['type']) {
            file_remote_delete($order['qrcode']);
        } else {
            file_delete($order['qrcode']);
        }
    }
    $sql = "update  " . tablename('amouse_wxapp_zhandui') . "  set `qrcode`='' where `id`='$id' ";
    pdo_query($sql);
    message('清除二维码成功！', $this->createWebUrl('wings', array('op' => 'display')), 'success');
}
include $this->template('web/wing');
