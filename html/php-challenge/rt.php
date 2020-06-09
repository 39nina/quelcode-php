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

//RT元のidからRT先のポスト一式を取得する変数($copy)
$st = $db->prepare('SELECT * FROM posts WHERE original_post_id=? AND id<>original_post_id');
$st->execute(array($_REQUEST['id']));
$copy = $st->fetch();

if ($flg === 0) {
	$st = $db->prepare('SELECT * FROM posts WHERE original_post_id=? AND id<>? AND post_member_id=?');
	$st->execute(array($post['id'], $post['id'], $_SESSION['id']));
	$check = $st->fetch();
	if ((int)$check['delete_flg'] === 1) {
	//1回以上RT済で取消したログイン者が再度元ツイートからRTする場合（OK!!!）
		$st = $db->prepare('UPDATE rt SET rt_flg=1 WHERE original_post_id=? AND post_id<>? AND member_id=?');
		$st->execute(array($_REQUEST['id'], $_REQUEST['id'], $_SESSION['id']));
		$st2 = $db->prepare('UPDATE posts SET rt=1, delete_flg=0 WHERE original_post_id=?AND id<>? AND post_member_id=?');
		$st2->execute(array($post['id'], $post['id'], $_SESSION['id']));
	} else {
	//元ツイートからRTされた状態で、RT元のボタンでRTを取り消す場合（OK!!!）
		$st = $db->prepare('SELECT post_id FROM rt WHERE original_post_id=? AND post_id<>original_post_id AND member_id=?');
		$st->execute(array($_REQUEST['id'], $_SESSION['id'],));
		$postP = $st->fetch();

		$st1 = $db->prepare('UPDATE rt SET rt_flg=0 WHERE original_post_id=? AND post_id<>original_post_id AND member_id=?');
		$st1->execute(array($_REQUEST['id'], $_SESSION['id']));
		$st2 = $db->prepare('UPDATE posts SET rt=0, delete_flg=1 WHERE original_post_id=? AND id=?');
		$st2->execute(array($_REQUEST['id'], $postP['post_id']));
	}
} elseif ($flg === 1) {
	//元ツイートからRTした状態で、RT先のボタンでRTを取り消す場合（OK!!!）
	$st = $db->prepare('UPDATE rt SET rt_flg=0 WHERE post_id=? AND member_id=?');
	$st->execute(array($post['id'], $_SESSION['id']));
	$st = $db->prepare('UPDATE posts SET rt=0, delete_flg=1 WHERE id=?');
	$st->execute(array($post['id']));

	//RTをRTした状態で、RT元のボタンで取り消す場合

} elseif ($flg === NULL) {

	$st = $db->prepare('SELECT * FROM rt WHERE post_id=?');
	$st->execute(array($post['id']));
	$search = $st->fetch();

	//押下するpostのoriginal_post_id：$storg
	$st = $db->prepare('SELECT original_post_id FROM posts WHERE id=?');
	$st->execute(array($post['id']));
	$rtorg = (int)($st->fetch()['original_post_id']);

	$st = $db->prepare('SELECT * FROM rt WHERE post_id=? AND original_post_id=? AND rt_flg=0');
	$st->execute(array($_REQUEST['id'], $_REQUEST['id']));
	$checkNull = $st->fetch()['id'];

	if ($checkNull === NULL) {
		if ($search['id'] !== $search['original_post_id']) {
		//他のアカウントがRTしたものをRTする場合
			//postTBLにRT分の投稿追加
			$st = $db->prepare('INSERT INTO posts SET message=?, member_id=?, post_member_id=?,reply_post_id=0, original_post_id=?, rt=1, delete_flg=0, created=?');
			$st->execute(array($post['message'], $post['member_id'], $_SESSION['id'], $rtorg, $post['created']));
			//rtTBLにRT分のデータ追加
				//押下するpostの自分がRTした分のid：$check
				$st = $db->prepare('SELECT * FROM posts WHERE original_post_id=? AND post_member_id=? AND id<>original_post_id');
				$st->execute(array($rtorg, $_SESSION['id']));
				$check = (int)($st->fetch()['id']);
			$st = $db->prepare('INSERT INTO rt SET post_id=?, original_post_id=?, member_id=?, rt_flg=0');
			$st->execute(array($rtorg, $rtorg, $_SESSION['id']));
			$st = $db->prepare('INSERT INTO rt SET post_id=?, original_post_id=?, member_id=?, rt_flg=1');
			$st->execute(array($check, $rtorg, $_SESSION['id']));
		} else {
	//RT取消済み含め、1回もRTされていない場合（OK!!!）
		//postTBLにRT分の投稿追加
		$st = $db->prepare('INSERT INTO posts SET message=?, member_id=?, post_member_id=?,reply_post_id=0, original_post_id=?, rt=1, delete_flg=0, created=?');
		$st->execute(array($post['message'], $post['member_id'], $_SESSION['id'],$post['id'], $post['created']));
		//rtTBLにデータ追加
		$st = $db->prepare('SELECT * FROM posts WHERE original_post_id=? AND post_member_id=? AND rt=1');
		$st->execute(array($_REQUEST['id'], $_SESSION['id']));
		$putRt = $st->fetch();
		$st = $db->prepare('INSERT INTO rt SET post_id=?, original_post_id=?, member_id=?, rt_flg=0');
		$st->execute(array($post['id'], $post['id'], $_SESSION['id']));
		$st = $db->prepare('INSERT INTO rt SET post_id=?, original_post_id=?, member_id=?, rt_flg=1');
		$st->execute(array((int)$putRt['id'], $post['id'], $_SESSION['id']));
		}

	} elseif ((int)$search['post_id'] === (int)$post['id']) {
	//他のアカウントがRTしたものをRTする場合
		// //postTBLにRT分の投稿追加
		// $st = $db->prepare('INSERT INTO posts SET message=?, member_id=?, post_member_id=?,reply_post_id=0, original_post_id=?, rt=1, delete_flg=0, created=?');
		// $st->execute(array($post['message'], $post['member_id'], $_SESSION['id'], $rtorg, $post['created']));
		// //rtTBLにRT分のデータ追加
		// $st = $db->prepare('SELECT * FROM posts WHERE post_member_id=? AND original_post_id=? AND id<>original_post_id');
		// $st->execute(array($_SESSION['id'], $rtorg));
		// $copyrt = $st->fetch();
		// $st = $db->prepare('INSERT INTO rt SET post_id=?, original_post_id=?, member_id=?, rt_flg=0');
		// $st->execute(array($post['id'], $post['id'], $_SESSION['id']));
		// $st = $db->prepare('INSERT INTO rt SET post_id=?, original_post_id=?, member_id=?, rt_flg=1');
		// $st->execute(array($copyrt['id'], $rtorg, $_SESSION['id']));
	}
}
header('Location: index.php'); exit();

?>
