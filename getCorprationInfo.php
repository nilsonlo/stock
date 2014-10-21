#!/usr/bin/php -q
<?php
require_once('./auto_load.php');
function GetWebService($url,$postData = null)
{
        $cookie = dirname(__FILE__) . './corpration_cookie.txt';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url );
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE );
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE );
        curl_setopt($ch, CURLOPT_VERBOSE,0);
        curl_setopt($ch, CURLOPT_COOKIEJAR , $cookie);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	if($postData != null)
        {
                curl_setopt($ch,CURLOPT_POST,true);
                curl_setopt($ch,CURLOPT_POSTFIELDS,http_build_query($postData));
        }
        $ret = curl_exec($ch);
        return $ret;
}

if($argc != 2)
{
        error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Syntax Error : '.$argv[0]." sii/otc\n",3,'./log/stock.log');
        error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Syntax Error : '.$argv[0]." sii/otc\n");
        exit;
}
$type = strtolower($argv[1]);
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n");
$dbh = new PDO($DB['DSN'],$DB['DB_USER'], $DB['DB_PWD'],
                array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
                        PDO::ATTR_PERSISTENT => false));
$p1 = $dbh->prepare("select stock_id,days from history_data where stock_id=:stock_id order by days desc limit 1");
$p2 = $dbh->prepare("update history_data set totalamount = :totalamount where days=:days and stock_id=:stock_id");

$url="http://mops.twse.com.tw/mops/web/t51sb01";
//上市 sii
//上櫃 otc
GetWebService($url);
$output = GetWebService("http://mops.twse.com.tw/mops/web/ajax_t51sb01",
	array('encodeURIComponent'=>1,'step'=>1,
		'firstin'=>1,'TYPEK'=>$type,'code'=>''));
//$output = file_get_contents("./corp.txt");
$html = str_get_html($output);
if(!is_object($html) or !isset($html->nodes))
{
        error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$days.' html is not object'."\n",3,'./log/stock.log');
        error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$days.' html is not object'."\n");
        die();
}
//<table style='width:100%;'>
$table = $html->find('table[style="width:100%;"]',0);
if(!isset($table))
{
        error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$days.' table is undefined'."\n",3,'./log/stock.log');
        error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$days.' table is undefined'."\n");
        die();
}
foreach($table->find('tr') as $tr)
{
	if(is_object($tr->children(26)))
	{
		$stock_id = preg_replace("/&#?[a-z0-9]{2,8};/i","",$tr->children(0)->plaintext);
		$corpName = $tr->children(1)->plaintext;
		//Title的忽略
		if($corpName == "公司名稱") continue;
		$tmpAmount = str_replace(",","",$tr->children(14)->plaintext);
		$corpTotalAmount = $tmpAmount;
		try {
			$p1->execute(array("stock_id"=>$stock_id));
			$stock_info = $p1->fetch(PDO::FETCH_ASSOC);
			if($p1->rowCount() !== 1)		//沒找到對應的
			{
        			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$stock_id.' is not in history_data'."\n",3,'./log/stock.log');
        			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$stock_id.' is not in history_data'."\n");
				continue;
			}
			$p2->execute(array("totalamount"=>$corpTotalAmount,
					"days"=>$stock_info['days'],
					"stock_id"=>$stock_id));
		}catch (PDOException $e)
		{
        		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$e->getLine().' '.$e->getMessage()."\n",3,'./log/stock.log');
        		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' '.$e->getLine().' '.$e->getMessage()."\n");
			continue;
		}
	}
}
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' End'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' End'."\n");
