<?php
session_start();
require('dbconnect.php');

$st = $db->prepare('SELECT * FROM posts WHERE id=?');
$st->execute(array($_REQUEST['id']));
$post = $st->fetch();

// 押下するいいねボタンの元ツイートのidを取得
$st = $db->prepare('SELECT * FROM posts WHERE id=?');
$st->execute(array($post['id']));
$orgid = (int)$st->fetch()['original_post_id'];

// 押下するいいねボタンの元ツイートをいいね済か否か確認
$st = $db->prepare('SELECT * FROM fav f, posts p WHERE f.post_id=p.original_post_id AND f.member_id=? AND p.id=? AND p.id=p.original_post_id');
$st->execute(array($_SESSION['id'], $orgid));
$fav = $st->fetch();
if ($fav['fav_flg'] !== NULL) {
	$flg = (int)($fav['fav_flg']);
}

if ($flg === 1) {
	$st = $db->prepare('DELETE FROM fav WHERE post_id=? AND member_id=?');
	$st->execute(array($orgid, $_SESSION['id']));
} elseif ($flg === NULL) {
	$st = $db->prepare('INSERT INTO fav SET post_id=?, member_id=?, fav_flg=1');
	$st->execute(array($orgid, $_SESSION['id']));
}

header('Location: index.php'); exit();

?>
