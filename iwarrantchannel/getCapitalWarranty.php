#!/usr/bin/php -q
<?php
# 群益權證
require_once(__DIR__.'/../auto_load.php');
function GetWebService($url)
{
	$cookie = __DIR__ . '/cookie1.txt';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url );
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE );
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE );
	curl_setopt($ch, CURLOPT_VERBOSE,0);
	curl_setopt($ch, CURLOPT_COOKIEJAR , $cookie);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	$ret = curl_exec($ch); 
	return $ret;
}
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n");
$code = "2331";
$url = 'http://iwarrant.capital.com.tw/warrants/wScreenerPull.aspx';
GetWebService($url);
$url = "http://iwarrant.capital.com.tw/data/warrants/Get_wScreenerResultScodes.aspx?ul=$code&histv=60";
$output = GetWebService($url);
$outputArray = explode("|",$output);
#echo $outputArray[0];
$url = "http://iwarrant.capital.com.tw/data/warrants/Get_wScreenerResultCont.aspx?wcodelist=$outputArray[0]&ndays_sv=60";
$output = GetWebService($url);
#$warrant_obj = json_decode($output);
#var_dump($warrant_obj);
echo $output;
exit;
foreach($warrant_obj as $item_obj)
{
	echo json_encode($item_obj);
	break;
}
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n");
exit;
