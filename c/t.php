<?php
date_default_timezone_set("Asia/Shanghai");
header('Content-type:text/json;charset=utf-8');
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
session_start();
if ($_SESSION['log'] !== 'yes') {
    $result['result'] = 'notok';
    $result['msg'] = '请登录.';
    echo json_encode($result, true);
    session_write_close();
    exit();
}
session_write_close();
require_once './f.php';
$type = $_GET['type'];
$t = filter($_POST['title']);
$c = urldecode($_POST['content']);
$d = filter($_POST['dat']);
$a = filter($_POST['tag']);
$edits = $_POST['editn'];
$zhiding = $_POST['ifzd'];
$fstr = '';
$result['result'] = 'ok';
function valid_date($date) { /*日期判断函数*/
    if (preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date, $parts)) {
        if (checkdate($parts[2], $parts[3], $parts[1])) return true;
        else return false;
    } else return false;
}
if ($type == 'submit') {
    if (!is_dir('./../p/')) {
        mkdir('./../p/');
    }
    if (!file_exists('./../p/index.php')) {
        $strs = '<?php $inn=0;$in=array();$tp=\'\';$tagi=array(); ?>';
        file_put_contents('./../p/index.php', $strs);
    }
    if ($edits == '' || $edits == 'undefined' || $edits == 'null') { /*新建文章*/
        if (!empty($t) && !empty($d) && !empty($c)) {
            if (empty($a)) {
                $a = '日常';
            }
            require './../p/index.php';
            $datestr = substr($d, 0, 4) . "-" . substr($d, 4, 2) . "-" . substr($d, 6, 2);
            if (valid_date($datestr)) {
                $in[$inn] = $d;
            } else {
                $in[$inn] = date('Ymd');
            }
            $tagi[$inn] = $a;
            if ($zhiding == 'yes') {
                $rtp = preg_replace("/\t|,/", '', $tp);
                if (empty($rtp)) {
                    $tp = $inn . ',';
                } else {
                    $tp = $tp . $inn . ',';
                }
            }
            arsort($in);
            $inn = $inn + 1;
            file_put_contents('./../p/index.php', '<?php $inn=' . $inn . ';$in=' . var_export($in, true) . ';$tp=\'' . $tp . '\';$tagi=' . var_export($tagi, true) . ';?>');
            if (valid_date($datestr)) {
                $fstr = '<?php $ptitle="' . $t . '";$pcontent=\'' . addslashes(htmlspecialchars($c)) . '\';$pdat="' . $d . '";$tag="' . $a . '";$ptype="post";?>';
            } else {
                $fstr = '<?php $ptitle="' . $t . '";$pcontent=\'' . addslashes(htmlspecialchars($c)) . '\';$pdat="' . $d . '";$tag="' . $a . '";$ptype="page";?>';
            }
            file_put_contents('./../p/' . ($inn - 1) . '.php', $fstr);
			changed();
            $result['pid'] = ($inn - 1);
        } else {
            $result['result'] = 'notok';
            $result['msg'] = '除了标签，其他内容不得为空.';
        }
    } else { /*编辑文章*/
        if (!empty($t) && !empty($d) && !empty($c)) {
            if (empty($a)) {
                $a = '日常';
            }
            require './../p/index.php';
            $datestr = substr($d, 0, 4) . "-" . substr($d, 4, 2) . "-" . substr($d, 6, 2);
            if (valid_date($datestr)) {
                $in[$edits] = $d;
            } else {
                $in[$edits] = $in[$edits];
            }
            $tagi[$edits] = $a;
            $tops = explode(',', $tp);
            if ($zhiding == 'yes') {
                if (!in_array($edits, $tops)) {
                    $tp = $tp . $edits . ',';
                }
            } else {
                if (in_array($edits, $tops)) {
                    $newtp = '';
                    foreach ($tops as $key => $val) {
                        if (intval($val) == intval($edits)) {
                            unset($tops[$key]);
                        }
                    }
                    foreach ($tops as $val) {
                        if (!empty($val)) {
                            $newtp = $newtp . $val . ',';
                        }
                    }
                    $rtp = preg_replace("/\t|,/", '', $newtp);
                    if (empty($rtp)) {
                        $newtp = '';
                    }
                    $tp = $newtp;
                }
            }
            arsort($in);
            file_put_contents('./../p/index.php', '<?php $inn=' . $inn . ';$in=' . var_export($in, true) . ';$tp=\'' . $tp . '\';$tagi=' . var_export($tagi, true) . ';?>');
            if (valid_date($datestr)) {
                $fstr = '<?php $ptitle="' . $t . '";$pcontent=\'' . addslashes(htmlspecialchars($c)) . '\';$pdat="' . $d . '";$tag="' . $a . '";$ptype="post";?>';
            } else {
                $fstr = '<?php $ptitle="' . $t . '";$pcontent=\'' . addslashes(htmlspecialchars($c)) . '\';$pdat="' . $d . '";$tag="' . $a . '";$ptype="page";?>';
            }
            file_put_contents('./../p/' . $edits . '.php', $fstr);
			changed();
            $result['pid'] = $edits;
        } else {
            $result['result'] = 'notok';
            $result['msg'] = '除了标签，其他内容不得为空.';
        }
    }
} else {
    $result['result'] = 'notok';
    $result['msg'] = '请求错误.';
}
echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>