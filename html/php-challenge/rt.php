<?php
session_start();
require('dbconnect.php');

$st = $db->prepare('SELECT * FROM rt WHERE member_id=? AND post_id=?');
$st->execute(array($_SESSION['id'], $_REQUEST['id']));
$rt = $st->fetch();
if ($rt['rt_flg'] != '') {
	$flg = (int)($rt['rt_flg']);
}

$st = $db->prepare('SELECT * FROM posts WHERE id=?');
$st->execute(array($_REQUEST['id']));
$post = $st->fetch();

if ($flg === 1) {
	//RT先を押下してRTを取り消す場合
	$st = $db->prepare('DELETE FROM rt WHERE post_id=? AND member_id=?');
	$st->execute(array($post['id'], $_SESSION['id']));
	$st = $db->prepare('DELETE FROM posts WHERE id=?');
	$st->execute(array($post['id']));

} elseif ($flg === NULL) {
	//押下するpostのRT先の情報を取得する変数：$copy
	$st = $db->prepare('SELECT * FROM posts WHERE original_post_id=? AND post_member_id=? AND id<>original_post_id');
	$st->execute(array($post['original_post_id'], $_SESSION['id']));
	$copy = $st->fetch();

	//押下するpostのrtTBLの情報を取得する変数：$search
	$st = $db->prepare('SELECT * FROM rt WHERE post_id=?');
	$st->execute(array($post['id']));
	$search = $st->fetch();

	//押下するpostのoriginal_post_idを取得する変数：$storg
	$st = $db->prepare('SELECT * FROM posts WHERE id=?');
	$st->execute(array($post['id']));
	$rtorg = (int)($st->fetch()['original_post_id']);

	if ((int)$copy['id'] !== (int)$copy['original_post_id']) {
	//RT元のボタンでRTを取り消す場合
		$st = $db->prepare('DELETE FROM posts WHERE id=?');
		$st->execute(array($copy['id']));
		$st = $db->prepare('DELETE FROM rt WHERE post_id=?');
		$st->execute(array($copy['id']));

	} elseif ($search['post_id'] !== $search['original_post_id']) {
	//他のログイン者のRTをRTする場合
		//postTBLにRT分の投稿追加
		$st = $db->prepare('INSERT INTO posts SET message=?, member_id=?, post_member_id=?,reply_post_id=0, original_post_id=?, rt=1, created=?');
		$st->execute(array($post['message'], $post['member_id'], $_SESSION['id'], $rtorg, $post['created']));
		//rtTBLにRT分のデータ追加
			//押下するpostの自分がRTした分のidを取得する変数：$check
			$st = $db->prepare('SELECT * FROM posts WHERE original_post_id=? AND post_member_id=? AND id<>original_post_id');
			$st->execute(array($rtorg, $_SESSION['id']));
			$check = (int)($st->fetch()['id']);
		$st = $db->prepare('INSERT INTO rt SET post_id=?, original_post_id=?, member_id=?, rt_flg=1');
		$st->execute(array($check, $rtorg, $_SESSION['id']));

	} else {
	//押下時点でログイン者がRTしていない状態でRTする場合
		//postTBLにRT分の投稿追加
		$st = $db->prepare('INSERT INTO posts SET message=?, member_id=?, post_member_id=?,reply_post_id=0, original_post_id=?, rt=1, created=?');
		$st->execute(array($post['message'], $post['member_id'], $_SESSION['id'],$post['id'], $post['created']));
		//rtTBLにデータ追加
		$st = $db->prepare('SELECT * FROM posts WHERE original_post_id=? AND post_member_id=? AND rt=1');
		$st->execute(array($_REQUEST['id'], $_SESSION['id']));
		$putRt = $st->fetch();
		$st = $db->prepare('INSERT INTO rt SET post_id=?, original_post_id=?, member_id=?, rt_flg=1');
		$st->execute(array((int)$putRt['id'], $post['id'], $_SESSION['id']));
	}
}
header('Location: index.php'); exit();

?>
