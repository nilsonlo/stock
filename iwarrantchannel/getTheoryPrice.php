#!/usr/bin/php -q
<?php
#永豐證券
#require_once(__DIR__.'/../auto_load.php');
function GetWebService($url)
{
	$cookie =  './cookie2.txt';
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
#$url = 'http://iwarrant.capital.com.tw/warrants/wScreenerPull.aspx';
#GetWebService($url);
$url = 'http://warrantchannel.sinotrade.com.tw/want/wSimulation.aspx';
$output = GetWebService($url);
preg_match('/.*vc=\'([^\']*)/',$output,$matches);
#25.85 ->目標股票現價
#0.241 ->委買波動率
#0.27222 -> 剩餘天數/360 (98/360)
$url = "http://cloud01.fortunengine.com.tw/WARNT/GetWcodeTheoryGreek.aspx?wcode=078141&paralist=26.15,0.23199,0.27222&cust_id=SINOTRADE&vc=$matches[1]&callback=jsonp";
$output = GetWebService($url);
$output = preg_replace('/jsonp\({stream:\"|\"}\);/','',$output);
$outputArray = explode(",",$output);
var_dump($outputArray);
/*
array(5) {
  [0]=>
  string(6) "0.8293"	//合理價
  [1]=>
  string(6) "0.4017"	//Delta
  [2]=>
  string(6) "0.1271"	//Gamma
  [3]=>
  string(6) "0.0531"	//Vega
  [4]=>
  string(7) "-0.0069"	//Theta
}
*/
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n");
