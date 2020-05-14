<?php

function createImage($imgurl)
{
	load()->func("communication");
	$resp = ihttp_request($imgurl);
	return imagecreatefromstring($resp["content"]);
}
function createImageUrl($param, $qr_file)
{
	load()->func("file");
	$path = "../attachment/images/" . $param["uniacid"] . "/" . date("Y/m/d");
	if (!is_dir($path)) {
		load()->func("file");
		mkdirs($path);
	}
	$target_file = $path . "/qr-image-" . $param["from_user"] . rand() . ".jpg";
	if (empty($param["bg"])) {
		$bg_file = IA_ROOT . "/addons/amouse_wxapp_card/style/images/qrcode.jpg";
	} else {
		$bg_file = $param["bg"];
	}
	set_time_limit(0);
	@ini_set("memory_limit", "256M");
	$target = imagecreatetruecolor(640, 1008);
	$bg = createImage(tomedia($bg_file));
	imagecopy($target, $bg, 0, 0, 0, 0, 640, 1008);
	imagedestroy($bg);
	$data = json_decode(str_replace("&quot;", "'", $param["data"]), true);
	foreach ($data as $d) {
		$d = getRealData($d);
		if ($d["type"] == "head") {
			$avatar = preg_replace("/\\/0\$/i", "/96", $param["avatar"]);
			$target = newMergeImage($target, $d, $avatar);
		} elseif ($d["type"] == "img") {
			$target = newMergeImage($target, $d, $d["src"]);
		} elseif ($d["type"] == "qr") {
			$target = newMergeImage($target, $d, tomedia($qr_file));
		} elseif ($d["type"] == "nickname") {
			$target = mergeText($target, $d, $param["nickname"]);
		}
	}
	imagejpeg($target, $target_file);
	imagedestroy($target);
	tomedia($target_file);
	return $target_file;
}
function createCodeUnlimit($obj, $qrcode)
{
	global $_W;
	$update["qr_img"] = createImageUrl($obj, $qrcode);
	$promote_qr["qr_img"] = str_replace("../attachment/", '', $update["qr_img"]);
	$url = tomedia($update["qr_img"]);
	if (!empty($_W["setting"]["remote"]["type"])) {
		$remotestatus = file_remote_upload($promote_qr["qr_img"]);
		if (is_error($remotestatus)) {
			$ret2 = array("code" => -1, "msg" => "远程附件上传失败，请检查配置并重新上传'");
			file_delete($promote_qr["qr_img"]);
			die(json_encode($ret2));
		} else {
			file_delete($promote_qr["qr_img"]);
			$url = tomedia($promote_qr["qr_img"], false);
		}
	}
	$update["qr_img"] = $url;
	$ret = array("code" => "1", "qr_img" => $update["qr_img"]);
	return $ret;
}
function getRealData($data)
{
	$data["left"] = intval(str_replace("px", '', $data["left"])) * 2;
	$data["top"] = intval(str_replace("px", '', $data["top"])) * 2;
	$data["width"] = intval(str_replace("px", '', $data["width"])) * 2;
	$data["height"] = intval(str_replace("px", '', $data["height"])) * 2;
	$data["size"] = intval(str_replace("px", '', $data["size"])) * 2;
	$data["src"] = tomedia($data["src"]);
	return $data;
}
function newMergeImage($target, $data, $imgurl)
{
	$img = createImage($imgurl);
	$w = imagesx($img);
	$h = imagesy($img);
	imagecopyresized($target, $img, $data["left"], $data["top"], 0, 0, $data["width"], $data["height"], $w, $h);
	imagedestroy($img);
	return $target;
}
function mergeImage($bg, $qr, $out, $param)
{
	list($bgWidth, $bgHeight) = getimagesize($bg);
	list($qrWidth, $qrHeight) = getimagesize($qr);
	$bgImg = imagez($bg);
	$qrImg = imagez($qr);
	$ret = imagecopyresized($bgImg, $qrImg, $param["left"], $param["top"], 0, 0, $param["width"], $param["height"], $qrWidth, $qrHeight);
	if (!$ret) {
		return false;
	}
	ob_start();
	imagejpeg($bgImg, NULL, 100);
	$contents = ob_get_contents();
	ob_end_clean();
	imagedestroy($bgImg);
	imagedestroy($qrImg);
	$fh = fopen($out, "w+");
	fwrite($fh, $contents);
	fclose($fh);
	return true;
}
function mergeText($target, $data, $text)
{
	$font = IA_ROOT . "/web/resource/fonts/msyhbd.ttf";
	$colors = hex2rgb($data["color"]);
	$color = imagecolorallocate($target, $colors["red"], $colors["green"], $colors["blue"]);
	imagettftext($target, $data["size"], 0, $data["left"], $data["top"] + $data["size"], $color, $font, $text);
	return $target;
}
function hex2rgb($colour)
{
	if ($colour[0] == "#") {
		$colour = substr($colour, 1);
	}
	if (strlen($colour) == 6) {
		list($r, $g, $b) = array($colour[0] . $colour[1], $colour[2] . $colour[3], $colour[4] . $colour[5]);
	} elseif (strlen($colour) == 3) {
		list($r, $g, $b) = array($colour[0] . $colour[0], $colour[1] . $colour[1], $colour[2] . $colour[2]);
	} else {
		return false;
	}
	$r = hexdec($r);
	$g = hexdec($g);
	$b = hexdec($b);
	return array("red" => $r, "green" => $g, "blue" => $b);
}
function imagez($bg)
{
	$bgImg = @imagecreatefromjpeg($bg);
	if (FALSE == $bgImg) {
		$bgImg = @imagecreatefrompng($bg);
	}
	if (FALSE == $bgImg) {
		$bgImg = @imagecreatefromgif($bg);
	}
	return $bgImg;
}
function url_base64_encode($str)
{
	$str = base64_encode($str);
	$code = url_encode($str);
	return $code;
}
function url_encode($code)
{
	$code = str_replace("+", "!", $code);
	$code = str_replace("/", "*", $code);
	$code = str_replace("=", '', $code);
	return $code;
}
function url_base64_decode($code)
{
	$code = url_decode($code);
	$str = base64_decode($code);
	return $str;
}
function url_decode($code)
{
	$code = str_replace("!", "+", $code);
	$code = str_replace("*", "/", $code);
	return $code;
}
function pencode($code, $seed = "undefiend9876543210")
{
	$c = url_base64_encode($code);
	$pre = substr(md5($seed . $code), 0, 3);
	return $pre . $c;
}
function pdecode($code, $seed = "undefiend9876543210")
{
	if (empty($code) || strlen($code) <= 3) {
		return '';
	}
	$pre = substr($code, 0, 3);
	$c = substr($code, 3);
	$str = url_base64_decode($c);
	$spre = substr(md5($seed . $str), 0, 3);
	if ($spre == $pre) {
		return $str;
	} else {
		return '';
	}
}
function imagecropper($source_path, $target_width, $target_height)
{
	$source_info = getimagesize($source_path);
	$source_width = $source_info[0];
	$source_height = $source_info[1];
	$source_mime = $source_info["mime"];
	$source_ratio = $source_height / $source_width;
	$target_ratio = $target_height / $target_width;
	if ($source_ratio > $target_ratio) {
		$cropped_width = $source_width;
		$cropped_height = $source_width * $target_ratio;
		$source_x = 0;
		$source_y = ($source_height - $cropped_height) / 2;
	} else {
		if ($source_ratio < $target_ratio) {
			$cropped_width = $source_height / $target_ratio;
			$cropped_height = $source_height;
			$source_x = ($source_width - $cropped_width) / 2;
			$source_y = 0;
		} else {
			$cropped_width = $source_width;
			$cropped_height = $source_height;
			$source_x = 0;
			$source_y = 0;
		}
	}
	switch ($source_mime) {
		case "image/gif":
			$source_image = imagecreatefromgif($source_path);
			break;
		case "image/jpeg":
			$source_image = imagecreatefromjpeg($source_path);
			break;
		case "image/png":
			$source_image = imagecreatefrompng($source_path);
			break;
		default:
			return false;
			break;
	}
	$target_image = imagecreatetruecolor($target_width, $target_height);
	$cropped_image = imagecreatetruecolor($cropped_width, $cropped_height);
	imagecopy($cropped_image, $source_image, 0, 0, $source_x, $source_y, $cropped_width, $cropped_height);
	imagecopyresampled($target_image, $cropped_image, 0, 0, 0, 0, $target_width, $target_height, $cropped_width, $cropped_height);
	header("Content-Type: image/jpeg");
	imagepng($target_image);
	imagedestroy($target_image);
	imagejpeg($target_image);
	imagedestroy($source_image);
	imagedestroy($target_image);
	imagedestroy($cropped_image);
}