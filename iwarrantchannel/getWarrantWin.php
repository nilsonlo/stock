#!/usr/bin/php -q
<?php
#永豐證券
require_once(__DIR__.'/../auto_load.php');
function GetWebService($url,$postData = null)
{
	$cookie =  './cookie4.txt';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url );
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE );
#	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
#	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
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
CheckLock($argv[0]);
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n");
$code='3481';
#$url = 'http://warrantchannel.sinotrade.com.tw/want/wSearch.aspx?';
#GetWebService($url);
#$url = "http://www.warrantwin.com.tw/wtsearch.aspx?sid=$code";
#GetWebService($url);
#$outputArray = explode("|",$output);
$url = "https://www.warrantwin.com.tw/ws/NewWarSearch.aspx?showType=basic_123&p=CPCode,Derivative,Broker,Conver,Lever,S_BuyIV,E_BuyIV,Sp,Ep,S_Period,E_Period,BuySellRate,PageSize,PageNo,listCode,Amt,Vol&v=7,ALL,ALL,ALL,ALL,,,-10000,10000,,,ALL,8000,1,$code,ALL,ALL";
$formData = array('sEcho'=>1,'iColumns'=>17,'sColumns'=>'','iDisplayStart'=>0,'iDisplayLength'=>500,
			'mDataProp_0'=>0,'mDataProp_1'=>1,'mDataProp_2'=>2,'mDataProp_3'=>3,'mDataProp_4'=>4,
			'mDataProp_5'=>5,'mDataProp_6'=>6,'mDataProp_7'=>7,'mDataProp_8'=>8,'mDataProp_9'=>9,
			'mDataProp_10'=>10,'mDataProp_11'=>11,'mDataProp_12'=>12,'mDataProp_13'=>13,'mDataProp_14'=>14,
			'mDataProp_15'=>15,'mDataProp_16'=>16,'iSortingCols'=>0,'bSortable_0'=>true,'bSortable_1'=>true,
			'bSortable_2'=>true,'bSortable_3'=>true,'bSortable_4'=>true,'bSortable_5'=>true,'bSortable_6'=>true,
			'bSortable_7'=>true,'bSortable_8'=>true,'bSortable_9'=>true,'bSortable_10'=>true,'bSortable_11'=>true,
			'bSortable_12'=>true,'bSortable_13'=>true,'bSortable_14'=>true,'bSortable_15'=>true,'bSortable_16'=>true,
			);
#$output = GetWebService($url,array('iDisplayStart'=>0,'iDisplayLength'=>200));
$output = GetWebService($url,$formData);
echo $output;
exit;
$warrant_obj = json_decode($output);
#var_dump($warrant_obj);
#echo $output;
#exit;
foreach($warrant_obj as $item_obj)
{
	echo json_encode($item_obj,JSON_FORCE_OBJECT);
	#var_dump($item_obj);
	break;
}
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n");
