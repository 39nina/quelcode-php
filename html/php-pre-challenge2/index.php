<?php
$array = explode(',', $_GET['array']);

// 修正はここから
// (配列の中身の数-1)回ソートを繰り返す
for ($i = 1; $i < count($array); $i++) {
    // index番号0から、最後から１つ手前のindex番号まで1ずつ増やす（比較対象を移動）
    for ($index = 0; $index < (count($array)-1); $index++) {
        // 右隣の数字と比較してそれより大きい場合のみ、配列の中身を入れ替えて上書き
        if ($array[$index] > $array[$index+1]) {
            $temporaryIndex = $array[$index];
            $array[$index] = $array[$index+1];
            $array[$index+1] = $temporaryIndex;
        }
    }
}
// 修正はここまで

echo "<pre>";
print_r($array);
echo "</pre>";
