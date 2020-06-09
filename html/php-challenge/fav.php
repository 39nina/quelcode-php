<?php
session_start();
require('dbconnect.php');

$favs = $db->prepare('SELECT * FROM fav WHERE member_id=? AND post_id=?');
$favs->execute(array($_SESSION['id'],$_REQUEST['id']));
$fav = $favs->fetch();
if ($fav['fav_flg'] != '') {
	$flg = (int)($fav['fav_flg']);
}

$st = $db->prepare('SELECT * FROM fav f, posts p WHERE f.post_id=p.id AND f.post_id=? AND f.member_id=?');
$st->execute(array($_REQUEST['id'], $_SESSION['id']));
$check = $st->fetch();

if ($flg === 0) {
	$idset = $db->prepare('UPDATE fav SET fav_flg=1 WHERE fav.post_id=posts.id AND fav.post_id=? AND fav.member_id=?');
	$idset->execute(array($check['original_post_id'],$_SESSION['id']));
} elseif ($flg === 1) {
	$idset = $db->prepare('UPDATE fav SET fav_flg=0 WHERE post_id=? AND member_id=?');
	$idset->execute(array($_REQUEST['id'], $_SESSION['id']));
} elseif ($flg === NULL) {
	$insert = $db->prepare('INSERT INTO fav SET post_id=?, member_id=?, fav_flg=1');
	$insert->execute(array($check['original_post_id'], $_REQUEST['id'], $_SESSION['id']));
}

header('Location: index.php'); exit();

?>
