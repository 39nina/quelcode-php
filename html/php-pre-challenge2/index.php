<?php
$array = explode(',', $_GET['array']);

// 修正はここから
// バブルソートの考え方は(https://www.youtube.com/watch?v=Hy5DhsGYykw)を参考にしました。

$i = count($array) - 1;
// (配列の中身の数-1)回ソートを繰り返す
for ($i; $i > 0; $i--) {
    // インデックス番号0から、最後から1つ手前のインデックス番号まで1つずつ増やす
    // その際、$iは配列最後の数字を1周毎に１つずつ範囲対象外としている
    for ($index = 0; $index < $i; $index++) {
        // 次のインデックス番号の中身と比較し、自身の方が大きい場合のみ配列の中身を入れ替えて上書きする
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
