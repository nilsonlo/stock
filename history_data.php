#!/usr/bin/php -q
<?php
#上市股票
require_once('./auto_load.php');
if($argc != 2)
	die('Syntax : '.$argv[0].' yyyy-mm-dd'."\n");
try {
	$current_date = new DateTime($argv[1]);
}catch(Exception $e) {
	die('Syntax : '.$argv[0].' yyyy-mm-dd'."\n");
}
$Year = $current_date->format('Y');
$Year2 = $Year-1911;
$Month = $current_date->format('m');
$Day = $current_date->format('d');

error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n");
$dbh = new PDO($DB['DSN'],$DB['DB_USER'], $DB['DB_PWD'],
		array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
			PDO::ATTR_PERSISTENT => false));
# 錯誤的話, 就不做了
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
$days = $Year.$Month.$Day;
$p1 = $dbh->prepare("select * from `history_data` where days=:days and stock_type=1 limit 1");
$p2 = $dbh->prepare("insert into `history_data` (`days`,`stock_id`,`stock_name`,`deal_amount`,`start_price`,`highest_price`,
		`lowest_price`,`end_price`,`stock_type`,`created_at`) values (:days,:stock_id,:stock_name,:deal_amount,
		:start_price,:highest_price,:lowest_price,:end_price,1,now())");
$p1->execute(array('days'=>$days));
if($p1->rowCount() !== 0)
{
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$days.' has exist data'."\n",3,'./log/stock.log');
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$days.' has exist data'."\n");
	die();
}
$url = 'http://www.twse.com.tw/ch/trading/exchange/MI_INDEX/MI_INDEX.php';
$data = WebService::GetWebService($url,'twse_cookie.txt',array('download'=>'','qdate'=>$Year2.'/'.$Month.'/'.$Day,
			'selectType'=>'ALLBUT0999'));
#file_put_contents('1.html',$data);
#exit;

$DataArray = array();
#$data = file_get_contents('1.html');
$html = str_get_html($data);
if(!is_object($html) or !isset($html->nodes))
{
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$days.' html is not object'."\n",3,'./log/stock.log');
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$days.' html is not object'."\n");
	die();
}
$div = $html->find('div#main-content table',1);
if(!isset($div))
{
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$days.' div is undefined'."\n",3,'./log/stock.log');
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$days.' div is undefined'."\n");
	die();
}
foreach($div->find('tr') as $tr)
{
	$itemArray = array();
	if(is_object($tr->children(15)))
	{
		$itemArray = array();
		$itemArray['days'] = $days;
		$itemArray['stock_id'] = $tr->children(0)->plaintext;			//char
		$itemArray['stock_name'] = $tr->children(1)->plaintext;			//char
		$amount = str_replace(",","",$tr->children(2)->plaintext);
		$itemArray['deal_amount'] = intval($amount);
		$amount = str_replace(",","",$tr->children(5)->plaintext);
		$itemArray['start_price'] = floatval($amount);
		$amount = str_replace(",","",$tr->children(6)->plaintext);
		$itemArray['highest_price'] = floatval($amount);
		$amount = str_replace(",","",$tr->children(7)->plaintext);
		$itemArray['lowest_price'] = floatval($amount);
		$amount = str_replace(",","",$tr->children(8)->plaintext);
		$itemArray['end_price'] = floatval($amount);
		if(!preg_match('/^[0-9]{1}/',$itemArray['stock_id']))	// Ignore Title Describe
		{
			unset($itemArray);
			continue;
		}
		try {
			$p2->execute($itemArray);
		} catch (PDOException $e) {
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/stock.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
			unset($itemArray);
			continue;	
		}
		unset($itemArray);
	}
}
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n");
