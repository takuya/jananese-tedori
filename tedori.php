<?php
$時給=2500;
$勤務日数=20;
$定時 = 8;
$給与 = $時給*$定時*$勤務日数;

// 健保＝健康保険証
$sql = 'select * from kenpo where min_sal <= :sal and :sal <max_sal ;';
$pdo = new PDO("sqlite:./table.db");
$stmt = $pdo->prepare($sql);
$stmt->execute([':sal'=>$給与]);
$ret = $stmt->fetch(PDO::FETCH_ASSOC);
$健康保険料 = $ret['price'];
$健保引額 =$ret['price_half'];

// 年金＝厚生年金
$sql = 'select * from nenkin where min_sal <= :sal and :sal <max_sal ;';
$pdo = new PDO("sqlite:./table.db");
$stmt = $pdo->prepare($sql);
$stmt->execute([':sal'=>$給与]);
$ret = $stmt->fetch(PDO::FETCH_ASSOC);
$年金負担額 = $ret['price'];
$年金引去額 =$ret['price_half'];

$社会保険料 = $健康保険料 + $年金負担額;
$雇用引去額 = intval($給与 * 6/1000);
$雇用保険額 = intval($給与 * 95/10000);
$課税対象額 = $給与 - $健保引額 -$年金引去額 - $雇用引去額;
$子ども・子育て拠出金 =intval($給与 * 0.035);

// 所得税
$sql = 'select * from income_tax where taxable_sal_min<=:sal and :sal < taxable_sal_max;';
$stmt = $pdo->prepare($sql);
$stmt->execute([':sal'=>$課税対象額]);
$ret = $stmt->fetch(PDO::FETCH_ASSOC);
$源泉徴収額 = $ret['tax'];

$振込額 = $給与 - $健保引額 - $源泉徴収額;
$format=fn( $x)=>number_format($x);
$人件費 = $給与 + $健保引額 + $年金引去額 + $子ども・子育て拠出金 + $雇用引去額 + $雇用保険額;
$会社から見た時給 = $人件費/($定時*$勤務日数);
echo <<<EOS


給与 {$format($給与)}円 : 時給{$時給}円　✕　${定時}時間勤務　✕　{$勤務日数}日
源泉徴収額(所得税): {$format($源泉徴収額)}円
----------------------
労働者が受け取る金額(振込額/手取り) : {$format($振込額)}円
------------------------------------------
会社が支払う人件費 : {$format($人件費)}円
会社が支払う時給: {$format($会社から見た時給)}円

----(社保負担額）-----
社会保険料 : {$format($社会保険料)}円
(年金＋健康保険証維持費+雇用保険+子育拠出金）
---------------------------------------------------
厚生年金（給与天引き） :  {$format($年金引去額)}
厚生年金（会社補給分） :  {$format($年金引去額)}
健康保険（給与天引き） :  {$format($健保引額)}
健康保険（会社補給分） :  {$format($健保引額)}
雇用保険（給与天引き） :   {$format($雇用引去額)}
雇用保険（会社補給分） :   {$format($雇用保険額)}
子育拠出金（会社補給） :   {$format($子ども・子育て拠出金)}

EOS;



