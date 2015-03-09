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
$p1 = $dbh->prepare("delete from `top_data` where stock_type=1");
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
	# 清除所有的歷史記錄
	$p1->execute();
	#$qp = htmlqp($output,null,$qp_options);
	$qp = htmlqp($output);
	foreach($qp->find('#tbl-containerx>table tbody tr') as $tr)
	{
		$tds = $tr->branch('td');
		if($tds->count() == 11)
		{
			$elementsArray = $tds->toArray();
			$code = trim($elementsArray[0]->nodeValue);
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
				'name'=>$elementsArray[1]->nodeValue,
				'stock_type'=>$stock_info->stock_type,
				'foreign_buyin'=>str_replace(",","",$elementsArray[2]->nodeValue),
				'foreign_sellout'=>str_replace(",","",$elementsArray[3]->nodeValue),
				'trust_buyin'=>str_replace(",","",$elementsArray[4]->nodeValue),
				'trust_sellout'=>str_replace(",","",$elementsArray[5]->nodeValue),
				'self_buyin'=>str_replace(",","",$elementsArray[6]->nodeValue),
				'self_sellout'=>str_replace(",","",$elementsArray[7]->nodeValue),
				'risk_buyin'=>str_replace(",","",$elementsArray[8]->nodeValue),
				'risk_sellout'=>str_replace(",","",$elementsArray[9]->nodeValue),
				'summary'=>str_replace(",","",$elementsArray[10]->nodeValue),
				'total_amount'=>$stock_info->deal_amount,
				);
			$itemArray['risk_summary'] = intval($itemArray['risk_buyin'])
				-intval($itemArray['risk_sellout']);
			$itemArray['self_rate'] = floatval((intval($itemArray['risk_summary'])*100)
				/intval($itemArray['total_amount']));
			$p3->execute($itemArray);
	#		var_dump($itemArray);
			unset($elementsArray);
			unset($stock_info);
		}
		unset($tds);
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

