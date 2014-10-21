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
$code='0050';
#$url = 'http://warrantchannel.sinotrade.com.tw/want/wSearch.aspx?';
#GetWebService($url);
#$url = "http://www.warrantwin.com.tw/wtsearch.aspx?sid=$code";
#GetWebService($url);
#$outputArray = explode("|",$output);
$url = "http://www.warrantwin.com.tw/ws/NewWarSearch.aspx?showType=basic_123&p=CPCode,Derivative,Broker,Conver,Lever,S_BuyIV,E_BuyIV,Sp,Ep,S_Period,E_Period,BuySellRate,PageSize,PageNo,listCode,Amt,Vol&v=7,ALL,ALL,ALL,ALL,,,-10000,10000,,,ALL,8000,1,$code,ALL,ALL";
$output = GetWebService($url,array('iDisplayStart'=>0,'iDisplayLength'=>200));
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
