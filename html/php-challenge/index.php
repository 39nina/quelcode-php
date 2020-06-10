<?php
session_start();
require('dbconnect.php');

if (isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()) {
	// ログインしている
	$_SESSION['time'] = time();

	$members = $db->prepare('SELECT * FROM members WHERE id=?');
	$members->execute(array($_SESSION['id']));
	$member = $members->fetch();
} else {
	// ログインしていない
	header('Location: login.php'); exit();
}

// 投稿を記録する
if (!empty($_POST)) {
	if ($_POST['message'] != '') {
		$message = $db->prepare('INSERT INTO posts SET member_id=?, post_member_id=?, message=?, reply_post_id=?, created=NOW()');
		$message->execute(array(
			$member['id'],
			$_SESSION['id'],
			$_POST['message'],
			$_POST['reply_post_id']
		));
		$st = $db->exec('UPDATE posts SET original_post_id=id');
		header('Location: index.php'); exit();
	}
}

// 投稿を取得する
$page = $_REQUEST['page'];
if ($page == '') {
	$page = 1;
}
$page = max($page, 1);

// 最終ページを取得する
$counts = $db->query('SELECT COUNT(*) AS cnt FROM posts');
$cnt = $counts->fetch();
$maxPage = ceil($cnt['cnt'] / 5);
$page = min($page, $maxPage);

$start = ($page - 1) * 5;
$start = max(0, $start);

$posts = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id ORDER BY p.id DESC LIMIT ?, 5');
$posts->bindParam(1, $start, PDO::PARAM_INT);
$posts->execute();

// 返信の場合
if (isset($_REQUEST['res'])) {
	$response = $db->prepare('SELECT m.name, m.picture, p.* FROM members m, posts p WHERE m.id=p.member_id AND p.id=? ORDER BY p.created DESC');
	$response->execute(array($_REQUEST['res']));

	$table = $response->fetch();
	$message = '@' . $table['name'] . ' ' . $table['message'];
}

// htmlspecialcharsのショートカット
function h($value) {
	return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

// 本文内のURLにリンクを設定します
function makeLink($value) {
	return mb_ereg_replace("(https?)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", '<a href="\1\2">\1\2</a>' , $value);
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>ひとこと掲示板</title>

	<link rel="stylesheet" href="style.css" />
</head>

<body>
<div id="wrap">
  <div id="head">
    <h1>ひとこと掲示板</h1>
  </div>
  <div id="content">
  	<div style="text-align: right"><a href="logout.php">ログアウト</a></div>
    <form action="" method="post">
      <dl>
        <dt><?php echo h($member['name']); ?>さん、メッセージをどうぞ</dt>
        <dd>
          <textarea name="message" cols="50" rows="5"><?php echo h($message); ?></textarea>
          <input type="hidden" name="reply_post_id" value="<?php echo h($_REQUEST['res']); ?>" />
        </dd>
      </dl>
      <div>
        <p>
          <input type="submit" value="投稿する" />
        </p>
      </div>
    </form>

<?php
foreach ($posts as $post):
?>

    <div class="msg">
	<p class="name">
		<?php
		// RTした人の名前を表示する
		$st = $db->prepare('SELECT * FROM members m, rt r WHERE m.id=r.member_id AND r.post_id=?');
		$st->execute(array($post['id']));
		$rtPerson = $st->fetch();
		if((int)$post['rt'] === 1): ?>
		<img src="images/rt.png" height="16" width="16">
		<?php endif; ?>
		<?php if((int)$post['rt'] === 1) {print($rtPerson['name']) . "さんがリツイート"; } ?>
	</p>
    <img src="member_picture/<?php echo h($post['picture']); ?>" width="48" height="48" alt="<?php echo h($post['name']); ?>" />
    <p><?php echo makeLink(h($post['message'])); ?><span class="name">（<?php echo h($post['name']); ?>）</span>[<a href="index.php?res=<?php echo h($post['id']); ?>">Re</a>]</p>
	<div style="display:flex;">
	<p class="day"><a href="view.php?id=<?php echo h($post['id']); ?>"><?php echo h($post['created']); ?></a>
		<?php
if ($post['reply_post_id'] > 0):
?>
<a href="view.php?id=<?php echo
h($post['reply_post_id']); ?>">
返信元のメッセージ</a>
<?php
endif;
?>
<?php
if ($_SESSION['id'] == $post['member_id']):
?>
[<a href="delete.php?id=<?php echo h($post['id']); ?>"
style="color: #F33;">削除</a>]
<?php
endif;
?>
</p>
<p>
<p style="font-size:15px; padding-left:20px;">
<?php
// 当該ツイートのrt数を調べるための変数：$count
	$st = $db->prepare('SELECT SUM(rt) FROM posts WHERE original_post_id=?');
	$st->execute(array($post['original_post_id']));
	$count = (int)$st->fetch()['SUM(rt)'];

// ログイン者がRT済なら色を変更するための変数：$rtcheck
	$st = $db->prepare('SELECT SUM(rt) FROM posts WHERE original_post_id=? AND post_member_id=?');
	$st->execute(array($post['original_post_id'], $_SESSION['id']));
	$rtcheck = (int)$st->fetch()['SUM(rt)'];

// RT総数が0より大きいものだけRT数を表示し、ログイン者がRTしたものだけ緑に変更する
?>
<a href="rt.php?id=<?php echo h($post['id']); ?>"><img alt="retweet" src="images/rt<?php if ($rtcheck === 1) { print 2; } ?>.png" style="height:16px; width:16px;"></a>
<span style="color:<?php if ($rtcheck === 1) { print "#3CB371";} else { print '#999';} ?>">
<?php
// RTの総数が0より大きいツイートのみ、RT元のいいね数を表示する
if ($count > 0) {
	print $count;
} else {
	print "&nbsp;&nbsp;";
}
?>
</span>

</p>
<p style="font-size:15px; padding-left:7px;">
<?php
$statement = $db->prepare('SELECT * FROM fav WHERE member_id=? AND post_id=?');
$statement->execute(array(h($_SESSION['id']),h($post['id'])));
$fav = (int)($statement->fetch()['fav_flg']);

$st = $db->prepare('SELECT * FROM fav WHERE post_id=? AND member_id=?');
$st->execute(array($post['id'], $_SESSION['id']));
$check = $st->fetch();

// ログイン者がいいね済なら色を変更するための変数：$favcheck
$st = $db->prepare('SELECT SUM(fav_flg) FROM fav WHERE post_id=? AND member_id=?');
$st->execute(array($post['original_post_id'], $_SESSION['id']));
$favcheck = (int)$st->fetch()['SUM(fav_flg)'];

// ログイン者がいいねしたツイートだけハートと数字を赤に変更する
?>
<a href="fav.php?id=<?php echo h($post['id']); ?>"><img alt="fav" src="images/fav<?php if ($favcheck === 1) { print 2; } ?>.png" style="height:14px; width:14px;"></a>
<span style="color:<?php if($favcheck === 1) { print 'red';} else { print '#999';} ?>">
<?php
// いいねの総数が0より大きいツイートのみ、RT元のいいね数を表示する
$st = $db->prepare('SELECT SUM(fav_flg) FROM fav WHERE post_id=?');
$st->execute(array($post['original_post_id']));
$favs = (int)$st->fetch()['SUM(fav_flg)'];
if($favs > 0) {
	print $favs;
}
?>
</span>
</p>
</p>
</div>
</div>
<?php
endforeach;
?>

<ul class="paging">
<?php
if ($page > 1) {
?>
<li><a href="index.php?page=<?php print($page - 1); ?>">前のページへ</a></li>
<?php
} else {
?>
<li>前のページへ</li>
<?php
}
?>
<?php
if ($page < $maxPage) {
?>
<li><a href="index.php?page=<?php print($page + 1); ?>">次のページへ</a></li>
<?php
} else {
?>
<li>次のページへ</li>
<?php
}
?>
</ul>
  </div>
</div>
</body>
</html>
