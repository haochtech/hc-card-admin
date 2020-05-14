<?php

if (!defined("IN_IA")) {
	exit("Access Denied");
}
if (!function_exists("redis")) {
	function redis()
	{
		global $_W;
		static $redis;
		if (is_null($redis)) {
			if (!extension_loaded("redis")) {
				return error(-1, "PHP 未安装 redis 扩展");
			}
			if (!isset($_W["config"]["setting"]["redis"])) {
				return error(-1, "未配置 redis, 请检查 data/config.php 中参数设置");
			}
			$config = $_W["config"]["setting"]["redis"];
			if (empty($config["server"])) {
				$config["server"] = "127.0.0.1";
			}
			if (empty($config["port"])) {
				$config["port"] = "6379";
			}
			$redis_temp = new Redis();
			if ($config["pconnect"]) {
				$connect = $redis_temp->pconnect($config["server"], $config["port"], $config["timeout"]);
			} else {
				$connect = $redis_temp->connect($config["server"], $config["port"], $config["timeout"]);
			}
			if (!$connect) {
				return error(-1, "redis 连接失败, 请检查 data/config.php 中参数设置");
			}
			if (!empty($config["requirepass"])) {
				$redis_temp->auth($config["requirepass"]);
			}
			try {
				$ping = $redis_temp->ping();
			} catch (ErrorException $e) {
				return error(-1, "redis 无法正常工作，请检查 redis 服务");
			}
			if ($ping != "+PONG") {
				return error(-1, "redis 无法正常工作，请检查 redis 服务");
			}
			$redis = $redis_temp;
		}
		return $redis;
	}
}
if (!function_exists("logg")) {
	function logg($name, $data)
	{
		global $_W;
		$data = is_array($data) ? json_encode($data, JSON_UNESCAPED_UNICODE) : $data;
		file_put_contents(IA_ROOT . "/" . $name, $data);
	}
}