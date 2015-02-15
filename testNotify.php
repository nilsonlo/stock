#!/usr/bin/php -q
<?php
require_once('./auto_load.php');
if($argc != 2)
{
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Syntax Error : '.$argv[0]." Message\n",3,'./log/test.log');
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Syntax Error : '.$argv[0]." Message\n");
	exit;
}
$notify = new SendNotify();
$current_date = new DateTime();
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Start'."\n",3,'./log/test.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Start'."\n");
$dbh = new PDO($DB['DSN'],$DB['DB_USER'], $DB['DB_PWD'],
	array( PDO::ATTR_PERSISTENT => false));
$title = "[測試]證交所抓取警示";
$notify->pushNote($title, $current_date->format('Y-m-d H:i:s')." ".$argv[1]);
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Finish'."\n",3,'./log/test.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Finish'."\n");
exit;
?>

