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
?>