<?php

$f_name = '源泉徴収表令和５年.md';
$f = new SplFileObject($f_name);
$f->setFlags(SplFileObject::READ_CSV);
$f->setCsvControl('|');
$rows = array_filter(iterator_to_array($f),fn($row)=> preg_match('/\d+/',$row[1]));
$rows = array_map(fn($cols)=>array_map('trim',$cols), $rows);
$rows = array_map(fn($cols)=>array_map(fn($e)=>str_replace(',','',$e),$cols), $rows);
$rows = array_map(fn($cols)=>array_values(array_filter($cols,fn($e)=>!empty($e))), $rows);


$sql_lines = [];
foreach ($rows as $row) {
  $inst = sprintf("insert into income_tax values( %7d, %7d, %5d );", ...$row);
  $sql_lines[]=$inst;
}
$ct = "DROP TABLE if  exists income_tax;
CREATE table if not exists income_tax (
    taxable_sal_min int unique,
    taxable_sal_max int unique,
    tax float unique
);
";
array_unshift($sql_lines,$ct);
echo $SQL = join(PHP_EOL,$sql_lines).PHP_EOL;
file_put_contents('gensen.sql',$SQL);
$pdo = new PDO("sqlite:./table.db");
$pdo->beginTransaction();
$pdo->exec($SQL);
$pdo->commit();


