<?php
global $_GPC, $_W;
$weid = intval($_W['uniacid']);
load()->func('tpl');
$operation = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
if(!pdo_fieldexists('amouse_wxapp_category', 'displayorder')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_category')." ADD `displayorder` int(10) unsigned NOT NULL comment '' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_category', 'parentid')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_category')." ADD `parentid` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '上级分类ID,0为第一级' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_category', 'type')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_category')." ADD `type` tinyint(2)  NOT NULL DEFAULT '0' COMMENT '0:名片行业，1:同城分类' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'amap_key')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `amap_key` varchar(50)   COMMENT '高德地图' ; ");
}

$s="CREATE TABLE IF NOT EXISTS `ims_amouse_wxapp_info` (
  `id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `uniacid` int(11) NOT NULL COMMENT '小程序id',
  `details` text NOT NULL COMMENT '内容',
  `img` text NOT NULL COMMENT '图片',
  `user_id` int(11) NOT NULL COMMENT '用户id',
  `linkman` varchar(20) NOT NULL COMMENT '联系人',
  `mobile` varchar(20) NOT NULL COMMENT '电话',
  `hot` int(11) NOT NULL COMMENT '1.热门 2.不热门',
  `top` int(11) NOT NULL COMMENT '1.置顶 2.不置顶',
  `like` int(11) NOT NULL COMMENT '点赞数',
  `views` int(11) NOT NULL COMMENT '浏览量',
  `cate_id` int(11) NOT NULL,
  `child_cate_id` int(11) NOT NULL COMMENT '分类二id',
  `check_time` int(10) NOT NULL COMMENT '审核时间',
  `top_type` tinyint(2) NOT NULL,
  `status` int(11) NOT NULL COMMENT '1.待审核 2.通过 3拒绝',
  `money` decimal(10,2) NOT NULL,
  `createtime` int(11) NOT NULL COMMENT '发布时间'
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
";
pdo_query($s) ;

if ($operation == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize = 10;
    $list = pdo_fetchall("SELECT * FROM ".tablename('amouse_wxapp_ad') . "  WHERE uniacid ={$weid} $condition ORDER BY id DESC LIMIT " . ($pindex - 1) * $psize . ',' . $psize);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM '.tablename('amouse_wxapp_ad') . "   WHERE uniacid ={$weid}  $condition");
    $pager = pagination($total, $pindex, $psize);

} elseif ($operation == 'post') {
    $id = intval($_GPC['id']);
    if (!empty($id)) {
        $item = pdo_fetch("SELECT * FROM " . tablename('amouse_wxapp_ad') . " WHERE id ={$id} limit 1 ");
    }
    if (checksubmit('submit')) {
        $data = array('uniacid' => $_W['uniacid'],
            'title' => $_GPC['title'],
            'status' => intval($_GPC['status']),
            'type' => intval($_GPC['type']),
            'appid' => $_GPC['appid'],
            'displayorder'=> intval($_GPC['displayorder']),
            'link' => trim($_GPC['link']),
            'logo' => trim($_GPC['logo']));
        if (!empty($id)) {
            pdo_update('amouse_wxapp_ad', $data, array('id' => $id));
        } else {
            pdo_insert('amouse_wxapp_ad', $data);
            $id = pdo_insertid();
        }
        message('更新幻灯片成功！', $this->createWebUrl('adv', array('op' => 'display')), 'success');
    }
} elseif ($operation == 'delete') {
    $id = intval($_GPC['id']);
    $category = pdo_fetch("SELECT id FROM " . tablename('amouse_wxapp_ad') . " WHERE id = '$id'");
    if (empty($category)) {
        message('抱歉，幻灯片不存在或是已经被删除！', $this->createWebUrl('adv', array('op' => 'display')), 'error');
    }
    pdo_delete('amouse_wxapp_ad', array('id' => $id));
    message('幻灯片删除成功！', $this->createWebUrl('adv', array('op' => 'display')), 'success');
}

include $this->template('web/adv');