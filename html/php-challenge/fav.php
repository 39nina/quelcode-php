<?php
session_start();
require('dbconnect.php');

$favs = $db->prepare('SELECT * FROM fav WHERE member_id=? AND post_id=?');
$favs->execute(array($_SESSION['id'],$_REQUEST['id']));
$fav = $favs->fetch();
if ($fav['fav_flg'] != '') {
	$flg = (int)($fav['fav_flg']);
}

if ($flg === 0) {
	$idset = $db->prepare('UPDATE fav SET fav_flg=1 WHERE post_id=? AND member_id=?');
	$idset->execute(array($_REQUEST['id'], $_SESSION['id']));
} elseif ($flg === 1) {
	$idset = $db->prepare('UPDATE fav SET fav_flg=0 WHERE post_id=? AND member_id=?');
	$idset->execute(array($_REQUEST['id'], $_SESSION['id']));
} elseif ($flg === NULL) {
	$insert = $db->prepare('INSERT INTO fav SET post_id=?, member_id=?, fav_flg=1');
	$insert->execute(array($_REQUEST['id'], $_SESSION['id']));
}

header('Location: index.php'); exit();

?>
