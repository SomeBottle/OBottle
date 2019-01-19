<?php
header('Content-type: application/rss+xml;charset=utf-8');
require './../p/index.php';
require './../c/f.php';
date_default_timezone_set("Asia/Shanghai");
$limit=15;
$s=1;
$ht='<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:dc="http://purl.org/dc/elements/1.1/"><channel><title>'.name().'</title><link>'.host().'</link><description>'.intro().'</description><lastBuildDate>'.date('D d M Y H:i:s ',time()).'</lastBuildDate><language>zh-CN</language>';
foreach($in as $p=>$pt){
	if($s<=$limit){
		require './../p/'.$p.'.php';
		$ts=strtotime($pt);
		$dt=date('D d M Y',$ts);
		$ht=$ht.'<item><title><![CDATA['.$ptitle.'-'.name().']]></title><pubDate>'.$dt.'</pubDate><link>'.host().'#!'.$p.'</link><category>'.$tag.'</category><description><![CDATA['.$pcontent.']]></description></item>';
	}else{
		break;
	}
	$s+=1;
}
$ht=$ht.'</channel></rss>';
echo $ht;
?>