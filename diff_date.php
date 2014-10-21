#!/usr/bin/php -q
<?php
require_once('./auto_load.php');
$day_ago = new DateTime('2014-10-05 09:00:00');
#$day_ago = new DateTime('2014-09-10 00:00:00');
$day_now = new DateTime('2014-10-05 13:20:00');
$interval = $day_ago->diff($day_now);
var_dump($interval);
# 找出的天數要+2
exit;
$dbh = new PDO($DB['DSN'],$DB['DB_USER'], $DB['DB_PWD'],
		array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
			PDO::ATTR_PERSISTENT => false));
# 錯誤的話, 就不做了
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
$p1 = $dbh->prepare("select days from `history_data` group by days");
$p2 = $dbh->prepare("select days from `history_data` where stock_id='2002' order by days desc limit 1");
$p1->execute();
echo $p1->rowCount()."\n";
$p2->execute();
$item =  $p2->fetch(PDO::FETCH_ASSOC);
echo $item['days'];

?>
