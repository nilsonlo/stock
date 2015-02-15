<?php
class WebService {
	public static function GetWebService($url,$postData=null,$cookie=null)
	{
		$cookie_file = __DIR__ . '/web_cookie.txt';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt($ch, CURLOPT_VERBOSE,0);
		curl_setopt($ch, CURLOPT_COOKIEJAR , $cookie_file);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
#		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36');
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
}
