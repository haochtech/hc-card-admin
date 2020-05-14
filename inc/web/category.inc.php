<?php
/**
 * Created by PhpStorm.
 * User: shizhongying
 * Date: 2017/12/14
 * Time: 20:46
 */
global $_GPC, $_W;
//$GLOBALS['frames'] = $this->getMainMenu();
$op = $_GPC['op'] ? $_GPC['op'] : 'display';
pdo_query("update ".tablename('amouse_wxapp_category')." set `type`=0 ; ");
$weid = $_W['uniacid'];
if ($op == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 15;
    if (!empty($_GPC['displayorder'])) {
        foreach ($_GPC['displayorder'] as $id => $displayorder) {
            $update = array('displayorder' => $displayorder);
            pdo_update('amouse_wxapp_category', $update, array('id' => $id));
        }
        message('分类排序更新成功！', 'refresh', 'success');
    }
    $condition="WHERE uniacid='{$weid}' and type=0  " ;
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND title LIKE '%" . $_GPC['keyword'] . "%'";
    }
    $children = array();
    $list = pdo_fetchall('SELECT * FROM ' . tablename('amouse_wxapp_category') .$condition." ORDER BY displayorder DESC  ") ;   //LIMIT . ($pindex - 1) * $psize . ',' . $psize); //分页
    foreach ($list as $index => $row) {
        if (!empty($row['parentid'])){
            $children[$row['parentid']][] = $row;
            unset($list[$index]);
        }
    }
} elseif ($op == 'post') {
    $id = intval($_GPC['id']);
    $parentid = intval($_GPC['parentid']);
    if ($id > 0) {
        $item = pdo_fetch('SELECT * FROM '.tablename('amouse_wxapp_category')." WHERE id=:id limit 1 ",array(':id' => $id));
    }
    if (!empty($parentid)) {
        $parent = pdo_fetch("SELECT * FROM ".tablename('amouse_wxapp_category')." WHERE id =:id limit 1 " ,array(':id' => $parentid));
        if (empty($parent)) {
            message('抱歉，上级分类不存在或是已经被删除！', $this->createWebUrl('category', array('do' => 'display')), 'error');
        }
    }
    if (checksubmit('submit')) {
        $insert = array(
            'uniacid'=>$weid,
            'name' => $_GPC['title'],
            'thumb' => $_GPC['thumb'],
            'type'=>0,
            'parentid' => intval($parentid),
            'displayorder' => intval($_GPC['displayorder']),
            'createtime' => TIMESTAMP
        );
        if (empty($id)) {
            pdo_insert('amouse_wxapp_category', $insert);
        } else {
            if (pdo_update('amouse_wxapp_category', $insert, array('id' => $id)) === false) {
                message('更新分类数据失败, 请稍后重试.', 'error');
            }
        }
        message('更新分类数据成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
    }
} elseif ($op == 'del') {
    $id = intval($_GPC['id']);
    $temp = pdo_delete("amouse_wxapp_category", array('id' => $id));
    if ($temp == false) {
        message('抱歉，删除分类数据失败！', '', 'error');
    } else {
        message('删除分类成功！', $this->createWebUrl('category', array('op' => 'display')), 'success');
    }
}elseif ($op == 'setstatus') {
    $id = intval($_GPC['id']);
    $data = intval($_GPC['data']);
    $type = $_GPC['type'];
    $data = ($data == 1 ? '0' : '1');
    $update = array($type => $data);
    pdo_update('amouse_wxapp_category', $update, array("id" => $id, "weid" => $_W['uniacid']));
    die(json_encode(array('result' => 1, 'data' => $data)));
}

include $this->template('web/category');