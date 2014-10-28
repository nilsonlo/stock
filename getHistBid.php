#!/usr/bin/php -q
<?php
#上市股票
require_once(__DIR__.'/auto_load.php');
function array_median($array) {
	$iCount = count($array);
	if ($iCount == 0) {
		return -1;
	}
	$middle_index = floor($iCount / 2);
	sort($array, SORT_NUMERIC);
	$median = $array[$middle_index];
	if ($iCount % 2 == 0) {
		$median = ($median + $array[$middle_index - 1]) / 2;
	}
	return $median;
}

function GetWebService($url,$postData=null)
{
	$cookie = dirname(__FILE__) . '/getHistBid_cookie.txt';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url );
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE );
	curl_setopt($ch, CURLOPT_VERBOSE,0);
	curl_setopt($ch, CURLOPT_COOKIEJAR , $cookie);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
#	curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36');
	if($postData != null)
        {
                curl_setopt($ch,CURLOPT_POST,true);
                curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($postData));
        }
	$ret = curl_exec($ch); 
	return $ret;
}
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n");
$current_date = new DateTime();
try
{
	$dbh = new PDO($DB['DSN'],$DB['DB_USER'], $DB['DB_PWD'],
			array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
				PDO::ATTR_PERSISTENT => false));
	# 錯誤的話, 就不做了
	$dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
	$p1 = $dbh->prepare("select stock_id,twse_stock_id from stock_info");
	$p2 = $dbh->prepare("insert into `warrant_data` (`stock_id`,`warrant_id`,`warrant_iv`,`warrant_type`,`stock_type`)
		values (:stock_id,:warrant_id,:warrant_iv,:warrant_type,:stock_type) 
		on duplicate key update warrant_iv=:warrant_iv,warrant_type=:warrant_type,stock_type=:stock_type");
	$p3 = $dbh->prepare("insert into `warrant_data` (`stock_id`,`warrant_id`,`warrant_name`,`warrant_type`,`warrant_strike`,
		`warrant_days`,`warrant_multi`,`stock_type`,`updated_at`) values (:stock_id,:warrant_id,:warrant_name,
		:warrant_type,:warrant_strike,:warrant_days,:warrant_multi,:stock_type,:updated_at) on duplicate key update
		warrant_name=:warrant_name,warrant_type=:warrant_type,warrant_strike=:warrant_strike,warrant_days=:warrant_days,
		warrant_multi=:warrant_multi,stock_type=:stock_type,updated_at=:updated_at");
	$p4 = $dbh->prepare("delete from `warrant_data`");
	$p4->execute();
	$p1->execute();
	$resData = $p1->fetchAll(PDO::FETCH_ASSOC);
	foreach($resData as $stockItem)
	{
		if(strpos($stockItem['twse_stock_id'],"tse") === false)
			$stock_type=2;
		else
			$stock_type=1;

		$url = "http://warrant.sinotrade.com.tw/warrant2010/json_biv.jsp?ul=".$stockItem['stock_id']."&callback=jsonp";
		$output = GetWebService($url);
		$output = preg_replace('/jsonp\(|\);/','',$output);
		$warrant_data = json_decode($output);
		foreach($warrant_data->wants as $item)
		{
			if(strpos($item->s,"P") === false)
				$warrant_type=1;
			else
				$warrant_type=2;
			$tmpArray = array();
			foreach($item->bivs as $bivObj)
			{
				$tmpArray[] = $bivObj->biv;
			}
			$biv = array_median($tmpArray);
			unset($tmpArray);
			if($biv != -1)
			{
				$p2->execute(array('stock_id'=>$stockItem['stock_id'],
					'warrant_id'=>$item->s,
					'warrant_iv'=>$biv,
					'warrant_type'=>$warrant_type,
					'stock_type'=>$stock_type,
					));
			}
		}
		unset($warrant_data);

		$url = "https://www.warrantwin.com.tw/ws/NewWarSearch.aspx?showType=basic_123&p=CPCode,Derivative,Broker,Conver,Lever,S_BuyIV,E_BuyIV,Sp,Ep,S_Period,E_Period,BuySellRate,PageSize,PageNo,listCode,Amt,Vol&v=7,ALL,ALL,ALL,ALL,,,-10000,10000,,,ALL,8000,1,".$stockItem['stock_id'].",ALL,ALL";
		$formData = array('sEcho'=>1,'iColumns'=>17,'sColumns'=>'','iDisplayStart'=>0,'iDisplayLength'=>200,
			'mDataProp_0'=>0,'mDataProp_1'=>1,'mDataProp_2'=>2,'mDataProp_3'=>3,'mDataProp_4'=>4,
			'mDataProp_5'=>5,'mDataProp_6'=>6,'mDataProp_7'=>7,'mDataProp_8'=>8,'mDataProp_9'=>9,
			'mDataProp_10'=>10,'mDataProp_11'=>11,'mDataProp_12'=>12,'mDataProp_13'=>13,'mDataProp_14'=>14,
			'mDataProp_15'=>15,'mDataProp_16'=>16,'iSortingCols'=>0,'bSortable_0'=>true,'bSortable_1'=>true,
			'bSortable_2'=>true,'bSortable_3'=>true,'bSortable_4'=>true,'bSortable_5'=>true,'bSortable_6'=>true,
			'bSortable_7'=>true,'bSortable_8'=>true,'bSortable_9'=>true,'bSortable_10'=>true,'bSortable_11'=>true,
			'bSortable_12'=>true,'bSortable_13'=>true,'bSortable_14'=>true,'bSortable_15'=>true,'bSortable_16'=>true,
			);
		$output = GetWebService($url,$formData);
		$warrant_obj = json_decode($output);
		if($warrant_obj->iTotalRecords == 0 || !isset($warrant_obj->aaData))
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : '.$stockItem['stock_id'].'-> no warrant data mapping'."\n",3,'./log/stock.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : '.$stockItem['stock_id'].'-> no warrant data mapping'."\n");
			continue;
		}
		foreach($warrant_obj->aaData as $warrant_item)
		{
			if(!isset($warrant_item[3])) continue;
			$id = $warrant_item[3];
			if(strpos($id,'P') === false)
				$warrant_type=1;
			else
				$warrant_type=2;
			$p3->execute(array('stock_id'=>$warrant_item[4],
					'warrant_id'=>$warrant_item[3],
					'warrant_name'=>$warrant_item[2],
					'warrant_type'=>$warrant_type,
					'warrant_strike'=>$warrant_item[10],
					'warrant_days'=>$warrant_item[13],
					'warrant_multi'=>$warrant_item[11],
					'stock_type'=>$stock_type,
					'updated_at'=>$current_date->format("Ymd"),
					));
		}
	}
}
catch(PDOException $e)
{
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/stock.log');
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
}
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n");
