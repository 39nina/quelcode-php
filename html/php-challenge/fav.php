<?php
session_start();
require('dbconnect.php');

$st = $db->prepare('SELECT * FROM posts WHERE id=?');
$st->bindParam(1, $_REQUEST['id'], PDO::PARAM_INT);
$st->execute();
$post = $st->fetch();

// 押下するいいねボタンの元ツイートのidを取得
$st = $db->prepare('SELECT * FROM posts WHERE id=?');
$st->bindParam(1, $post['id'], PDO::PARAM_INT);
$st->execute();
$orgid = $st->fetch()['original_post_id'];

// 押下するいいねボタンの元ツイートをいいね済か否か確認
$st = $db->prepare('SELECT * FROM fav f, posts p WHERE f.post_id=p.original_post_id AND f.member_id=? AND p.id=? AND p.id=p.original_post_id');
$st->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
$st->bindParam(2, $orgid, PDO::PARAM_INT);
$st->execute();
$fav = $st->fetch();
if ($fav['fav_flg'] !== NULL) {
	$flg = (int)($fav['fav_flg']);
}

if ($flg === 1) {
	$st = $db->prepare('DELETE FROM fav WHERE post_id=? AND member_id=?');
	$st->bindParam(1, $orgid, PDO::PARAM_INT);
	$st->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
	$st->execute();
} elseif ($flg === NULL) {
	$st = $db->prepare('INSERT INTO fav SET post_id=?, member_id=?, fav_flg=1');
	$st->bindParam(1, $orgid, PDO::PARAM_INT);
	$st->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
	$st->execute();
}

header('Location: index.php'); exit();

?>
