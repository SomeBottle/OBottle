<?php 
header("Content-type: text/html; charset=utf-8");
error_reporting(E_ALL^E_NOTICE^E_WARNING);
session_start();
date_default_timezone_set("Asia/Shanghai");
$daten=date('Ymd');
$act=$_GET['t'];
$cof=$_GET['c'];
$edit=$_GET['e'];
$ptitle='';
$pcontent='';
$pdat='';
$tag='';
if($_SESSION['log']!=='yes'){
	header('Location: index.php');
	session_write_close();
	exit();
}
if(!is_numeric($edit)){$edit='';};
if(file_exists('./../p/'.$edit.'.php')){
	require './../p/'.$edit.'.php';
}else{
	$edit='';
}
function checkc(){
	global $edit;
	if($edit!==''){
require './../p/index.php';
$tops=explode(',',$tp);
if(in_array($edit,$tops)){
	return true;
}
	}
}
if($act=='out'){
	session_destroy();
	header('Location: index.php');
}else if($act=='del'){/*删除文章*/
	if($cof=='yes'){
	if(file_exists('./../p/index.php')){
	require './../p/index.php';
	if(array_key_exists($edit,$in)){
		unset($in[$edit]);
		unset($tagi[$edit]);
		/*删除置顶残留*/
		$tops=explode(',',$tp);
			if(in_array($edit,$tops)){
				$newtp='';
				foreach($tops as $key=>$val){
					if(intval($val)==intval($edit)){
						unset($tops[$key]);
					}
				}
				foreach($tops as $val){
					if(!empty($val)){
						$newtp=$newtp.$val.',';
					}
				}
				$rtp=preg_replace("/\t|,/",'',$newtp);
				if(empty($rtp)){
					$newtp='';
				}
				$tp=$newtp;
			}
		unlink('./../p/'.$edit.'.php');
		file_put_contents('./../p/index.php','<?php $inn='.$inn.';$in='.var_export($in,true).';$tp=\''.$tp.'\';$tagi='.var_export($tagi,true).';?>');
		echo "<script>window.open('edit.php','_self');</script>";
	}else{
		echo "<script>alert('该文章被吃了O_o');window.open('edit.php','_self');</script>";
	}
	}else{
		echo "<script>alert('你还没有任何文章');window.open('edit.php','_self');</script>";
	}
	}else{
		echo "<script>if(confirm('真的要删除这篇文章吗？！')){window.open('?e=".$edit."&t=del&c=yes','_self');}else{window.open('edit.php?e=".$edit."','_self');};</script>";
	}
	exit();
}
session_write_close();
?>
<html>
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1">
	  <link href="./../c/m.css" rel="stylesheet">
	  <script src="https://cdn.bootcss.com/pagedown/1.0/Markdown.Converter.min.js"></script>
	 <style>
body{
font-family:'\5FAE\8F6F\96C5\9ED1';
margin:0 auto;
}
.input {
	font-family:'\5FAE\8F6F\96C5\9ED1';
	width:100%;
	max-width:500px;
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
.container {
	font-family: '\5FAE\8F6F\96C5\9ED1';
	text-align: center;
}
body {
	margin: 0 auto;
	max-width: 40em;
	overflow-x: hidden;
}
.input:focus {
	border-color: #66afe9;
	outline: 0;
	-webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6);
	box-shadow: inset 0 1px 1px rgba(0,0,0,.075),0 0 8px rgba(102,175,233,.6)
}
.area{font-family:'\5FAE\8F6F\96C5\9ED1';background:#ffffff;border-bottom-color:#ff6633;   border-bottom-width:0px;border-top-width:0px;border-left-width:0px;border-right-width:0px;   solid   #ff6633;   color:   #000000;   FONT-SIZE:   20px;   FONT-STYLE:   normal;   FONT-VARIANT:   normal;   FONT-WEIGHT:   normal;   HEIGHT:   18px;   LINE-HEIGHT:   normal;resize: vertical;width:100%;max-width:500px;height:25%;}   
.tagi{border-top-width:0px;border-left-width:0px;border-right-width:0px;   solid   #ff6633;}
.s{width:100%;max-width:500px;text-align:left;margin:0 auto;}
a{color:grey;}
</style>
      <script src="./../c/q.js"></script>
	  <title>文章&页面</title>
	</head>
</html>
<script>var editnum<?php if(is_numeric($edit)){echo '='.$edit;}?>;</script>
<body>
<div class='container'>
<h2 id='zt'>EDIT -v-</h2>
<?php if(is_numeric($edit)){?><p><a href='edit.php' target='_self' style='color:#AAA;'>新撰写文章/页面</a>&nbsp;<a href='edit.php?e=<?php echo $edit;?>&t=del' target='_self' style='color:#AAA;'>删除这个</a></p><?php }; ?>
<p><input type='text' placeholder='标题Title' class='tagi input' name='t' id='t' value='<?php echo $ptitle;?>'></input></p>
<p><textarea rows='20' class='area' placeholder='内容Content' name='c' id='c' class='input'><?php echo $pcontent;?></textarea></p>
<p><input type='text' placeholder='日期Date/页面链接link' class='tagi input' value='<?php if(!empty($pdat)){echo $pdat;}else{echo $daten;};?>' name='d' id='d'></input></p>
<p><input type='text' placeholder='标签Tag' class='tagi input' name='a' id='a' value='<?php echo $tag;?>'></input></p>
<p class='s'><a href="javascript:void(0);" onclick='edit()' class="button button-primary button-rounded" id='btn'>(O_o)?</a><input type="checkbox" id='zd' <?php if(checkc()){echo 'checked="true"';}?>/>置顶&nbsp;&nbsp;&nbsp;<a href='javascript:void(0);' onclick='faq()'>FAQ</a></p>
<form action="https://sm.ms/api/upload" id="fileinfo" method="post"
enctype="multipart/form-data" style='display:none;'>
<input type="file" name="smfile" id="smfile" /> 
<input type="hidden" name="ssl" value="true"></input>
<input type="hidden" name="format" value="json"></input>
</form>
<input type="file" id="btn_file" style="display:none">
<p class='s'></p>
</div>
</body>
<script src='./../c/f.js'></script>
