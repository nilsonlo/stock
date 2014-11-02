#!/usr/bin/php -q
<?php
require_once('./auto_load.php');
function CheckStockInfo($dbh,$stock_id,$days)
{
	try {
                # 錯誤的話, 就不做了
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
		# check stock info data
		$p = $dbh->prepare("select totalamount,date_format(updated_at,'%Y%m%d') as days from `stock_info`
				where stock_id=:stock_id");
		$p->execute(array('stock_id'=>$stock_id));
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
		if(!isset($item['days']))
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Stock : '. $stock_id .
					' in '. $days . ' updated_at unset!' . "\n",3,'./log/valid.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Stock : '. $stock_id .
					' in '. $days . ' updated_at unset!' . "\n");
			return -4;
		}
		$num = $item['days'];
		if($days !== $num)
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Stock : '. $stock_id .
					' in '. $days . ' no match the day : ' . $num . "\n",3,'./log/valid.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Stock : '. $stock_id .
					' in '. $days . ' no match the day : ' . $num . "\n");
			return -5;
		}
		return 0;
        } catch (PDOException $e) {
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/valid.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
		return -6;
        }
}

function CheckWarrantData($dbh)
{
	try {
                # 錯誤的話, 就不做了
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
		# check stock info data
		$p = $dbh->prepare("select count(*) as num from `warrant_data` where warrant_name is null");
		$p2 = $dbh->prepare("select count(*) as num from `warrant_data` where warrant_name is not null");
		$p->execute();
		if($p->rowCount() === 0)
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No exist warrant is null records'.
					"\n",3,'./log/valid.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No exist warrant is null records'.
					"\n");
			return -1;
		}
		$item = $p->fetch(PDO::FETCH_ASSOC);
		if(!isset($item['num']))
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No exist specific column to fetch'.
					"\n",3,'./log/valid.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No exist specific column to fetch'.
					"\n");
			return -2;
		}
		$num = intval($item['num']);
		unset($item);
		$p2->execute();
		if($p2->rowCount() === 0)
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No exist warrant is not null records'.
					"\n",3,'./log/valid.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No exist warrant is not null records'.
					"\n");
			return -3;
		}
		$item = $p2->fetch(PDO::FETCH_ASSOC);
		if(!isset($item['num']))
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No exist specific column to fetch (not null)'.
					"\n",3,'./log/valid.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No exist specific column to fetch (not null)'.
					"\n");
			return -4;
		}
		$num2 = intval($item['num']);
		unset($item);
		if($num >= $num2)
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' warrant data parser may be wrong'.
					$num.'<->'.$num2 ."\n",3,'./log/valid.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' warrant data parser may be wrong'.
					$num.'<->'.$num2 ."\n");
			return -5;
		}
		return 0;
        } catch (PDOException $e) {
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/valid.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
		return -6;
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
$title = "證交所統計警示-ParseStock";
# 上市挑一間檢查
$ret = CheckStockInfo($dbh,'2002',$days);
switch($ret)
{
	case -1:
		$notify->pushNote($title,"統計上市股票資料有誤");
		break;
	case -2:
	case -3:
		$notify->pushNote($title,"統計上市股票市值資料有誤");
		break;
	case -4:
	case -5:
		$notify->pushNote($title,"統計上市股票資料日期不一致");
		break;
	case -6:
		$notify->pushNote($title,"統計上市公司資料有誤");
		break;
	default:
		break;
}
# 上櫃挑一間檢查
$ret = CheckStockInfo($dbh,'3227',$days);
switch($ret)
{
	case 0:
		break;
	case -1:
		$notify->pushNote($title,"統計上櫃股票資料有誤 ".$ret);
		break;
	case -2:
	case -3:
		$notify->pushNote($title,"統計上櫃股票市值資料有誤 ".$ret);
		break;
	case -4:
	case -5:
		$notify->pushNote($title,"統計上櫃股票資料日期不一致 ".$ret);
		break;
	case -6:
		$notify->pushNote($title,"統計上櫃公司資料有誤 ".$ret);
		break;
	default:
		break;
}

$ret = CheckWarrantData($dbh);
switch($ret)
{
	case 0:
		break;
	case -5:
		$notify->pushNote($title,"抓取權證資料有誤 ".$ret);
		break;
	default:
		$notify->pushNote($title,"抓取權證資料有誤 ".$ret);
		break;
}
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Finish'."\n",3,'./log/valid.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Finish'."\n");
exit;
?>

