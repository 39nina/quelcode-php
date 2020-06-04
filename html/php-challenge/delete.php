<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id'])) {
	$id = $_REQUEST['id'];

	// 投稿を検査する
	$messages = $db->prepare('SELECT * FROM posts WHERE id=?');
	$messages->execute(array($id));
	$message = $messages->fetch();

	if ($message['member_id'] == $_SESSION['id']) {
		// 削除する
		$st = $db->prepare('SELECT * FROM posts WHERE id=?');
		$st->execute(array($id));
		$org = $st->fetch();
		$del = $db->prepare('UPDATE posts SET delete_flg=1 WHERE original_post_id=?');
		$del->execute(array($org['original_post_id']));
		// rtTBLのrt_flgも削除
		$st = $db->prepare('UPDATE rt SET rt_flg=0 WHERE original_post_id');
		$st->execute(array($org['original_post_id']));
	}
}

header('Location: index.php'); exit();
?>
