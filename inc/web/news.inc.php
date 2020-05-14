<?php
global $_GPC, $_W;
$weid = intval($_W['uniacid']);
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if ($operation == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $list = pdo_fetchall("SELECT * FROM ".tablename('amouse_wxapp_news')."  WHERE uniacid ={$weid} $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('amouse_wxapp_news') ."   WHERE uniacid ={$weid}  $condition");
    $pager = pagination($total, $pindex, $psize);

} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $item = pdo_fetch("SELECT * FROM " . tablename('amouse_wxapp_news')." WHERE id ={$id} limit 1 ");
    }
    if (checksubmit('submit')) {
        $data = array('uniacid' => $_W['uniacid'],
            'title' => $_GPC['title'],
            'status' => intval($_GPC['status']),
            'type' => intval($_GPC['type']),
            'num'=> intval($_GPC['displayorder']),
            'details' => html_entity_decode($_GPC['details']),
            'createtime' =>  time());

        if (!empty($id)) {
            pdo_update('amouse_wxapp_news', $data, array('id' => $id));
        } else {
            pdo_insert('amouse_wxapp_news', $data);
            var_dump($data);
            $id = pdo_insertid();
            var_dump($id);
        }
        message('更新公告成功！', $this->createWebUrl('news', array('op' => 'display')), 'success');
    }
} elseif ($operation == 'delete') {
    $id = intval($_GPC['id']);
    $category = pdo_fetch("SELECT id FROM " . tablename('amouse_wxapp_news') . " WHERE id = '$id'");
    if (empty($category)) {
        message('抱歉，公告不存在或是已经被删除！', $this->createWebUrl('news', array('op' => 'display')), 'error');
    }
    pdo_delete('amouse_wxapp_ad', array('id' => $id));
    message('公告删除成功！', $this->createWebUrl('news', array('op' => 'display')), 'success');
}

include $this->template('web/news');