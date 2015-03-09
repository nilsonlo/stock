#!/usr/bin/php -q
<?php
#上櫃股票買賣超統計
require_once(__DIR__.'/auto_load.php');
$current_date = new DateTime();
$qdate = $current_date->format('Y')-1911 . '/' . $current_date->format('m')
		. '/' . $current_date->format('d');
$days = $current_date->format('Ymd');
#$qdate = '104/02/13';
#$days = 20150213;
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n",3,'./log/top_stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n");
$url = "http://www.otc.org.tw/web/stock/3insti/daily_trade/3itrade_hedge_result.php?l=zh-tw&t=D&d=".$qdate;
$output = WebService::GetWebService($url);
#$output = file_get_contents('2.txt');
$dbh = new PDO($DB['DSN'],$DB['DB_USER'], $DB['DB_PWD'],
	array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
	PDO::ATTR_PERSISTENT => false));
# 錯誤的話, 就不做了
$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
$p1 = $dbh->prepare("delete from `top_data` where stock_type=2");
$p2 = $dbh->prepare("select * from history_data where days=:days and stock_id=:stock_id");
$p3 = $dbh->prepare("insert into `top_data` (`code`,`name`,`stock_type`,`foreign_buyin`,
	`foreign_sellout`,`trust_buyin`,`trust_sellout`,`self_buyin`,`self_sellout`,
	`risk_buyin`,`risk_sellout`,`risk_summary`,`summary`,`total_amount`,`self_rate`,
	`updated_at`) values (:code,:name,:stock_type,:foreign_buyin,:foreign_sellout,
	:trust_buyin,:trust_sellout,:self_buyin,:self_sellout,:risk_buyin,:risk_sellout,
	:risk_summary,:summary,:total_amount,:self_rate,now()) on duplicate key update 
	name=:name,stock_type=:stock_type,foreign_buyin=:foreign_buyin,
	foreign_sellout=:foreign_sellout,trust_buyin=:trust_buyin,trust_sellout=:trust_sellout,
	self_buyin=:self_buyin,self_sellout=:self_sellout,risk_buyin=:risk_buyin,
	risk_sellout=:risk_sellout,risk_summary=:risk_summary,summary=:summary,
	total_amount=:total_amount,self_rate=:self_rate,updated_at=now()");
try {
	#清除所有的歷史記錄
	$p1->execute();
	$reportObj = json_decode($output);
	foreach($reportObj->aaData as $item)
	{
		$code = $item[0];
		$p2->execute(array('stock_id'=>$code,'days'=>$days));
		if($p2->rowCount() !== 1)     //Empty Set
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Empty : '.
				$code . "\n", 3, './log/top_stock.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Empty : '.
				$code . "\n");
			continue;
		}
		$stock_info = $p2->fetch(PDO::FETCH_OBJ);
		$itemArray = array(
			'code'=>$code,
			'name'=>$item[1],
			'stock_type'=>$stock_info->stock_type,
			'foreign_buyin'=>str_replace(",","",$item[2]),
			'foreign_sellout'=>str_replace(",","",$item[3]),
			'trust_buyin'=>str_replace(",","",$item[5]),
			'trust_sellout'=>str_replace(",","",$item[6]),
			'self_buyin'=>str_replace(",","",$item[9]),
			'self_sellout'=>str_replace(",","",$item[10]),
			'risk_buyin'=>str_replace(",","",$item[12]),
			'risk_sellout'=>str_replace(",","",$item[13]),
			'summary'=>str_replace(",","",$item[15]),
			'total_amount'=>$stock_info->deal_amount,
			);
		$itemArray['risk_summary'] = intval($itemArray['risk_buyin'])
			-intval($itemArray['risk_sellout']);
		$itemArray['self_rate'] = floatval((intval($itemArray['risk_summary'])*100)
			/intval($itemArray['total_amount']));
		$p3->execute($itemArray);
#		var_dump($itemArray);
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

