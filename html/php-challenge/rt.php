<?php
session_start();
require('dbconnect.php');

$rts = $db->prepare('SELECT * FROM rt WHERE member_id=? AND post_id=?');
$rts->execute(array($_SESSION['id'],$_REQUEST['id']));
$rt = $rts->fetch();
if ($rt['rt_flg'] != '') {
	$flg = (int)($rt['rt_flg']);
}

if ($flg === 0) {
	$idset = $db->prepare('UPDATE rt SET rt_flg=1 WHERE post_id=? AND member_id=?');
	$idset->execute(array($_REQUEST['id'], $_SESSION['id']));
} elseif ($flg === 1) {
	$idset = $db->prepare('UPDATE rt SET rt_flg=0 WHERE post_id=? AND member_id=?');
	$idset->execute(array($_REQUEST['id'], $_SESSION['id']));
} elseif ($flg === NULL) {
	$insert = $db->prepare('INSERT INTO rt SET post_id=?, member_id=?, rt_flg=1');
	$insert->execute(array($_REQUEST['id'], $_SESSION['id']));
}

header('Location: index.php'); exit();

?>
