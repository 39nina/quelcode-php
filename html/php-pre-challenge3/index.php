<?php
$limit = $_GET['target'];

$dsn = 'mysql:dbname=test;host=mysql';
$dbuser = 'test';
$dbpassword = 'test';

// $limitが正の整数かどうかチェック、正の整数でなければHTMLレスポンシブコード400を返す
function positiveNumber($value) {
    $value2 = (float)$value;
    return intval($value) == $value2 && $value2 > 0;
}
if(!positiveNumber($limit)) {
    http_response_code(400) ;
    exit();
}

// 上記で整数であることが確認できているのでint型に変換
$limit = (int)$limit;

// DB接続
try {
    $db = new PDO('mysql:dbname=test;host=mysql;charset=utf8','test', 'test');
} catch(PDOException $e) {
    echo 'DB接続エラー：' . $e->getMessage();
    exit();
}

// データベースの値の取り出し
$records = $db->query('SELECT * FROM prechallenge3');
$record = $records->fetchAll(PDO::FETCH_COLUMN);
$record = array_map('intval', $record);

// $array:配列全体、$pick:組み合わせる値の数として全ての組み合わせを返すファンクションcombinationを作成
// 参考にしたページ（https://stabucky.com/wp/archives/2188）
function combination ($array, $pick) {
    $sum = count($array);
    if($sum < $pick) {   // 組み合わせにつかう数が配列の総数を超える場合、成立しないためif文の処理を終了
        return;
    } elseif($pick === 1) {  // 配列$arrayのうち単独の値で$limitとの一致を確認する場合、その値を$arrays[$i]に代入
        for($i = 0; $i < $sum; $i++) {
        $arrays[$i] = array($array[$i]);
        }
    } elseif($pick > 1) {  // 配列$arrayのうち複数の値を組み合わせて$limitとの一致を確認する場合、組み合わせの１項目めを固定とするため一度除外して２項目め以降で組み合わせを作る。その後、１項目めを組み合わせ配列$otherPartの頭に挿入し、$arrays[$j]に代入（再帰処理される）
        $j = 0;
        for($i = 0; $i < $sum - $pick + 1; $i++) {
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
$sumCount = count($record);
$combi = [];
for($k = 1; $k < ($sumCount + 1); $k++) {
    $temps = combination($record, $k);
    foreach($temps as $temp) {
        if(array_sum($temp) === $limit) {
            array_push($combi,$temp);
        }
    }
}

// json形式で出力
$json_combi = json_encode($combi);
print($json_combi);

?>