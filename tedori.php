<?php
require_once 'funcitons.php';


$時給=1500;
$勤務日数=23;
$定時 = 8;
$給与 = $時給*$定時*$勤務日数;
$交通費 = 0;
$標準報酬月額 = $給与 + $交通費;

$format_yen=fn( $x)=>sprintf("% 8s円", number_format(intval($x)));
$format_p = fn($f)=>sprintf("%3.1f%%", $f*100);

[$健康保険料,$健保引額] = kenpo($給与);
[$厚生年金額,$厚生年金引去額] = nenkin($標準報酬月額);
$雇用保険_個人 = intval($給与 * 6/1000);
$雇用保険_企業= intval($給与 * 9.5/1000);
$子ども・子育て拠出金 =intval($給与 * 0.36/100);

$課税対象額 = $給与 - $健保引額 -$厚生年金引去額 - $雇用保険_個人;
$源泉徴収額 = income_tax($課税対象額);

// 国が受け取る金額
$社会保険料 = $健康保険料 + $厚生年金額 + $雇用保険_個人 + $雇用保険_企業 + $子ども・子育て拠出金;
// 労働者が受け取る金額
$振込額 = $給与 - $健保引額 - $厚生年金引去額 - $雇用保険_個人 - $源泉徴収額;
// 企業（雇用主）絡みたら金額
$人件費 = $給与 + $交通費 + $健保引額 + $厚生年金引去額 + $雇用保険_企業;
$会社から見た時給 = $人件費/($定時*$勤務日数);

$負担率=(($社会保険料+$源泉徴収額)/$人件費);
$受け取り率=1-(($社会保険料+$源泉徴収額)/$人件費);
echo <<<EOS
--[計算条件]---------
  
  時給{$format_yen($時給)}　✕　${定時}時間勤務　✕　{$勤務日数}日

--[労働者]----------
  給与　　　　　：{$format_yen($給与)}
  振込額・手取り：{$format_yen($振込額)}
  --------------------------
      源泉徴収額（所得税）　:{$format_yen($源泉徴収額*-1)}
      厚生年金（給与引去り）:{$format_yen($厚生年金引去額*-1)}
      健康保険（給与引去り）:{$format_yen($健保引額*-1)}
      雇用保険（給与引去り）:{$format_yen($雇用保険_個人*-1)}
  ----------------------------------

  
---[企業]---------------
    会社が支払う人件費　: {$format_yen($人件費)}
    　会社からみた時給　: {$format_yen($会社から見た時給)}
    労働者の月給　　　　: {$format_yen($給与)}
    　労働者からみた時給: {$format_yen($時給)}

    ----(社保負担額）-----
      厚生年金（会社賦課）　:  {$format_yen($厚生年金引去額)}
      健康保険（会社賦課）　:  {$format_yen($健保引額)}
      雇用保険（会社賦課）　:  {$format_yen($雇用保険_企業)}
      少子化対策（会社賦課）:  {$format_yen($子ども・子育て拠出金)}

---[日本政府]-----------
    政府の手取り：{$format_yen($社会保険料 + $源泉徴収額)}

    ----(この雇用で、日本国が受け取る金額）-----
    源泉徴収額 : {$format_yen($源泉徴収額)}
    社会保険料 : {$format_yen($社会保険料)}
      - 厚生年金（給与天引き）: {$format_yen($厚生年金引去額)}
      - 厚生年金（会社賦課）　: {$format_yen($厚生年金引去額)}
      - 健康保険（給与天引き）: {$format_yen($健保引額)}
      - 健康保険（会社賦課）　: {$format_yen($健保引額)}
      - 雇用保険（給与天引き）: {$format_yen($雇用保険_個人)}
      - 雇用保険（会社賦課）　: {$format_yen($雇用保険_企業)}
      - 少子化対策（会社賦課）: {$format_yen($子ども・子育て拠出金)}
      

---[まとめ]-----------　分配率
雇主（企業）： -{$format_yen($人件費)} / 100%
給与労働者　： +{$format_yen($振込額)} / {$format_p($受け取り率)}
日本政府　　： +{$format_yen($社会保険料 + $源泉徴収額)} / {$format_p($負担率)}

EOS;



