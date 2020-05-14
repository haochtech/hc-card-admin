<?php
global $_W, $_GPC;
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');
$weid = intval($_W['uniacid']);
$category = pdo_fetchall("SELECT id,parentid,name,thumb FROM " . tablename('amouse_wxapp_category') . " WHERE uniacid = '{$_W['uniacid']}' and type=0 ORDER BY parentid ASC, displayorder ASC, id ASC ", array(), 'id');
$parent = array();
$children = array();
if (!empty($category)) {
    $children = '';
    foreach ($category as $cid => $cate) {
        $cate['name'] = $cate['name'];
        if (!empty($cate['parentid'])) {
            $children[$cate['parentid']][] = $cate;
        } else {
            $parent[$cate['id']] = $cate;
        }
    }
}

if ($operation == 'display') {
    $condition = '';
    $stat = $_GPC['status'];
    if (checksubmit('submit1') && !empty($_GPC['delete'])) {
        pdo_delete('amouse_wxapp_card', " id  IN  ('" . implode("','", $_GPC['delete']) . "')");
        message('批量处理成功！', $this->createWebUrl('cards', array('page' => $_GPC['page'])));
    }
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    if (checksubmit('submit2') && !empty($_GPC['delete'])) {
        $ids = $_GPC['delete'];
        include_once IA_ROOT . '/addons/amouse_wxapp_card/AliyunSms.class.php';
        $sms = new \AliyunSms();
        foreach ($ids as $key => $id) {
            $card = pdo_fetch("SELECT `username`,`mobile`,`id` FROM " . tablename('amouse_wxapp_card') . " WHERE id = $id AND uniacid=" . $weid);
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
        $condition .= " AND mobile LIKE '%{$_GPC['mobile']}%'";
    }
    if (!empty($_GPC['username'])) {
        $condition .= " AND username LIKE '%{$_GPC['username']}%'";
    }
    $list = pdo_fetchall("SELECT * FROM ".tablename('amouse_wxapp_card') . "  WHERE uniacid ={$weid} $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('amouse_wxapp_card') . "   WHERE uniacid ={$weid}  $condition");
    $pager = pagination($total, $pindex, $psize);
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
} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $item = pdo_fetch("SELECT *  FROM " . tablename('amouse_wxapp_card') . " WHERE id =:id AND uniacid=:weid limit 1", array(":id" => $id, ":weid" => $weid));
        $piclist = array();
        $piclist1 = unserialize($item['imgs']);
        if (is_array($piclist1)) {
            foreach ($piclist1 as $key => $p) {
                $piclist[] = is_array($p) ? $p['attachment'] : tomedia($p);
            }
        }

        $item['createtime'] = $item['createtime'] == 0 ? time() : $item['createtime'];
        $pcate = $item['categoryId'];
        $ccate = $item['child_cate_id'];
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
        $data2['status'] = $_GPC['status'];
        $data2['audit_status'] = $_GPC['audit_status'];
        $data2['weixinImg'] = $_GPC['weixinImg'];
        $data2['address'] = $_GPC['address'];
        $data2['lat'] = trim($_GPC['lat']);
        $data2['lng'] = trim($_GPC['lng']);
        $data2['categoryId'] = intval($_GPC['category']['parentid']);
        $data2['child_cate_id'] = intval($_GPC['category']['childid']);
        $data2['desc'] = htmlspecialchars_decode($_GPC['desc'], ENT_QUOTES);
        if (!empty($id)) {
            pdo_update('amouse_wxapp_card', $data2, array('id' => $id));
        } else {
            pdo_insert('amouse_wxapp_card', $data2);
            $id = pdo_insertid();
        }
        message('更新信息成功！', $this->createWebUrl('cards', array('op' => 'display')), 'success');
    }

} elseif ($operation == 'delete') {
    $id = intval($_GPC['id']);
    $order = pdo_fetch("SELECT id  FROM " . tablename('amouse_wxapp_card') . " WHERE id = $id AND uniacid=" . $weid);
    if (empty($order)) {
        message('抱歉，记录不存在或者已经删除！', $this->createWebUrl('cards', array('op' => 'display')), 'error');
    }
    pdo_delete('amouse_wxapp_card', array('id' => $id));

    message('删除成功！', $this->createWebUrl('cards', array('op' => 'display')), 'success');
} elseif ($operation == 'clear') {
    $id = intval($_GPC['id']);
    $order = pdo_fetch("SELECT * FROM " . tablename('amouse_wxapp_card') . " WHERE id = $id AND uniacid=" . $weid);
    load()->func('file');
    if (!empty($order)) {
        if ($_W['setting']['remote']['type']) {
            file_remote_delete($order['qrcode']);
        } else {
            file_delete($order['qrcode']);
        }
    }
    $sql = "update  " . tablename('amouse_wxapp_card') . "  set `qrcode`='' where `id`='$id' ";
    pdo_query($sql);
    message('清除二维码成功！', $this->createWebUrl('cards', array('op' => 'display')), 'success');
}elseif($operation=='smsall'){
    $id = intval($_GPC['id']);
    $card = pdo_fetch("SELECT `username`,`mobile`,`id` FROM " . tablename('amouse_wxapp_card') . " WHERE id = $id AND uniacid=" . $weid);
    include_once IA_ROOT . '/addons/amouse_wxapp_card/AliyunSms.class.php';
    $sms = new \AliyunSms();
    $username= $card['username'];
    $set = pdo_fetch("SELECT `sms_type`,`sms_user`,`sms_secret`,`sms_free_sign_name`,`notice_sms_code` FROM " . tablename('amouse_wxapp_sysset') . " WHERE `uniacid`= :weid  limit 1 ", array(':weid' => $weid));
    $sms_param = "{\"uname\":\"$username\"}";
    if ($set['sms_type'] == 1) {
        $acsResponse =$sms->_sendNewDySms($card['mobile'], $set['sms_user'], $set['sms_secret'], $set['sms_free_sign_name'], $set['notice_sms_code'], $sms_param, $code);
    } else { //阿里大于老接口
        $acsResponse = $sms->_sendAliDaYuSms($card['mobile'], $set['sms_user'], $set['sms_secret'], $set['sms_free_sign_name'], $set['notice_sms_code'], $sms_param);
    }
    message('发送短信成功！', $this->createWebUrl('cards', array('op' => 'display')), 'success');
} elseif ($operation == 'clearall') {
    $list = pdo_fetchall("SELECT * FROM " . tablename('amouse_wxapp_card') . " WHERE `uniacid`=" . $weid);
    load()->func('file');
    if (!empty($list)) {
        foreach ($list as $key => $card) {
            if ($_W['setting']['remote']['type']) {
                file_remote_delete($card['qrcode']);
                file_remote_delete($card['qrcode2']);
            } else {
                file_delete($card['qrcode']);
                file_delete($card['qrcode2']);
            }
            $sql = "update  " . tablename('amouse_wxapp_card') . "  set `qrcode`='' , `qrcode2`='' where `id`= " . $card['id'];
            pdo_query($sql);
        }
    }
    message('清除所有二维码成功！', $this->createWebUrl('cards', array('op' => 'display')), 'success');
} elseif ($operation == 'export_submit') {
    $sql = "SELECT `id`,`openid`,`mobile`,`categoryId`,`username`,`email`,`weixin`,`company`,`job`,`createtime` FROM" . tablename('amouse_wxapp_card') . " WHERE uniacid = :uniacid ";
    $cards = pdo_fetchall($sql, array(':uniacid' => $weid));
    $html = wxapp_house_export_parse($cards);
    header("Content-type:text/csv");
    header("Content-Disposition:attachment; filename=名片信息.csv");
    echo $html;
    exit();

}elseif ($operation == 'setstatus') {
    $id = intval($_GPC['id']);
    $data = intval($_GPC['data']);
    $type = $_GPC['type'];
    $data = ($data == 1 ? '0' : '1');
    pdo_update('amouse_wxapp_card', array($type => $data), array("id" => $id, "uniacid" => $_W['uniacid']));
    die(json_encode(array( 'result' => 1, 'data' => $data )));

}
include $this->template('web/card');


function wxapp_house_export_parse($cards) {
    if (empty($cards)) {
        return false;
    }
    $header = array(
        'id' => 'ID', 'openid' => 'openid', 'mobile' => '手机号', 'categoryId' => '分类',
        'username' => '用户名称', 'email' => '邮箱',
        'weixin' => '微信号', 'company' => '公司', 'job' => '职位','createtime'=>'注册时间'
    );
    $keys = array_keys($header);
    $html = "\xEF\xBB\xBF";
    foreach ($header as $li) {
        $html .= $li . "\t ,";
    }
    $html .= "\n";
    $count = count($cards);
    $pagesize = ceil($count / 5000);
    for ($j = 1; $j <= $pagesize; $j++) {
        $list = array_slice($cards, ($j - 1) * 5000, 5000);
        if (!empty($list)) {
            $size = ceil(count($list) / 500);
            for ($i = 0; $i < $size; $i++) {
                $buffer = array_slice($list, $i * 500, 500);
                $user = array();
                foreach ($buffer as $row) {
                    $area = pdo_fetchcolumn("SELECT name FROM " . tablename('amouse_wxapp_category') . " WHERE id=:id  limit 1", array(":id" => $row['categoryId']));
                    $row['categoryId'] = $area;
                    $row['createtime'] = date('Y-m-d H:i:s', $row['createtime']);
                    foreach ($keys as $key) {
                        $data[] = $row[$key];
                    }
                    $user[] = implode("\t ,", $data) . "\t ,";
                    unset($data);
                }
                $html .= implode("\n", $user) . "\n";
            }
        }
    }
    return $html;
}