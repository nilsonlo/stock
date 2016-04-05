#!/usr/bin/php -q
<?php
function GetCheatStock($dbh)
{
	try {
                # 錯誤的話, 就不做了
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
		# 找出所有的記錄
		# 1. 市值350億以下
		# 2. 權證收盤價在0.6以上，達7支
		# 3. 股價10~27元、50~55元、100~270元
		# 4. 代碼扣除0開頭的
		# 5. 股本沒抓到的就忽略
		# 6. 前一日有買進的就要列入
		$p1 = $dbh->prepare("select * from `stock_info` where lastprice*totalamount <= 35000000000 and stock_type !=0
				and lastprice >= 10 and lastprice <= 270");
		$p2 = $dbh->prepare("select * from warrant_data where stock_id=:stock_id and warrant_price >= 0.6");
		$p3 = $dbh->prepare("select stock_id from daily_buyin_stock where stock_id=:stock_id");
		$p1->execute();
		$items = $p1->fetchAll(PDO::FETCH_ASSOC);
		$newArray = [];
		foreach($items as $item)
		{
			if(preg_match('/^0/',$item['stock_id'])) continue;
			$p3->execute(array('stock_id'=>$item['stock_id']));
			if($p3->rowCount() === 1)
			{
				$newArray[] = $item;
				error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ ." $item['stock_id'] 有庫存, 強制列入\n",3,'./log_stock.log');
				error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ ." $item['stock_id'] 有庫存, 強制列入\n");
				continue;
			}
			else
			{
				if($item['totalamount'] == 0) continue;
				//10以下不要
				if(intval($item['lastprice']) < 10) continue;
				//28~49 不要
				if(intval($item['lastprice']) >= 28 && intval($item['lastprice']) < 50) continue;
				//56~99 不要
				if(intval($item['lastprice']) >= 56 && intval($item['lastprice']) < 100) continue;
				$p2->execute(array('stock_id'=>$item['stock_id']));
				if($p2->rowCount() < 7) continue;
				$newArray[] = $item;
			}
		}
		return $newArray;
	} catch (PDOException $e) {
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/stock.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
		return null;
	}
}
function GetBanishStock($dbh)
{
	try {
                # 錯誤的話, 就不做了
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
		# 找出所有黑名單的記錄
		$p1 = $dbh->prepare("select stock_id from `block_stock`");
		$p1->execute();
		return $p1->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP,0);
	} catch (PDOException $e) {
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/stock.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
	}
}
function DeleteCheatStockInfo($dbh)
{
	try {
                # 錯誤的話, 就不做了
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
		# 找出所有的記錄
		$p1 = $dbh->prepare("delete from `cheat_info`");
		$p1->execute();
	} catch (PDOException $e) {
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/stock.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
	}
}


function DeleteDailyBuyinStockInfo($dbh)
{
	try {
                # 錯誤的話, 就不做了
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
		# 找出所有的記錄
		$p1 = $dbh->prepare("delete from `daily_buyin_stock`");
		$p1->execute();
	} catch (PDOException $e) {
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/stock.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
	}
}

function TextIntoDB($dbh,$item)
{
        try {
                # 錯誤的話, 就不做了
        //      $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
		$p1 = $dbh->prepare("insert into `cheat_info` (stock_id,
			twse_stock_id,stock_type,
			totalamount,lastamount,lastprice,last_highprice,
			last_lowprice,lastamount2,lastprice2,
			lastamount3,lastprice3,lastamount4,lastprice4,avgdays3amount,
			days5,amount5,hp_days5,hprice5,lp_days5,lprice5,
			days10,amount10,hp_days10,hprice10,lp_days10,lprice10,
			days15,amount15,hp_days15,hprice15,lp_days15,lprice15,
			days20,amount20,hp_days20,hprice20,lp_days20,lprice20,
			days25,amount25,hp_days25,hprice25,lp_days25,lprice25,
			days30,amount30,hp_days30,hprice30,lp_days30,lprice30,
			days35,amount35,hp_days35,hprice35,lp_days35,lprice35,
			days40,amount40,hp_days40,hprice40,lp_days40,lprice40,
			days45,amount45,hp_days45,hprice45,lp_days45,lprice45,
			days50,amount50,hp_days50,hprice50,lp_days50,lprice50,
			days55,amount55,hp_days55,hprice55,lp_days55,lprice55,
			days60,amount60,hp_days60,hprice60,lp_days60,lprice60,
			days65,amount65,hp_days65,hprice65,lp_days65,lprice65,
			days70,amount70,hp_days70,hprice70,lp_days70,lprice70,
			days75,amount75,hp_days75,hprice75,lp_days75,lprice75,
			days80,amount80,hp_days80,hprice80,lp_days80,lprice80,
			days85,amount85,hp_days85,hprice85,lp_days85,lprice85,
			days90,amount90,hp_days90,hprice90,lp_days90,lprice90,
			block_flag,updated_at) values (:stock_id,:twse_stock_id,:stock_type,
			:totalamount,:lastamount,:lastprice,:last_highprice,
			:last_lowprice,:lastamount2,:lastprice2,
			:lastamount3,:lastprice3,:lastamount4,:lastprice4,:avgdays3amount,
			:days5,:amount5,:hp_days5,:hprice5,:lp_days5,:lprice5,
			:days10,:amount10,:hp_days10,:hprice10,:lp_days10,:lprice10,
			:days15,:amount15,:hp_days15,:hprice15,:lp_days15,:lprice15,
			:days20,:amount20,:hp_days20,:hprice20,:lp_days20,:lprice20,
			:days25,:amount25,:hp_days25,:hprice25,:lp_days25,:lprice25,
			:days30,:amount30,:hp_days30,:hprice30,:lp_days30,:lprice30,
			:days35,:amount35,:hp_days35,:hprice35,:lp_days35,:lprice35,
			:days40,:amount40,:hp_days40,:hprice40,:lp_days40,:lprice40,
			:days45,:amount45,:hp_days45,:hprice45,:lp_days45,:lprice45,
			:days50,:amount50,:hp_days50,:hprice50,:lp_days50,:lprice50,
			:days55,:amount55,:hp_days55,:hprice55,:lp_days55,:lprice55,
			:days60,:amount60,:hp_days60,:hprice60,:lp_days60,:lprice60,
			:days65,:amount65,:hp_days65,:hprice65,:lp_days65,:lprice65,
			:days70,:amount70,:hp_days70,:hprice70,:lp_days70,:lprice70,
			:days75,:amount75,:hp_days75,:hprice75,:lp_days75,:lprice75,
			:days80,:amount80,:hp_days80,:hprice80,:lp_days80,:lprice80,
			:days85,:amount85,:hp_days85,:hprice85,:lp_days85,:lprice85,
			:days90,:amount90,:hp_days90,:hprice90,:lp_days90,:lprice90,
			:block_flag,:updated_at) on duplicate key update
			twse_stock_id=:twse_stock_id,stock_type=:stock_type,
			totalamount=:totalamount,lastamount=:lastamount,lastprice=:lastprice,
			last_highprice=:last_highprice,last_lowprice=:last_lowprice,
			lastamount2=:lastamount2,lastprice2=:lastprice2,
			lastamount3=:lastamount3,lastprice3=:lastprice3,
			lastamount4=:lastamount4,lastprice4=:lastprice4,avgdays3amount=:avgdays3amount,
			days5=:days5,amount5=:amount5,hp_days5=:hp_days5,hprice5=:hprice5,lp_days5=:lp_days5,lprice5=:lprice5,
			days10=:days10,amount10=:amount10,hp_days10=:hp_days10,hprice10=:hprice10,lp_days10=:lp_days10,lprice10=:lprice10,
			days15=:days15,amount15=:amount15,hp_days15=:hp_days15,hprice15=:hprice15,lp_days15=:lp_days15,lprice15=:lprice15,
			days20=:days20,amount20=:amount20,hp_days20=:hp_days20,hprice20=:hprice20,lp_days20=:lp_days20,lprice20=:lprice20,
			days25=:days25,amount25=:amount25,hp_days25=:hp_days25,hprice25=:hprice25,lp_days25=:lp_days25,lprice25=:lprice25,
			days30=:days30,amount30=:amount30,hp_days30=:hp_days30,hprice30=:hprice30,lp_days30=:lp_days30,lprice30=:lprice30,
			days35=:days35,amount35=:amount35,hp_days35=:hp_days35,hprice35=:hprice35,lp_days35=:lp_days35,lprice35=:lprice35,
			days40=:days40,amount40=:amount40,hp_days40=:hp_days40,hprice40=:hprice40,lp_days40=:lp_days40,lprice40=:lprice40,
			days45=:days45,amount45=:amount45,hp_days45=:hp_days45,hprice45=:hprice45,lp_days45=:lp_days45,lprice45=:lprice45,
			days50=:days50,amount50=:amount50,hp_days50=:hp_days50,hprice50=:hprice50,lp_days50=:lp_days50,lprice50=:lprice50,
			days55=:days55,amount55=:amount55,hp_days55=:hp_days55,hprice55=:hprice55,lp_days55=:lp_days55,lprice55=:lprice55,
			days60=:days60,amount60=:amount60,hp_days60=:hp_days60,hprice60=:hprice60,lp_days60=:lp_days60,lprice60=:lprice60,
			days65=:days65,amount65=:amount65,hp_days65=:hp_days65,hprice65=:hprice65,lp_days65=:lp_days65,lprice65=:lprice65,
			days70=:days70,amount70=:amount70,hp_days70=:hp_days70,hprice70=:hprice70,lp_days70=:lp_days70,lprice70=:lprice70,
			days75=:days75,amount75=:amount75,hp_days75=:hp_days75,hprice75=:hprice75,lp_days75=:lp_days75,lprice75=:lprice75,
			days80=:days80,amount80=:amount80,hp_days80=:hp_days80,hprice80=:hprice80,lp_days80=:lp_days80,lprice80=:lprice80,
			days85=:days85,amount85=:amount85,hp_days85=:hp_days85,hprice85=:hprice85,lp_days85=:lp_days85,lprice85=:lprice85,
			days90=:days90,amount90=:amount90,hp_days90=:hp_days90,hprice90=:hprice90,lp_days90=:lp_days90,lprice90=:lprice90,
			updated_at=now()");
	
		$p1->execute($item);
        } catch (PDOException $e) {
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/stock.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
        }
        return ;
}

$ini_array = parse_ini_file("./db.ini",true);
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Start'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Start'."\n");
$DB = $ini_array['DB'];
$dbh = new PDO($DB['DSN'],$DB['DB_USER'], $DB['DB_PWD'],
	array( PDO::ATTR_PERSISTENT => true ));
# 清空股票資訊
DeleteCheatStockInfo($dbh);
$stockArray = GetCheatStock($dbh);
# 清空前一日有庫存的股票資訊
DeleteDailyBuyinStockInfo($dbh);
$banishStockArray = GetBanishStock($dbh);
foreach($stockArray as $stock)
{
	if(isset($banishStockArray[$stock['stock_id']])) continue;
	TextIntoDB($dbh,$stock);
}
unset($banishStockArray);
unset($stockArray);
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Finish'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Finish'."\n");
exit;
?>

