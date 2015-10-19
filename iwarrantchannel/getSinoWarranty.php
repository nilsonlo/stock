#!/usr/bin/php -q
<?php
#永豐證券
#require_once(__DIR__.'/../auto_load.php');
function GetWebService($url)
{
	$cookie =  './cookie3.txt';
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
	$ret = curl_exec($ch); 
	return $ret;
}
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n");
$code='2002';
$url = "http://warrantchannel.sinotrade.com.tw/want/wHistBidIV.aspx?";
GetWebService($url);
$url = "http://warrant.sinotrade.com.tw/warrant2010/json_biv.jsp?ul=".$code."&callback=jsonp";
echo GetWebService($url);
$url = "http://warrantchannel.sinotrade.com.tw/data/warrants/Get_wSearchResultScodes.aspx?ul=$code&histv=60";
$output = GetWebService($url);
$outputArray = explode("|",$output);
$url = "http://warrantchannel.sinotrade.com.tw/data/warrants/Get_wSearchResultCont.aspx?wcodelist=$outputArray[0]&ndays_sv=60";
$output = GetWebService($url);
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
