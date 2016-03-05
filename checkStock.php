#!/usr/bin/php -q
<?php
#上市股票
require_once(__DIR__.'/auto_load.php');
require 'vendor/autoload.php';
$current_date = new DateTime();
$qdate = $current_date->format('Y')-1911 . '/' . $current_date->format('m')
		. '/' . $current_date->format('d');
$days = $current_date->format('Ymd');
#$qdate = '104/02/13';
#$days = 20150213;
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n",3,'./log/top_stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n");
$url = "http://www.twse.com.tw/ch/trading/fund/T86/T86.php";
$output = WebService::GetWebService($url,array('input_date'=>$qdate,
		'select2'=>'ALLBUT0999','sorting'=>'by_issue','login_bt'=>''));
#$output = file_get_contents('1.txt');
$qp_options = array(
	'convert_from_encoding' => 'UTF-8',
	'convert_to_encoding' => 'UTF-8',
	'strip_low_ascii' => FALSE,
	);
$dbh = new PDO($DB['DSN'],$DB['DB_USER'], $DB['DB_PWD'],
	array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
	PDO::ATTR_PERSISTENT => false));
# 錯誤的話, 就不做了
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
$p1 = $dbh->prepare("select stock_id,lastprice*totalamount as amount from stock_info where
                        lastprice*totalamount <= 30000000000 and lastprice<=150 and stock_type!=0 order by amount desc");
$p2 = $dbh->prepare("select * from warrant_data where stock_id=:stock_id and warrant_price >= 0.8");
try {
	# 查一下市值小於500億的
	$p1->execute();
	$items = $p1->fetchAll(PDO::FETCH_OBJ);
	$n = 0;
	foreach($items as $item)
	{
		if(preg_match('/^0/',$item->stock_id)) continue;
		$p2->execute(array('stock_id'=>$item->stock_id));
		if($p2->rowCount() < 8)
			continue;
		print $item->stock_id."\t".$item->amount."\n";
	}
}
catch(PDOException $e)
{
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/top_stock.log');
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
}
catch(Exception $ex)
{
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$ex->getLine().') '.$ex->getMessage()."\n",3,'./log/top_stock.log');
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$ex->getLine().') '.$ex->getMessage()."\n");
}
finally
{
	unset($qp);
}
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n",3,'./log/top_stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n");

