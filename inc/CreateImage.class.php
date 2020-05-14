<?php


/*use Grafika\Grafika;*/

class CreateImage {

    public function createQrCode($param,$qrcode){
        load()->func('file');
        $path = "../attachment/images/" . $param['uniacid'] . '/' . date("Y/m/d");
        if (!is_dir($path)) {
            load()->func('file');
            mkdirs($path);
        }
        load()->func('logging');
        WeUtility::logging('---path---', var_export($path, true));
        $target_file = md5("v1.5.0&cardid={$param['cardid']}&nickname={$param['nickname']}&from_user={$param['from_user']}") . '.jpg';
        if(file_exists($path.$target_file)){
            $ret = array("code" => 0, "qr_img" => $path.$target_file );
            return $ret;
        }
        WeUtility::logging('---$target_file---', var_export($target_file, true));
//        if (empty($param['bg'])) {
//        } else {
//            $bg_file = $param['bg'];
//        }
        $qrcode_dst = IA_ROOT . '/addons/amouse_wxapp_card/style/images/card-qrcode-dst.jpg';
        $font_path = IA_ROOT . '/web/resource/fonts/st-heiti-light.ttc';

        $editor = Grafika::createEditor();
        $editor->open($qrcode_dst_e, $qrcode_dst);
        $editor->open($wxapp_qrcode, $qrcode);
        $name_size = 30;
        $name_width = 670;
        //处理换行
        $name = $this->autowrap($name_size, 0, $font_path, $param['nickname'], $name_width, 2);
        //加名称
        $editor->text($goods_qrcode, $name, $name_size, 40, 750, new Color('#333333'), $font_path, 0);
        $editor->resizeFill($goods_pic, 670, 670);
        $editor->blend($goods_qrcode, $goods_pic, 'normal', 1.0, 'top-left', 40, 40);
        //电话
        $editor->text($goods_qrcode, '电话：' . $param['mobile'], 45, 30, 910, new Color('#ff4544'), $font_path, 0);
        //公司
        $editor->text($goods_qrcode, '公司：'.$param['company'], 20, 40, 1170, new Color('#888888'), $font_path, 0);

        //调整小程序码图片
        $editor->resizeExactWidth($wxapp_qrcode, 240);
        //附加小程序码图片
        $editor->blend($goods_qrcode, $wxapp_qrcode, 'normal', 1.0, 'top-left', 470, 1040);
        //保存图片
        $editor->save($goods_qrcode, $path . $target_file, 'jpeg', 85);
        //删除临时图片
        unlink($qrcode);
        $ret = array("code" => 1, "qr_img" => $path.$target_file. '?v=' . time() );
        return $ret;
    }


    /**
     * @param integer $fontsize 字体大小
     * @param integer $angle 角度
     * @param string $fontface 字体名称
     * @param string $string 字符串
     * @param integer $width 预设宽度
     */
    private function autowrap($fontsize, $angle, $fontface, $string, $width, $max_line = null)
    {
        // 这几个变量分别是 字体大小, 角度, 字体名称, 字符串, 预设宽度
        $content = "";
        // 将字符串拆分成一个个单字 保存到数组 letter 中
        $letter = [];
        for ($i = 0; $i < mb_strlen($string, 'UTF-8'); $i++) {
            $letter[] = mb_substr($string, $i, 1,'UTF-8');
        }
        $line_count = 0;
        foreach ($letter as $l) {
            $teststr = $content . " " . $l;
            $testbox = imagettfbbox($fontsize, $angle, $fontface, $teststr);
            // 判断拼接后的字符串是否超过预设的宽度
            if (($testbox[2] > $width) && ($content !== "")) {
                $line_count++;
                if ($max_line && $line_count >= $max_line) {
                    $content = mb_substr($content, 0, -1,'UTF-8') . "...";
                    break;
                }
                $content .= "\n";
            }
            $content .= $l;
        }
        return $content;
    }

}