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
?>