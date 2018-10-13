<?php 
error_reporting(E_ALL^E_NOTICE^E_WARNING);
session_start();
function grc($length){
   $str = null;
   $strPol = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz7823647^&*^&*^&(^GYGdghevghwevfghfvgrf_+KD:QW{D
   NUIGH^&S%$X%#$"}{{}":":L:"';
   $max = strlen($strPol)-1;
   for($i=0;$i<$length;$i++){
    $str.=$strPol[rand(0,$max)];
   }
   return $str;
  }  
$salt=base64_encode(base64_encode(grc(64)).base64_encode(grc(64)).base64_encode(grc(64)).base64_encode(grc(64)).base64_encode(grc(64)));
function fcrypt($s){
	global $salt;
	return sha1(crypt(sha1($s),sha1(base64_encode(md5(substr($s,0,6)))).$salt));
}
$request=@explode('?',$_SERVER['REQUEST_URI'])[1];
if($_SESSION['log']!=='yes'){
if($request=='log'){
	if(!file_exists('./passport.php')){
		if(stripos($_POST['auth'],':')!==false){
		file_put_contents('./passport.php','<?php $authid=\''.fcrypt($_POST['auth']).'\';$authsalt=\''.$salt.'\';?>');
		}else{
			echo "<script>alert('请按格式来哦');</script>";
		}
	}else{
		require_once './passport.php';
		if($authid==sha1(crypt(sha1($_POST['auth']),sha1(base64_encode(md5(substr($_POST['auth'],0,6)))).$authsalt))){
			$_SESSION['log']='yes';
			header('Location: edit.php');
		}else{
			echo "<script>alert('验证错误QAQ');</script>";
		}
	}
}
}else{
	header('Location: edit.php');
}
session_write_close();
?>
<style>
body{
font-family:'\5FAE\8F6F\96C5\9ED1';
}
input {
	border: 1px solid #ccc;
	padding: 7px 0px;
	border-radius: 3px;
	padding-left: 5px;
	-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
	box-shadow: inset 0 1px 1px rgba(0,0,0,.075);
	-webkit-transition: border-color ease-in-out .15s,-webkit-box-shadow ease-in-out .15s;
	-o-transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
	transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s
}
h2{
	margin-top:30px;
}
input:focus {
	border-color: #66afe9;
	outline: 0;
	-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);
	box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6)
}
</style>
<head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <script src="./../c/jquery.min.js"></script>
	  <title>Auth.</title>
	</head>
<body>
<center>
<?php if(!file_exists('./passport.php')){?>
<h2>检测到你是第一次进入验证页面</h2>
<p>请输入[用户名:密码]格式来创建一个验证文件~</p>
<p style='font-size:20px;color:#AAA;'>示例：SomeBottle:123456</p>
<?php }else{ ?>
<h2>请输入[用户名:密码]格式以继续</h2>
<?php } ?>
<p>&nbsp;</p>
<form action='?log' method='post'>
<p><input type='<?php if(!file_exists('./passport.php')){echo 'text';}else{echo 'password';}?>' style='width:20em;' placeholder='输入后回车' name='auth'></input></p>
</form>
</center>
</body>