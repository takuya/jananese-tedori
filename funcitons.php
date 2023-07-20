<?php
function db() {
  $pdo = new PDO("sqlite:./table.db");
  
  return $pdo;
}

function income_tax( $課税対象額 ) {
  // 所得税
  $sql = 'select * from income_tax where taxable_sal_min<=:sal and :sal < taxable_sal_max;';
  $stmt = db()->prepare($sql);
  $stmt->execute([':sal' => $課税対象額]);
  $ret = $stmt->fetch(PDO::FETCH_ASSOC);
  
  return $ret['tax'];
}

function nenkin( $標準報酬月額 ) {
  // 年金＝厚生年金
  $sql = 'select * from nenkin where min_sal <= :sal and :sal <max_sal ;';
  $stmt = db()->prepare($sql);
  $stmt->execute([':sal' => $標準報酬月額]);
  $ret = $stmt->fetch(PDO::FETCH_ASSOC);
  
  return [
    $ret['price'],
    $ret['price_half'],
  ];
}

function kenpo( $標準報酬月額 ) {
  $sql = 'select * from kenpo where min_sal <= :sal and :sal <max_sal ;';
  $stmt = db()->prepare($sql);
  $stmt->execute([':sal' => $標準報酬月額]);
  $ret = $stmt->fetch(PDO::FETCH_ASSOC);
  
  return [
    $ret['price'],
    $ret['price_half'],
  ];
}