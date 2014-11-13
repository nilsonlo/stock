#!/usr/bin/php -q
<?php
#上市指數的抓取
require_once('./auto_load.php');
if($argc != 2)
	die('Syntax : '.$argv[0].' yyyy-mm-dd'."\n");
try {
	$current_date = new DateTime($argv[1]);
}catch(Exception $e) {
	die('Syntax : '.$argv[0].' yyyy-mm-dd'."\n");
}

$days = $current_date->format('Ymd');
$INDEX_STOCK_ARRAY = array('t00'=>'tse_t00.tw_'.$days,
			't13'=>'tse_t13.tw_'.$days,
			't17'=>'tse_t17.tw_'.$days,
			);
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n");
$dbh = new PDO($DB['DSN'],$DB['DB_USER'], $DB['DB_PWD'],
		array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
			PDO::ATTR_PERSISTENT => false));
# 錯誤的話, 就不做了
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
$p = $dbh->prepare("insert into `history_data` (`days`,`stock_id`,`stock_name`,`deal_amount`,`start_price`,`highest_price`,
		`lowest_price`,`end_price`,`stock_type`,`created_at`) values (:days,:stock_id,:stock_name,:deal_amount,
		:start_price,:highest_price,:lowest_price,:end_price,0,now()) on duplicate key update
		stock_name=:stock_name,deal_amount=:deal_amount,start_price=:start_price,highest_price=:highest_price,
		lowest_price=:lowest_price,end_price=:end_price,created_at=now()");

$url = 'http://mis.twse.com.tw/stock/api/getStockInfo.jsp?ex_ch='.implode($INDEX_STOCK_ARRAY,"|").'|&json=1&delay=0';
$data = file_get_contents($url);
$dataObject = json_decode($data);
if(isset($dataObject->msgArray))
{
	foreach($dataObject->msgArray as $stock)
	{
		if($stock->c == 't00')
			$name = mb_substr($stock->n,0,3,mb_detect_encoding($stock->n));
		else
			$name = mb_substr($stock->n,0,2,mb_detect_encoding($stock->n));
			
		$itemArray = array(
			'days'=>$days,
			'stock_id'=>$stock->c,
			'stock_name'=>$name,
			'deal_amount'=>isset($stock->v)?$stock->v:0,
			'start_price'=>$stock->o,
			'highest_price'=>$stock->h,
			'lowest_price'=>$stock->l,
			'end_price'=>$stock->z,
			);
		try {
			$p->execute($itemArray);
		} catch (PDOException $e) {
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/stock.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
		}
		unset($itemArray);
	}
}
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n");
