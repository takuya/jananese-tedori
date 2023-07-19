<?php
$f_name = '年金令和５.md';
$f = new SplFileObject($f_name);
$f->setFlags(SplFileObject::READ_CSV);
$f->setCsvControl('|');

$rows = iterator_to_array($f);
$rows = array_filter(iterator_to_array($f),fn($row)=> preg_match('/\d+/',$row[1]));
$rows = array_map(fn($cols)=>array_map('trim',$cols), $rows);
$rows = array_map(fn($cols)=>array_map(fn($e)=>str_replace(',','',$e),$cols), $rows);
$rows = array_map(fn($cols)=>array_values(array_filter($cols,fn($e)=>$e!="")), $rows);

$sql_lines = [];
foreach ($rows as $row) {
  $inst = sprintf("insert into nenkin values(%7d, %7d, %6d, %5d);", ...$row);
  $sql_lines[]=$inst;
}
$ct = "DROP TABLE if  exists nenkin;
CREATE table if not exists nenkin (
    min_sal int unique,
    max_sal int unique,
    price float unique,
    price_half float unique
);
";
array_unshift($sql_lines,$ct);
echo $SQL = join(PHP_EOL,$sql_lines).PHP_EOL;
file_put_contents('neinkin.sql',$SQL);
$pdo = new PDO("sqlite:./table.db");
$pdo->beginTransaction();
$pdo->exec($SQL);
$pdo->commit();




