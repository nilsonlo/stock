#!/usr/bin/php -q
<?php
#上市股票
require_once(__DIR__.'/auto_load.php');
require 'vendor/autoload.php';
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n",3,'./log/top_history.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n");
$url = "http://www.twse.com.tw/ch/trading/fund/TWT43U/TWT43U.php";
$output = WebService::GetWebService($url);
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
$p1 = $dbh->prepare("delete from `top_data`");
$p2 = $dbh->prepare("select stock_type from history_data where stock_id=:stock_id limit 1");
$p3 = $dbh->prepare("insert into `top_data` (`code`,`name`,`stock_type`,`warrant_type`,
	`self_buyin`,`self_sellout`,`self_summary`,`risk_buyin`,`risk_sellout`,`risk_summary`,
	`updated_at`) values (:code,:name,:stock_type,:warrant_type,:self_buyin,:self_sellout,
	:self_summary,:risk_buyin,:risk_sellout,:risk_summary,now()) on duplicate key update 
	name=:name,stock_type=:stock_type,warrant_type=:warrant_type,self_buyin=:self_buyin,
	self_sellout=:self_sellout,self_summary=:self_summary,risk_buyin=:risk_buyin,
	risk_sellout=:risk_sellout,risk_summary=:risk_summary,updated_at=now()");
try {
	$qp = htmlqp($output,null,$qp_options);
	foreach($qp->find('table tbody tr') as $tr)
	{
		$tds = $tr->branch('td');
		if($tds->count() == 11)
		{
			$elementsArray = $tds->toArray();
			$code = trim($elementsArray[0]->nodeValue);
			$p2->execute(array('stock_id'=>$code));
			if($p2->rowCount() !== 1) {	//Empty Set
				$warrant_type = (strpos($code,"P") == false)?1:2;
				$stock_type = -1;
			}
			else
			{
				$stock_info = $p2->fetch(PDO::FETCH_OBJ);
				$stock_type = $stock_info->stock_type;
				$warrant_type = -1;
			}
			$itemArray = array(
				'code'=>$code,
				'stock_type'=>$stock_type,
				'warrant_type'=>$warrant_type,
				'name'=>$elementsArray[1]->nodeValue,
				#'name'=>iconv('BIG5','UTF-8//IGNORE',$elementsArray[1]->nodeValue),
				'self_buyin'=>str_replace(",","",$elementsArray[2]->nodeValue),
				'self_sellout'=>str_replace(",","",$elementsArray[3]->nodeValue),
				'self_summary'=>str_replace(",","",$elementsArray[4]->nodeValue),
				'risk_buyin'=>str_replace(",","",$elementsArray[5]->nodeValue),
				'risk_sellout'=>str_replace(",","",$elementsArray[6]->nodeValue),
				'risk_summary'=>str_replace(",","",$elementsArray[7]->nodeValue),
				);
			$p3->execute($itemArray);
			unset($elementsArray);
		}
		unset($tds);
	}
}
catch(PDOException $e)
{
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/top_history.log');
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
}
catch(Exception $ex)
{
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$ex->getLine().') '.$ex->getMessage()."\n",3,'./log/top_history.log');
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$ex->getLine().') '.$ex->getMessage()."\n");
}
finally
{
	unset($qp);
}
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n",3,'./log/top_history.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n");

