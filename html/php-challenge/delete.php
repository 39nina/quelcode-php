<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id'])) {
	$id = $_REQUEST['id'];

	// 投稿を検査する
	$messages = $db->prepare('SELECT * FROM posts WHERE id=?');
	$messages->bindParam(1, $id, PDO::PARAM_INT);
	$messages->execute();
	$message = $messages->fetch();

	if ($message['member_id'] === $_SESSION['id']) {
		// postsTBLからRT含めて当該投稿を削除
		$st = $db->prepare('DELETE FROM posts WHERE original_post_id=?');
		$st->bindParam(1, $message['original_post_id'], PDO::PARAM_INT);
		$st->execute();
		// rtTBLからもデータ削除
		$st = $db->prepare('DELETE FROM rt WHERE original_post_id=?');
		$st->bindParam(1, $message['original_post_id'], PDO::PARAM_INT);
		$st->execute();
	}
}

header('Location: index.php'); exit();
?>
