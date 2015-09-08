<?php
class WebService {
	public static function GetWebService($url,$postData=null,$cookie=null)
	{
		$cookie_file = __DIR__ . '/cookie.txt';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt($ch, CURLOPT_VERBOSE,0);
		curl_setopt($ch, CURLOPT_COOKIEJAR , $cookie_file);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36');
		if(is_null($cookie))
			curl_setopt($ch,CURLOPT_COOKIE,$cookie);
		if(!is_null($postData))
		{
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($postData));
		}
		$ret = curl_exec($ch);
		return $ret;
	}
	public static function GetTWSEService($url,$clean_cookie=false,$cookie=null,$postData=null)
	{
		$cookie_file = __DIR__ . '/tcookie.txt';
		if($clean_cookie === true && file_exists($cookie_file))
			unlink($cookie_file);
			
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_COOKIEJAR , $cookie_file);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
		if(is_null($cookie))
			curl_setopt($ch,CURLOPT_COOKIE,$cookie);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt($ch, CURLOPT_VERBOSE,1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/43.0.2357.124 Safari/537.36",
			"Accept: application/json, text/javascript, */*; q=0.01",
			"Accept-Language: zh-TW,zh;q=0.8,en-US;q=0.6,en;q=0.4,ja;q=0.2",
			"Accept-Encoding: gzip, deflate",
//			"Connection: keep-alive",
			"X-Requested-With: XMLHttpRequest"
			));

		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
#		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36');
		curl_setopt($ch, CURLOPT_REFERER, 'http://mis.twse.com.tw/stock/group.jsp?type=warrant');
		if(!is_null($postData))
		{
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($postData));
		}
		$ret = curl_exec($ch);
		return $ret;
	}
}
