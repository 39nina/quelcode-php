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
		// postsTBLからRT含めて当該投稿を削除する
		// $st = $db->prepare('SELECT * FROM posts WHERE id=?');
		// $st->execute(array($id));
		// $org = $st->fetch();
		$st = $db->prepare('DELETE FROM posts WHERE original_post_id=?');
		$st->execute(array($message['original_post_id']));
		// rtTBLからもデータ削除
		$st = $db->prepare('DELETE FROM rt WHERE original_post_id=?');
		$st->execute(array($message['original_post_id']));
	}
}

header('Location: index.php'); exit();
?>
