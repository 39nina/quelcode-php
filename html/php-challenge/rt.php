<?php
session_start();
require('dbconnect.php');

$st = $db->prepare('SELECT * FROM rt WHERE member_id=? AND post_id=?');
$st->execute(array($_SESSION['id'],$_REQUEST['id']));
$rt = $st->fetch();
if ($rt['rt_flg'] != '') {
	$flg = (int)($rt['rt_flg']);
}

$st = $db->prepare('SELECT * FROM posts WHERE id=?');
$st->execute(array($_REQUEST['id']));
$post = $st->fetch();

//RT元のidからRT先のポスト一式を取得する変数($copy)
$st = $db->prepare('SELECT * FROM posts WHERE original_post_id=? AND id<>original_post_id');
$st->execute(array($_REQUEST['id']));
$copy = $st->fetch();

if ($flg === 0) {
	$st = $db->prepare('SELECT * FROM posts WHERE original_post_id=? AND id<>?');
	$st->execute(array($post['id'], $post['id']));
	$check = $st->fetch();
	//1回以上RT済で取消された状態の場合(OK!)
	if ((int)$check['delete_flg'] === 1) {
		$st = $db->prepare('UPDATE rt SET rt_flg=1 WHERE original_post_id=? AND post_id<>? AND member_id=?');
		$st->execute(array($_REQUEST['id'], $_REQUEST['id'], $_SESSION['id']));
		$st2 = $db->prepare('UPDATE posts SET rt=1, delete_flg=0 WHERE original_post_id=?AND id<>?');
		$st2->execute(array($post['id'], $post['id']));
	} else {
	//RTされた状態で、RT元のボタンでRTを取り消す時(OK!)
		$st = $db->prepare('UPDATE rt SET rt_flg=0 WHERE original_post_id=? AND post_id<>? AND member_id=?');
		$st->execute(array($_REQUEST['id'], $_REQUEST['id'], $_SESSION['id']));
		$st2 = $db->prepare('UPDATE posts SET rt=0, delete_flg=1 WHERE original_post_id=? AND id<>?');
		$st2->execute(array($post['id'], $post['id']));
	}

} elseif ($flg === 1) {  //RTされた状態で、RT先のボタンでRTを取り消す時(OK!)
	$st = $db->prepare('UPDATE rt SET rt_flg=0 WHERE post_id=? AND member_id=?');
	$st->execute(array($_REQUEST['id'], $_SESSION['id']));
	$st2 = $db->prepare('UPDATE posts SET rt=0, delete_flg=1 WHERE id=?');
	$st2->execute(array($post['id']));

} elseif ($flg === NULL) {  //RT取消済み含め、1回もRTされていない場合(OK!)
	$st = $db->prepare('INSERT INTO posts SET message=?, member_id=?, reply_post_id=0, original_post_id=?, rt=1, delete_flg=0, created=?');
	$st->execute(array($post['message'], $post['member_id'],$post['id'], $post['created']));
	//RT元のidからRT先のポスト一式を取得する変数($copy)
	$st2 = $db->prepare('SELECT * FROM posts WHERE original_post_id=? AND id<>original_post_id');
	$st2->execute(array($_REQUEST['id']));
	$copy = $st2->fetch();

	$insert = $db->prepare('INSERT INTO rt SET post_id=?, original_post_id=?, member_id=?, rt_flg=0');
	$insert->execute(array($_REQUEST['id'], $_REQUEST['id'], $_SESSION['id']));
	$insert2 = $db->prepare('INSERT INTO rt SET post_id=?, original_post_id=?, member_id=?, rt_flg=1');
	$insert2->execute(array($copy['id'], $_REQUEST['id'], $_SESSION['id']));
}

header('Location: index.php'); exit();

?>
