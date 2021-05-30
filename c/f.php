<?php
require dirname(__FILE__) . "/../a/conf.php";
function name(){
	global $blog;
	return $blog['name'];
}
function intro(){
	global $blog;
	return $blog['intro'];
}
function frontnum(){
	global $blog;
	return $blog['frontposts'];
}
function avatar(){
	global $blog;
	return $blog['avatar'];
}
function host(){
	global $blog;
	return $blog['host'];
}
function keyword(){
	global $blog;
	return $blog['keyword'];
}
function descript(){
	global $blog;
	return $blog['descript'];
}
function ctime(){
	global $blog;
	return $blog['cachetime'];
}
function changed(){/*有编辑，变更时间戳*/
	file_put_contents(dirname(__FILE__) . "/../a/change.log",time());
}
function getchangetime(){
	$p=file_get_contents(dirname(__FILE__) . "/../a/change.log");
	if(!empty($p)){
	return intval($p);
	}else{
		return 'nolog';
	}
}
function filter($c) { /*xss过滤*/
    return str_ireplace(array("\r\n", "\r", "\n"), ' <br> ', addslashes(htmlspecialchars($c, ENT_QUOTES))); /*这里换行标签<br>刻意留了空格，因为在前端识别url的时候可能会连同br一同牵连进去*/
}
?>