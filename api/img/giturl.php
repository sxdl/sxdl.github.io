<?php

function get_curl($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_URL, $url);
	$response = curl_exec($ch);
	curl_close($ch);
	//-------请求为空
	if (empty($response)) {
		return false;
	}
	return $response;
}

$APIname = "QCAPI-Github-Wallpaper";
//此处填写API名称
$Block_IP = "on";
//开启恶意IP拦截功能则填写on,反之填写off
$FilePath = "./Main/pc-img-github-202007127812560.txt";
//资源文件路径

if (!file_exists("$FilePath")) {
	die("<strong>Warning:</strong>资源文件不存在或路径名称填写错误，" . $APIname . " 运行出错位置 file:" . basename(__FILE__) . " on line " . __LINE__ . ' (' . $_SERVER['SERVER_NAME'] . ')');
} else {
	$giturlArr = file($FilePath);
	//读取资源文件
}

$giturlData = [];
//将资源文件写入数组
foreach ($giturlArr as $key => $value) {
	$value = trim($value);
	if (!empty($value)) {
		$giturlData[] = trim($value);
	}
}

//反爬虫,反扫描器模块
//获取用户UA信息
$UserUA = $_SERVER['HTTP_USER_AGENT'];

//将恶意USER_AGENT存入数组
$BAN_UA = array("FeedDemon", "BOT/0.1 (BOT for JCE)", "CrawlDaddy", "Java", "Feedly", "UniversalFeedParser", "ApacheBench", "Swiftbot", "ZmEu", "Indy Library", "oBot", "jaunty", "YandexBot", "AhrefsBot", "MJ12bot", "WinHttp", "EasouSpider", "HttpClient", "Microsoft URL Control", "YYSpider", "Python-urllib", "lightDeckReports Bot", "HTTrack ", "Apache-HttpClient", "Audit ", "DirBuster", "Pangolin", "Nmap", "sqln", "Hydra", "Parser", "Libwww", "BBBike", "sqlmap", "w3af", "OWASP", "Nikto", "Fimap", "Havij", "BabyKrokodil", "Netsparker", "httperf");


function is_BAN_UA($val)
{
	$UserUA = $_SERVER['HTTP_USER_AGENT'];
	return stripos($UserUA, $val);
}

$is_BAN_UA_Arr = array_filter($BAN_UA, "is_BAN_UA");

//禁止空USER_AGENT
if (!$UserUA) {
	header("Content-type: text/html; charset=utf-8");
	die('您的访问USER_AGENT被系统判定空，已被安全模块拦截！' . '(' . $_SERVER['SERVER_NAME'] . ')');
} else {
	//判断是否为恶意UA
	if (count($is_BAN_UA_Arr)) {
		header("Content-type: text/html; charset=utf-8");
		die('您的访问USER_AGENT被系统判定为恶意用户，已被安全模块拦截！' . '(' . $_SERVER['SERVER_NAME'] . ')');
	}
}

//禁止恶意IP地址访问(调用Kos工具箱恶意IP黑名单) 
if ($Block_IP == 'on') {
	$UserIP = $_SERVER["REMOTE_ADDR"];
	//$UserIP = $_SERVER["HTTP_CF_CONNECTING_IP"]; //Cloudflare CDN获取用户真实IP（若可传递访客真实IP无视）
	$BanIP = "http://cloudcc.kostool.cn/kos-defense-cc-attack/KosCcBlackIP.txt";

	$KosCcBlackIP_path = "./Main/KosCcBlackIP.txt";
	//缓存文件不存在或者超过七天就重新更新一次
	if (!file_exists($KosCcBlackIP_path) || floor((time() - filemtime($KosCcBlackIP_path)) / 86400) > 7) {
		$BanIPData = get_curl($BanIP);
		file_put_contents($KosCcBlackIP_path, $BanIPData);
	} else {
		$BanIPData = file_get_contents($KosCcBlackIP_path);
	}

	if (!$BanIPData) {
		die("<strong>Warning:</strong>资源文件不存在或路径名称填写错误，" . $APIname . " 运行出错位置 file:" . basename(__FILE__) . " on line " . __LINE__ . ' (' . $_SERVER['SERVER_NAME'] . ')');
	} else {
		if (stripos($BanIPData, $UserIP)) {
			http_response_code('444');
			header("HTTP/1.1 444 Bad Request");
			die();
		}
	}
}

//随机输出一张
$randKey = rand(0, count($giturlData));
$imageUrl = $giturlData[$randKey];
//随机输出十张
$randKeys = array_rand($giturlData, 10);
$imageUrls = [];
foreach ($randKeys as $key) {
	$imageUrls[] = $giturlData[$key];
}
//json格式
$json = array(
	"server" => "$APIname",
	"code" => "200",
	"type" => "image"
);
$returnType = $_GET['return'];
switch ($returnType) {
	case 'url':
		echo $imageUrl;
		echo "<br>";
		echo "200OK-" . $_SERVER['SERVER_NAME'];
		echo "<br>";
		echo "Get Information Success from " . $APIname;
		break;

	case 'img':
		$img = file_get_contents($imageUrl, true);
		header("Content-Type: image/jpeg;");
		echo $img;
		break;

	case 'urlpro':
		foreach ($imageUrls as $imgUrl) {
			echo $imgUrl;
			echo '<br>';
		}
		echo "200OK-" . $_SERVER['SERVER_NAME'];
		echo "<br>";
		echo "Get Information Success from " . $APIname;
		break;

	case 'jsonpro':
		header('Content-type:text/json');
		$json['acgUrls'] = $imageUrls;
		echo json_encode($json);
		break;

	case 'json':
		$json['acgUrl'] = $imageUrl;
		$imageInfo = getimagesize($imageUrl);
		$json['width'] = "$imageInfo[0]";
		$json['height'] = "$imageInfo[1]";
		header('Content-type:text/json');
		echo json_encode($json);
		break;
		
	default:
		header("Location:" . $imageUrl);
		break;
}

//统计API调用次数
//@session_start();  //若访问压力大可尝试同一访客不重复记录,删去该行前//注释符即可
$Count = file_get_contents("./Main/count.txt");
//读取数据文件
if (!$_SESSION['#']) {
	$_SESSION['#'] = true;
	$Count++;
	//刷新一次+1
	$ApiTimes = fopen("./Main/count.txt", "w");
	//以写入的方式，打开文件，并赋值给变量ApiTimes
	fwrite($ApiTimes, $Count);
	//将变量ApiTimes的值+1
	fclose($ApiTimes);
}
