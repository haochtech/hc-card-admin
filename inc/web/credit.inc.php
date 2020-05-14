<?php
/**
 * Created by PhpStorm.
 * User: shizhongying
 * * QQ : 214983937
 * Date: 7/21/15
 * Time: 09:47
 */
global $_W, $_GPC;
$weid=$_W['uniacid'];
$op = !empty($_GPC['op']) ? $_GPC['op'] : 'display';
load()->func('tpl');
if ($op == 'display') {
    $pindex = max(1, intval($_GPC['page']));
    $psize =10;
    $condition = " WHERE uniacid =$weid ";
    $params = array();
    if (!empty($_GPC['keyword'])) {
        $condition .= " AND title LIKE :keyword";
        $params[':keyword'] = "%{$_GPC['keyword']}%";
    }
    $type = $_GPC['type'];
    if ($type != '') {
        $condition .= " AND type = '" . $type. "'";
    }
 $list = pdo_fetchall("SELECT * FROM ".tablename('amouse_wxapp_creditshop_goods')."  $condition ORDER BY displayorder DESC,id DESC LIMIT
".($pindex - 1) * $psize.','.$psize, $params);
    $total = pdo_fetchcolumn('SELECT count(*)  FROM '.tablename('amouse_wxapp_creditshop_goods'). $condition , $params);
    $pager = pagination($total, $pindex, $psize);

} elseif ($op == 'post') {
    $id = intval($_GPC['id']);
    load()->func('tpl');
    load()->func('file');
    $pindex = max(1, intval($_GPC['page']));
    if ($id>0) {
        $item = pdo_fetch("SELECT * FROM ".tablename('amouse_wxapp_creditshop_goods')." WHERE id = :id" , array(':id' => $id));
        if (empty($item)) {
            message('抱歉，兑换商品不存在或是已经删除！', '', 'error');
        }
        $piclist = array();
        $piclist1 = iunserializer($item['thumb']);
        if (is_array($piclist1)) {
            foreach ($piclist1 as $key => $p) {
                $piclist[] = is_array($p) ? $p['attachment'] :tomedia($p);
            }
        }
    }else{
        $item['type']=0;
    }
  
    if (checksubmit('submit')) {
        empty($_GPC['title']) ? message('亲,兑换商品标题不能为空') : $title= $_GPC['title'];
        $data = array(
            'uniacid' => $_W['uniacid'],
            'title' => $title,
            'displayorder'=>intval($_GPC['displayorder']),
            'thumb' => iserializer($_GPC['thumb']),
            'detail' =>  decode_html_images(trim($_GPC['detail'])),
            'money'=>floatval($_GPC['money']),
            'credit'=>intval($_GPC['credit']),'credit2'=>intval($_GPC['credit2']),
            'totalday'=>$_GPC['totalday'],'stock'=>intval($_GPC['stock']),
            'status'=>trim($_GPC['status']),
            'type'=>intval($_GPC['type']),'description'=>decode_html_images(trim($_GPC['description'])),
            'endtime'=> strtotime($_GPC['endtime']),
            'createtime' => TIMESTAMP 
        );

        if (empty($id)) {
            pdo_insert('amouse_wxapp_creditshop_goods', $data);
        } else {
            pdo_update('amouse_wxapp_creditshop_goods', $data, array('id' => $id));
        }
        message('兑换商品更新成功！', $this->createWebUrl('credit', array('op' => 'display','page'=>$pindex)), 'success');
    }

}elseif($op=='delete') {

    $id = intval($_GPC['id']);
    $row = pdo_fetch("SELECT id FROM ".tablename('amouse_wxapp_creditshop_goods')." WHERE id = :id", array(':id' => $id));
    if (empty($row)) {
        message('抱歉，兑换商品不存在或是已经被删除！');
    }
    pdo_delete('amouse_wxapp_creditshop_goods', array('id' => $id));
    message('删除成功！', referer(), 'success');

} elseif ($op == 'setstatus') {
    $id   = intval($_GPC['id']);
    $data = intval($_GPC['data']);
    $data = ($data == 1 ? '0' : '1');
    pdo_update('amouse_wxapp_creditshop_goods', array('status' => $data), array( "id" => $id,"uniacid" => $_W['uniacid']));
    die(json_encode(array(
        'result' => 1,
        'data' => $data
    )));
}elseif($op=='log'){//兑换记录
    $glist = pdo_fetchall("SELECT * FROM ".tablename('amouse_wxapp_creditshop_goods')." WHERE uniacid =$weid ORDER BY displayorder DESC,id DESC " );
    $pindex = max(1, intval($_GPC['page']));
    $psize =15;
    $condition = "WHERE uniacid = {$weid}  ";
    if (!empty($_GPC['goodsid'])) {
        $cid = intval($_GPC['goodsid']);
        $condition .= " AND goodsid = $cid ";
    }
    if (!empty($_GPC['goodsids'])) {
        $goodsids = intval($_GPC['goodsids']);
        $condition .= " AND goodsid = $goodsids ";
    }

    $list = pdo_fetchall("SELECT * FROM ".tablename('amouse_wxapp_creditshop_log')." $condition ORDER BY createtime DESC LIMIT ".($pindex - 1) * $psize.','.$psize, $params);
    $total = pdo_fetchcolumn('SELECT COUNT(*) FROM ' . tablename('amouse_wxapp_creditshop_log').$condition);
    $pager = pagination($total, $pindex, $psize);
    $orders = array();
    foreach ($list as &$item) {
        $goods= pdo_fetch('SELECT * FROM '.tablename('amouse_wxapp_creditshop_goods')." WHERE id=:gid ", array(':gid' => $item['goodsid']));
        $item['title'] =$goods['title'];
        $item['credit'] = $goods['credit'];
        $item['type'] = $goods['type'];
        if($goods['type']==0){
            $item['desc']="实物商品";
        }elseif($goods['type']==1){
            $item['desc']="置顶个人名片";
        }elseif($goods['type']==2){
            $item['desc']="置顶个人微信群";
        }elseif($goods['type']==3){
            $item['desc']="置顶了个人公众号";
        }
        $member= pdo_fetch('SELECT * FROM '.tablename('amouse_board_member')." WHERE openid=:openid ", array(':openid' => $item['openid']));
        $item['nickname']=$member['nickname'];
        $orders[] = $item;
    }
    unset($item);

    if (checksubmit('submit')) {
        if (!empty($_GPC['express']) && empty($_GPC['expresssn'])) {
            message('请输入快递单号！');
        }
        $log= pdo_fetch('SELECT * FROM '.tablename('amouse_wxapp_creditshop_log')." WHERE id=:id ", array(':id' =>$_GPC['logid']));
        $goods= pdo_fetch('SELECT * FROM '.tablename('amouse_wxapp_creditshop_goods')." WHERE id=:gid ", array(':gid' => $log['goodsid']));

        pdo_update('amouse_board_creditshop_log', array(
            'status' => 1,
            'express' => trim($_GPC['express']),
            'expresscom' => trim($_GPC['expresscom']),
            'expresssn' => trim($_GPC['expresssn']),
            'usetime' => time()
        ), array('id'=>$log['id'], 'uniacid' => $_W['uniacid'] ));

        message('发货操作成功！', $this->createWebUrl('credit', array('op' => 'log')), 'success');

        load()->classs('weixin.account');
        $accObj= WeixinAccount::create($_W['acid']);
        $content = "您兑换的宝贝已经发货，订单内容".$goods['title']."，物流服务".$_GPC['expresscom']."，快递单号".$_GPC['expresssn']." \n 地址: " . $log['address'] . ' ' . "收件人: " . $log['address_name'] . "(" . $log['address_phone'] . ")";
        if(!empty($send)) {
            $msg = array(
                'first' => array(
                    'value' => "您的宝贝已经发货！",
                    "color" => "#4a5077"
                ),
                'keyword1' => array(
                    'title' => '订单内容',
                    'value' => "【" . $goods['title'] . "】" . $goods,
                    "color" => "#4a5077"
                ),
                'keyword2' => array(
                    'title' => '物流服务',
                    'value' => $_GPC['expresscom'],
                    "color" => "#4a5077"
                ),
                'keyword3' => array(
                    'title' => '快递单号',
                    'value' => $_GPC['expresssn'],
                    "color" => "#4a5077"
                ),
                'keyword4' => array(
                    'title' => '收货信息',
                    'value' => "地址: " . $log['address']. "收件人: " . $address['address_name'] . ' (' . $address['address_phone'] . ') ',
                    "color" => "#4a5077"
                ),
                'remark' => array(
                    'value' => "\r\n我们正加速送到您的手上，请您耐心等候。",
                    "color" => "#4a5077"
                )
            );

            $account->sendTplNotice($log['openid'], $send, $msg, $detailurl);
        }else{
            $account->sendCustomNotice(array(
                "touser" => trim($log['openid']),
                "msgtype" => "text",
                "text" => array(
                    'content' => urlencode($content)
                )
            ));
        }
    }
}elseif($op=='del'){

    $logid = intval($_GPC['logid']);
    $row = pdo_fetch("SELECT id FROM ".tablename('amouse_wxapp_creditshop_log')." WHERE id = :id", array(':id' => $logid));
    if (empty($row)) {
        message('抱歉，兑换记录不存在或是已经被删除！');
    }
    pdo_delete('amouse_wxapp_creditshop_log', array('id' => $logid));
    message('删除兑换记录成功！', referer(), 'success');
}
function decode_html_images($detail = ''){
    $detail = htmlspecialchars_decode($detail);
    preg_match_all("/<img.*?src=[\'| \"](.*?(?:[\.gif|\.jpg|\.png|\.jpeg]?))[\'|\"].*?[\/]?>/", $detail, $imgs);
    $images = array();
    if (isset($imgs[1])) {
        foreach ($imgs[1] as $img) {
            $im= array(
                "old" => $img,
                "new" => tomedia($img)
            );
            $images[] = $im;
        }
    }
    foreach ($images as $img) {
        $detail = str_replace($img['old'], $img['new'], $detail);
    }
    return $detail;
}
include $this->template('web/credit_shop');