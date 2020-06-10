<?php
session_start();
require('dbconnect.php');

$st = $db->prepare('SELECT * FROM rt WHERE member_id=? AND post_id=?');
$st->bindParam(1, $_SESSION['id'], PDO::PARAM_INT);
$st->bindParam(2, $_REQUEST['id'], PDO::PARAM_INT);
$st->execute();
$rt = $st->fetch();
if ($rt['rt_flg'] != '') {
	$flg = (int)($rt['rt_flg']);
}

$st = $db->prepare('SELECT * FROM posts WHERE id=?');
$st->bindParam(1, $_REQUEST['id'], PDO::PARAM_INT);
$st->execute();
$post = $st->fetch();

if ($flg === 1) {
	//RT先を押下してRTを取り消す場合
	$st = $db->prepare('DELETE FROM rt WHERE post_id=? AND member_id=?');
	$st->bindParam(1, $post['id'], PDO::PARAM_INT);
	$st->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
	$st->execute();
	$st = $db->prepare('DELETE FROM posts WHERE id=?');
	$st->bindParam(1, $post['id'], PDO::PARAM_INT);
	$st->execute();

} elseif ($flg === NULL) {
	//押下するpostのRT先の情報を取得する変数：$copy
	$st = $db->prepare('SELECT * FROM posts WHERE original_post_id=? AND post_member_id=? AND id<>original_post_id');
	$st->bindParam(1, $post['original_post_id'], PDO::PARAM_INT);
	$st->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
	$st->execute();
	$copy = $st->fetch();

	//押下するpostのrtTBLの情報を取得する変数：$search
	$st = $db->prepare('SELECT * FROM rt WHERE post_id=?');
	$st->bindParam(1, $post['id'], PDO::PARAM_INT);
	$st->execute();
	$search = $st->fetch();

	//押下するpostのoriginal_post_idを取得する変数：$storg
	$st = $db->prepare('SELECT * FROM posts WHERE id=?');
	$st->bindParam(1, $post['id'], PDO::PARAM_INT);
	$st->execute();
	$rtorg = $st->fetch()['original_post_id'];

	if ((int)$copy['id'] !== (int)$copy['original_post_id']) {
	//RT元のボタンでRTを取り消す場合
		$st = $db->prepare('DELETE FROM posts WHERE id=?');
		$st->bindParam(1, $copy['id'], PDO::PARAM_INT);
		$st->execute();
		$st = $db->prepare('DELETE FROM rt WHERE post_id=?');
		$st->bindParam(1, $copy['id'], PDO::PARAM_INT);
		$st->execute();

	} elseif ((int)$search['post_id'] !== (int)$search['original_post_id']) {
	//他のログイン者のRTをRTする場合
		//postTBLにRT分の投稿追加
		$st = $db->prepare('INSERT INTO posts SET message=?, member_id=?, post_member_id=?,reply_post_id=0, original_post_id=?, rt=1, created=?');
		$st->bindParam(1, $post['message']);
		$st->bindParam(2, $post['member_id'], PDO::PARAM_INT);
		$st->bindParam(3, $_SESSION['id'], PDO::PARAM_INT);
		$st->bindParam(4, $rtorg, PDO::PARAM_INT);
		$st->bindParam(5, $post['created']);
		$st->execute();
		//rtTBLにRT分のデータ追加
			//押下するpostの自分がRTした分のidを取得する変数：$check
			$st = $db->prepare('SELECT * FROM posts WHERE original_post_id=? AND post_member_id=? AND id<>original_post_id');
			$st->bindParam(1, $rtorg, PDO::PARAM_INT);
			$st->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
			$st->execute();
			$check = $st->fetch()['id'];
		$st = $db->prepare('INSERT INTO rt SET post_id=?, original_post_id=?, member_id=?, rt_flg=1');
		$st->bindParam(1, $check, PDO::PARAM_INT);
		$st->bindParam(2, $rtorg, PDO::PARAM_INT);
		$st->bindParam(3, $_SESSION['id'], PDO::PARAM_INT);
		$st->execute();

	} else {
	//押下時点でログイン者がRTしていない状態でRTする場合
		//postTBLにRT分の投稿追加
		$st = $db->prepare('INSERT INTO posts SET message=?, member_id=?, post_member_id=?,reply_post_id=0, original_post_id=?, rt=1, created=?');
		$st->bindParam(1, $post['message']);
		$st->bindParam(2, $post['member_id'], PDO::PARAM_INT);
		$st->bindParam(3, $_SESSION['id'], PDO::PARAM_INT);
		$st->bindParam(4, $post['id'], PDO::PARAM_INT);
		$st->bindParam(5, $post['created']);
		$st->execute();
		//rtTBLにデータ追加
		$st = $db->prepare('SELECT * FROM posts WHERE original_post_id=? AND post_member_id=? AND rt=1');
		$st->bindParam(1, $_REQUEST['id'], PDO::PARAM_INT);
		$st->bindParam(2, $_SESSION['id'], PDO::PARAM_INT);
		$st->execute();
		$putRt = $st->fetch();
		$st = $db->prepare('INSERT INTO rt SET post_id=?, original_post_id=?, member_id=?, rt_flg=1');
		$st->bindParam(1, $putRt['id'], PDO::PARAM_INT);
		$st->bindParam(2, $post['id'], PDO::PARAM_INT);
		$st->bindParam(3, $_SESSION['id'], PDO::PARAM_INT);
		$st->execute();
	}
}
header('Location: index.php'); exit();

?>
