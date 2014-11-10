<?php
class WebService {
	public static function GetWebService($url,$cookie_file,$postData=null)
	{
		$cookie = dirname(__FILE__) . $cookie_file;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE );
		curl_setopt($ch, CURLOPT_VERBOSE,0);
		curl_setopt($ch, CURLOPT_COOKIEJAR , $cookie);
		curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
#		curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/37.0.2062.120 Safari/537.36');
		if($postData != null)
		{
			curl_setopt($ch,CURLOPT_POST,true);
			curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($postData));
		}
		$ret = curl_exec($ch);
		return $ret;
	}
}
