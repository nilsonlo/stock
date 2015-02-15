#!/usr/bin/php -q
<?php
#上市股票
require_once(__DIR__.'/auto_load.php');
require 'vendor/autoload.php';
$current_date = new DateTime();
$days = $current_date->format('Y')-1911 . '/' . $current_date->format('m')
		#. '/' . $current_date->format('d');
		. '/13';
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n",3,'./log/top_stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n");
$url = "http://www.twse.com.tw/ch/trading/fund/T86/T86.php";
#$output = WebService::GetWebService($url,array('input_date'=>$days,
#		'select2'=>'0999','sorting'=>'by_issue','login_bt'=>''));
#		'select2'=>'ALLBUT0999','sorting'=>'by_issue','login_bt'=>''));
$output = file_get_contents('1.txt');
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
$p1 = $dbh->prepare("delete from `top_data`");
$p2 = $dbh->prepare("select stock_type from history_data where stock_id=:stock_id limit 1");
$p3 = $dbh->prepare("insert into `top_data` (`code`,`name`,`foreign_buyin`,`foreign_sellout`,
	`trust_buyin`,`trust_sellout`,`self_buyin`,`self_sellout`,`risk_buyin`,`risk_sellout`,
	`summary`,`updated_at`) values (:code,:name,:foreign_buyin,:foreign_sellout,:trust_buyin,
	:trust_sellout,:self_buyin,:self_sellout,:risk_buyin,:risk_sellout,:summary,now()) 
	on duplicate key update name=:name,foreign_buyin=:foreign_buyin,
	foreign_sellout=:foreign_sellout,trust_buyin=:trust_buyin,trust_sellout=:trust_sellout,
	self_buyin=:self_buyin,self_sellout=:self_sellout,risk_buyin=:risk_buyin,
	risk_sellout=:risk_sellout,summary=:summary,updated_at=now()");
try {
	#$qp = htmlqp($output,null,$qp_options);
	$qp = htmlqp($output);
	foreach($qp->find('#tbl-containerx>table tbody tr') as $tr)
	{
		$tds = $tr->branch('td');
		if($tds->count() == 11)
		{
			$elementsArray = $tds->toArray();
			$code = trim($elementsArray[0]->nodeValue);
			$itemArray = array(
				'code'=>$code,
				'name'=>$elementsArray[1]->nodeValue,
				'foreign_buyin'=>str_replace(",","",$elementsArray[2]->nodeValue),
				'foreign_sellout'=>str_replace(",","",$elementsArray[3]->nodeValue),
				'trust_buyin'=>str_replace(",","",$elementsArray[4]->nodeValue),
				'trust_sellout'=>str_replace(",","",$elementsArray[5]->nodeValue),
				'self_buyin'=>str_replace(",","",$elementsArray[6]->nodeValue),
				'self_sellout'=>str_replace(",","",$elementsArray[7]->nodeValue),
				'risk_buyin'=>str_replace(",","",$elementsArray[8]->nodeValue),
				'risk_sellout'=>str_replace(",","",$elementsArray[9]->nodeValue),
				'summary'=>str_replace(",","",$elementsArray[10]->nodeValue),
				);
			$p3->execute($itemArray);
	#		var_dump($itemArray);
			unset($elementsArray);
		}
		unset($tds);
		break;
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

