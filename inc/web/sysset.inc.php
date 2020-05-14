<?php
/**
 * Created by PhpStorm.
 * User: shizhongying
 * QQ : 214983937
 * Date: 7/21/15
 * Time: 09:47
 */
global $_W, $_GPC;
$weid = $_W['uniacid'];
$op = empty($_GPC['op']) ? 'base' : trim($_GPC['op']);
load()->func('tpl');
$set = pdo_fetch("select * from " . tablename('amouse_wxapp_sysset') . ' where `uniacid`=:uniacid limit 1', array(':uniacid' => $weid));
$rules = unserialize($set['rule']);
$sets  = unserialize($set['sets']);

if(!pdo_fieldexists('amouse_wxapp_sysset', 'enable')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `enable` tinyint(2) NOT NULL DEFAULT 1 COMMENT '是否官方客服' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'isshare')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `isshare` tinyint(2) NOT NULL DEFAULT 0 COMMENT '是否开启分享' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'iscreate')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `iscreate` tinyint(2) NOT NULL DEFAULT 1 COMMENT '是否开启分享' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'public_status')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `public_status` tinyint(2) NOT NULL DEFAULT 1 COMMENT '是否开启分享' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'mp')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `mp` varchar(500)  NOT NULL  COMMENT '公众号图片' ; ");
}

if(!pdo_fieldexists('amouse_wxapp_card', 'qrcode')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card')." ADD `qrcode` varchar(200) DEFAULT NULL COMMENT '二维码' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_card', 'qrcode2')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card')." ADD `qrcode2` varchar(200) DEFAULT NULL COMMENT '名片二维码' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'qqmap_ak')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `qqmap_ak` varchar(500) NOT NULL DEFAULT '' COMMENT '是否官方客服' ; ");
}
if (!pdo_fieldexists('amouse_wxapp_sysset', 'adv_day')) {
    pdo_query("ALTER TABLE  " . tablename('amouse_wxapp_sysset') . " ADD `adv_day` int(10) DEFAULT '7'  COMMENT '广告' ;");
}
if (!pdo_fieldexists('amouse_wxapp_sysset', 'adv_day')) {
    pdo_query("ALTER TABLE  " . tablename('amouse_wxapp_sysset') . " ADD `adv_fee` decimal(10,2) DEFAULT 0  COMMENT '广告费用' ;");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'exchange')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `exchange` tinyint(1) NOT NULL DEFAULT '0' COMMENT '开启分享会员' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'enter_price')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `enter_price` int(10) NOT NULL DEFAULT 0 COMMENT '入驻金额' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'share_title')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `share_title` varchar(150) NOT NULL DEFAULT '' COMMENT '分享' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'share_desc')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `share_desc` varchar(150) NOT NULL DEFAULT '' COMMENT '分享' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'share_thumb')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `share_thumb` varchar(255) NOT NULL DEFAULT '' COMMENT '分享' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'enter_price')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `enter_price` int(10) NOT NULL DEFAULT 0 COMMENT '入驻金额' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'public_price')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `public_price` int(10) NOT NULL DEFAULT 0 COMMENT '入驻金额' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'top_price')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `top_price` int(10) NOT NULL DEFAULT 0 COMMENT '入驻金额' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'collect_tpl')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `collect_tpl` varchar(255) DEFAULT NULL COMMENT '名片新增收藏模板通知' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'zan_tpl')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `zan_tpl` varchar(255) DEFAULT NULL COMMENT '名片新增点赞模板通知' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'save_tpl')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `save_tpl` varchar(255) DEFAULT NULL COMMENT '名片保存模板通知' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'wxapp_name1')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `wxapp_name1` varchar(255) NOT NULL DEFAULT '' COMMENT '分享' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'wxapp_url1')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `wxapp_url1` varchar(255) NOT NULL DEFAULT '' COMMENT '分享' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'wxapp_name2')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `wxapp_name2` varchar(255) NOT NULL DEFAULT '' COMMENT '分享' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'wxapp_url2')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `wxapp_url2` varchar(255) NOT NULL DEFAULT '' COMMENT '分享' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'public_credit')) {
    pdo_query("ALTER TABLE  ".tablename('amouse_wxapp_sysset')." ADD `public_credit` int(10) NOT NULL DEFAULT 0 COMMENT '发布积分' ;");
    pdo_query("ALTER TABLE  ".tablename('amouse_wxapp_sysset')." ADD `share_credit` int(10) NOT NULL COMMENT '分享积分' ;");
    pdo_query("ALTER TABLE  ".tablename('amouse_wxapp_sysset')." ADD `pay_credit` int(10) NOT NULL DEFAULT 0 COMMENT '支付积分' ;");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'limit_credit')) {
    pdo_query("ALTER TABLE  ".tablename('amouse_wxapp_sysset')." ADD `limit_credit` int(10) NOT NULL DEFAULT 0 COMMENT '每日限制积分' ;");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'rule')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `rule` text NOT NULL COMMENT '置顶信息'; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'isenable')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `isenable` tinyint(2) NOT NULL DEFAULT 0 COMMENT '是否官方客服'; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'appid')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `appid` varchar(25) NOT NULL COMMENT '置顶信息'; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'bgcolor')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `bgcolor` varchar(25) NOT NULL COMMENT '置顶信息'; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'indexname')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `indexname` varchar(25) NOT NULL COMMENT '自定义标题'; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'appname')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `appname` varchar(50) NOT NULL COMMENT '置顶信息'; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'notice_sms_code')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `notice_sms_code` varchar(100) NOT NULL COMMENT '短信通知模板code'; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'sets')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `sets` longtext COMMENT '后增字段' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_card', 'fromId')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card')." ADD `fromId` varchar(50) NOT NULL COMMENT '置顶信息'; ");
}
if(!pdo_fieldexists('amouse_wxapp_card', 'categoryId')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card')." ADD `categoryId` int(10) NOT NULL DEFAULT 0 COMMENT '分类id' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_card', 'vip')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card')." ADD `vip` tinyint(1) DEFAULT 0 COMMENT '0vip，1非vip'; ");
}
if(!pdo_fieldexists('amouse_wxapp_card', 'listorder')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card')." ADD `listorder` int(10) DEFAULT 0 COMMENT '0vip，1非vip'; ");
}
if(!pdo_fieldexists('amouse_wxapp_card', 'createtime')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card')." ADD `createtime` int(10) NOT NULL DEFAULT 0 COMMENT '会员时间' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_card', 'clazz')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card')." ADD `clazz` varchar(10) NOT NULL DEFAULT 'default' COMMENT '模板' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_card', 'status')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card')." ADD `status` tinyint(1) DEFAULT 0 COMMENT '0表示未审核，1表示已审核，2表示禁用' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_card', 'audit_status')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card')." ADD `audit_status` tinyint(1) DEFAULT 0 COMMENT '0表示未审核，1表示已审核，2表示禁用' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_order', 'formid')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_order')." ADD `formid` varchar(50) NOT NULL COMMENT '推送码' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_card', 'lng')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card')." ADD `lng` decimal(10,6) DEFAULT '0.000000' COMMENT '经度' ; ");
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card')." ADD `lat` decimal(10,6) DEFAULT '0.000000' COMMENT '纬度' ; ");
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card')." ADD `address` varchar(255) COMMENT '地址' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_zhandui', 'qrcode')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_zhandui')." ADD `qrcode` varchar(255)   COMMENT '小程序码' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_card_slide', 'qrcode')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card_slide')." ADD `qrcode` varchar(255)   COMMENT '小程序码' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_card_slide', 'click')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card_slide')." ADD `click` tinyint(2) NOT NULL DEFAULT 0; ");
}
if(!pdo_fieldexists('amouse_wxapp_card_slide', 'status')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card_slide')." ADD `status` tinyint(2) NOT NULL DEFAULT 0; ");
}
if(!pdo_fieldexists('amouse_wxapp_card_slide', 'appid')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card_slide')." ADD `appid` varchar(255) NOT NULL DEFAULT ''; ");
}
if(!pdo_fieldexists('amouse_wxapp_card_slide', 'endtime')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card_slide')." ADD `endtime` int(10) unsigned NOT NULL comment '广告结束时间' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_sysset', 'total_num')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_sysset')." ADD `total_num` int(10) NOT NULL DEFAULT '100' COMMENT '浏览量' ; ");
}

if (checksubmit('submit')) {
    if ($op == 'base') {
        $data2['logo'] = trim($_GPC['logo']);
        $data2['copyright'] = trim($_GPC['copyright']);
        $data2['systel'] = trim($_GPC['systel']);
        $data2['enable'] = intval($_GPC['enable']) ;
        $data2['isenable'] = intval($_GPC['isenable']) ;
        $data2['isshare'] = intval($_GPC['isshare']) ;
        $data2['iscreate'] =  intval($_GPC['iscreate']) ;
        $data2['share_thumb']  =trim($_GPC['share_thumb']);
        $data2['mp']  =trim($_GPC['mp']);
        $data2['appid']  =trim($_GPC['appid']);
        $data2['total_num']  =trim($_GPC['total_num']);
        $data2['qqmap_ak']  =trim($_GPC['qqmap_ak']);
        $data2['appname']  =trim($_GPC['appname']);
        $data2['amap_key']  =trim($_GPC['amap_key']);
        $data2['wxapp_name1']  =trim($_GPC['wxapp_name1']);
        $data2['wxapp_url1']  =trim($_GPC['wxapp_url1']);$data2['wxapp_url2']  =trim($_GPC['wxapp_url2']);
        $data2['wxapp_name2']  =trim($_GPC['wxapp_name2']);
        $data2['bgcolor']  =trim($_GPC['bgcolor']); $data2['indexname']  =trim($_GPC['indexname']);
        $data2['public_status'] = trim($_GPC['public_status']);
        $data2['exchange'] = trim($_GPC['exchange']);
        $data2['is_public'] = trim($_GPC['is_public']);
        $set2['sys'] = is_array($_GPC['sys']) ? $_GPC['sys'] : array();
    } else if ($op == 'tpl') {
        $data2['mobile_verify_status'] = trim($_GPC['mobile_verify_status']);
        $data2['sms_user'] = trim($_GPC['sms_user']);
        $data2['sms_secret'] = trim($_GPC['sms_secret']);
        $data2['sms_type'] = trim($_GPC['sms_type']);
        $data2['sms_template_code'] = trim($_GPC['sms_template_code']);
        $data2['sms_free_sign_name'] = trim($_GPC['sms_free_sign_name']);
        $data2['reg_sms_code'] = trim($_GPC['reg_sms_code']);
        $data2['notice_sms_code'] = trim($_GPC['notice_sms_code']);
        $data2['share_title'] = trim($_GPC['share_title']);
        $data2['share_desc'] = trim($_GPC['share_desc']);

        $data2['collect_tpl']  =trim($_GPC['collect_tpl']);
        $data2['zan_tpl']  =trim($_GPC['zan_tpl']);
        $data2['save_tpl'] = trim($_GPC['save_tpl']);
    }else if($op == 'credit') {
        $data2['public_price'] =  trim($_GPC['public_price']) ;
        $data2['public_credit'] = trim($_GPC['public_credit']);
        $data2['pay_credit'] = trim($_GPC['pay_credit']);
        $data2['share_credit'] = trim($_GPC['share_credit']);
        $data2['limit_credit'] = trim($_GPC['limit_credit']);
        $data2['adv_fee'] = trim($_GPC['adv_fee']);
        $data2['adv_day'] = trim($_GPC['adv_day']);
        $cs = array();
        $top_days = $_GPC['top_days'];
        $top_amouts = $_GPC['top_amounts'];
        if (is_array($top_days)) {
            foreach ($top_days as $key => $value) {
                $d = array('day' => $top_days[$key], 'amount' => $top_amouts[$key]);
                $cs[] = $d;
            }
        }
        if (!empty($cs)) {
            $_GPC['rule'] = iserializer($cs);
        }
        $data2['rule'] = trim($_GPC['rule']);
    }else if($op == 'level') {
        $set2['level'] = is_array($_GPC['level']) ? $_GPC['level'] : array();
    }

    $data2['sets']= iserializer($set2);
    if (empty($set)) {
        $data2['uniacid'] = $weid;
        pdo_insert('amouse_wxapp_sysset', $data2);
    } else {
        pdo_update('amouse_wxapp_sysset', $data2, array('uniacid' => $weid));
    }
    message('更新参数设置成功！', 'refresh');
}

if(!pdo_fieldexists('amouse_wxapp_category', 'displayorder')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_category')." ADD `displayorder` int(10) unsigned NOT NULL comment '' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_card_slide', 'openid')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card_slide')." ADD `openid` varchar(100)  NOT NULL comment '' ; ");
}
if(!pdo_fieldexists('amouse_wxapp_card_slide', 'isshow')) {
    pdo_query("ALTER TABLE ".tablename('amouse_wxapp_card_slide')." ADD `isshow` tinyint(2) NOT NULL DEFAULT 1 comment '' ; ");
}
/*if (checksubmit('confrimprint')) {
    $rnd = random(6, 1);
    require_once IA_ROOT . '/addons/amouse_wxapp_card/AliyunSms.class.php';
    $txt = "【微信验证】您的本次操作的验证码为：" . $rnd . ".十分钟内有效";
    if ($set['sms_free_sign_name'] && $set['sms_template_code']) {
        $sms = new \AliyunSms();
        $sms_param = "{\"number\":\"$rnd\"}";
        if ($set['sms_type'] == 1) {
           $result =  $sms->_sendNewDySms('15852511994', $set['sms_user'], $set['sms_secret'], $set['sms_free_sign_name'], $set['reg_sms_code'], $sms_param, $rnd);

        } else {
            $sms->_sendAliDaYuSms('17601500531', $set['sms_user'], $set['sms_secret'], $set['sms_free_sign_name'], $set['reg_sms_code'], $sms_param);
        }
    }
    message('xxxxxxxx！'.$set['sms_type'], 'refresh');
}*/
include $this->template('web/_set');