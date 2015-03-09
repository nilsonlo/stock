#!/usr/bin/php -q
<?php
require_once('./auto_load.php');
function CheckHistoryIndexData($dbh,$stock_id,$days)
{
	try {
                # 錯誤的話, 就不做了
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
		# check stock history data
		$p = $dbh->prepare("select * from `history_data` where stock_id=:stock_id and days=:days");
		$p->execute(array('stock_id'=>$stock_id,'days'=>$days));
		if($p->rowCount() === 0)
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No Exists Index : '. $stock_id .
					' in '. $days . "\n",3,'./log/valid.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No Exists Index : '. $stock_id .
					' in '. $days . "\n");
			return -1;
		}
		return 0;
        } catch (PDOException $e) {
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/valid.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
		return -2;
        }
}
function CheckHistoryData($dbh,$stock_id,$days)
{
	try {
                # 錯誤的話, 就不做了
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
		# check stock history data
		$p = $dbh->prepare("select totalamount,deal_amount from `history_data` where stock_id=:stock_id and days=:days");
		$p->execute(array('stock_id'=>$stock_id,'days'=>$days));
		if($p->rowCount() === 0)
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No Exists Stock : '. $stock_id .
					' in '. $days . "\n",3,'./log/valid.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No Exists Stock : '. $stock_id .
					' in '. $days . "\n");
			return -1;
		}
		$item = $p->fetch(PDO::FETCH_ASSOC);
		if(!isset($item['totalamount']))
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Stock : '. $stock_id .
					' in '. $days . ' totalamount unset' . "\n",3,'./log/valid.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Stock : '. $stock_id .
					' in '. $days . ' totalamount unset' . "\n");
			return -2;
		}
		
		$num = intval($item['totalamount']);
		if($num === 0) 
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Stock : '. $stock_id .
					' in '. $days . ' totalamount = 0' . "\n",3,'./log/valid.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Stock : '. $stock_id .
					' in '. $days . ' totalamount = 0' . "\n");
			return -3;
		}
		return 0;
        } catch (PDOException $e) {
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/valid.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
		return -4;
        }
}

if($argc != 2)
{
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Syntax Error : '.$argv[0]." YYYY-mm-dd\n",3,'./log/valid.log');
	error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Syntax Error : '.$argv[0]." YYYY-mm-dd\n");
	exit;
}
$notify = new SendNotify();
$ValidDate = new DateTime($argv[1]);
$days =  $ValidDate->format('Ymd');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Start'."\n",3,'./log/valid.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Start'."\n");
$dbh = new PDO($DB['DSN'],$DB['DB_USER'], $DB['DB_PWD'],
	array( PDO::ATTR_PERSISTENT => false));
$title = "AWS-證交所抓取警示-HistoryData";
$current_date = new DateTime();
# 上市指數檢查
$ret = CheckHistoryIndexData($dbh,'t00',$days);
switch($ret)
{
	case -1:
	case -2:
		$notify->pushNote($title, $current_date->format('Y-m-d H:i:s')." 抓取上市指數資料有誤 t00 ".$ret);
		break;
	default:
		break;
}
# 上市挑一間檢查
$ret = CheckHistoryData($dbh,'2002',$days);
switch($ret)
{
	case -1:
		$notify->pushNote($title, $current_date->format('Y-m-d H:i:s')." 抓取上市股票資料有誤 2002 ".$ret);
		break;
	case -2:
	case -3:
		$notify->pushNote($title, $current_date->format('Y-m-d H:i:s')." 抓取上市公司資料有誤 2002 ".$ret);
		break;
	default:
		break;
}
# 上櫃挑一間檢查
$ret = CheckHistoryData($dbh,'3227',$days);
switch($ret)
{
	case -1:
		$notify->pushNote($title, $current_date->format('Y-m-d H:i:s')." 抓取上櫃股票資料有誤 3227 ".$ret);
		break;
	case -2:
	case -3:
		$notify->pushNote($title, $current_date->format('Y-m-d H:i:s')." 抓取上櫃公司資料有誤 3227 ".$ret);
		break;
	default:
		break;
}
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Finish'."\n",3,'./log/valid.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Finish'."\n");
exit;
?>

