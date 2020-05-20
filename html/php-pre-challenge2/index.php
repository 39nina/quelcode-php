<?php
$array = explode(',', $_GET['array']);

// 修正はここから
// バブルソートの考え方は(https://www.youtube.com/watch?v=Hy5DhsGYykw)を参考にしました。

$listCount = count($array);
$last = $listCount - 1; 
// (配列の中身の数-1)回ソートを繰り返す
for ($i = $last; $i > 0; $i--) {
    $last = $i;
    // index番号0から、最後から1つ手前($last)のindex番号まで1つずつ増やす。
    // その際、$lastは1周毎に1つずつ左にずれ、比較範囲を減らしている。
    for ($index = 0; $index < $last; $index++) {
        // 右隣の数字と比較してそれより大きい場合のみ、配列の中身を入れ替えて上書き
        if ($array[$index] > $array[$index + 1]) {
            $temporaryIndex = $array[$index];
            $array[$index] = $array[$index + 1];
            $array[$index + 1] = $temporaryIndex;
        }
    }
}
// 修正はここまで

echo "<pre>";
print_r($array);
echo "</pre>";
