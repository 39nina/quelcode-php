<?php
$limit = $_GET['target'];

$dsn = 'mysql:dbname=test;host=mysql';
$dbuser = 'test';
$dbpassword = 'test';

// $limitが正の整数かどうかチェック、正の整数でなければHTMLレスポンシブコード400を返す
if(!ctype_digit($limit)) {
    http_response_code(400);
    exit();
}
// 上記で$limitが正の10進数字であることが確認できたのでint型に変換、0の場合も400を返すためチェック
$limit = (int)$limit;
if($limit === 0) {
    http_response_code(400);
    exit();
}

// DB接続
try {
    $db = new PDO($dsn, $dbuser, $dbpassword);
} catch(PDOException $e) {
    echo 'DB接続エラー：' . $e->getMessage();
    http_response_code(500);
    exit();
}

// DBの値の取り出し
$statement = $db->query('SELECT * FROM prechallenge3');
$records = array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN));

// $array:配列全体、$pick:組み合わせる値の数として全ての組み合わせを返すファンクションcombinationを作成
// 参考にしたページ（https://stabucky.com/wp/archives/2188）
function combination ($array, $pick) {
    $count = count($array);
    if($pick === 1) {  // 配列$arrayのうち単独の値で$limitとの一致を確認する場合、その値を$arrays[$i]に代入
        for($i = 0; $i < $count; $i++) {
            $arrays[$i] = [$array[$i]];
        }
    } elseif($pick > 1) {  // 配列$arrayのうち複数の値を組み合わせて$limitとの一致を確認する場合、組み合わせの１項目めを固定とするため一度除外して２項目め以降で組み合わせを作る。その後、１項目めを組み合わせ配列$otherPartの頭に挿入し、$arrays[$j]に代入（再帰処理される）
        $j = 0;
        for($i = 0; $i < $count - $pick + 1; $i++) {
            $otherPart = combination(array_slice($array, $i + 1), $pick - 1);
            foreach($otherPart as $op) {
                array_unshift($op, $array[$i]);
                $arrays[$j] = $op;
                $j++;
            }
        }
    }
    return $arrays;
}

// combinationファンクションを使い、全組み合わせから$limitと一致するものを配列$combiに代入
$count = count($records);
$combi = [];
for($i = 1; $i < ($count + 1); $i++) {
    $temps = combination($records, $i);
    foreach($temps as $temp) {
        if(array_sum($temp) === $limit) {
            array_push($combi, $temp);
        }
    }
}

// json形式で出力
$jsonCombi = json_encode($combi);
print($jsonCombi);
