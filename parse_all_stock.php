#!/usr/bin/php -q
<?php
function GetAllStock($dbh)
{
	try {
                # 錯誤的話, 就不做了
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
		# 找出所有的記錄
		$p1 = $dbh->prepare("select * from `history_data` group by `stock_id`");
		$p1->execute();
		return $p1->fetchAll(PDO::FETCH_OBJ);
	} catch (PDOException $e) {
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/stock.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
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
function DeleteAllStockInfo($dbh)
{
	try {
                # 錯誤的話, 就不做了
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
		# 找出所有的記錄
		$p1 = $dbh->prepare("delete from `all_stock_info`");
		$p1->execute();
	} catch (PDOException $e) {
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/stock.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
	}
}

function ComputeStockAmount($dbh,$stock_id,$isIndex=false)
{
	$outputArray = array(
			'lastamount'=>0,'lastprice'=>0,
			'last_highprice'=>0,'last_lowprice'=>0,
			'lastamount2'=>0,'lastprice2'=>0,
			'lastamount3'=>0,'lastprice3'=>0,
			'lastamount4'=>0,'lastprice4'=>0,
			'avgdays3amount'=>0,
			'days5'=>0,'amount5'=>0,'hp_days5'=>0,'hprice5'=>0,'lp_days5'=>0,'lprice5'=>0,
			'days10'=>0,'amount10'=>0,'hp_days10'=>0,'hprice10'=>0,'lp_days10'=>0,'lprice10'=>0,
			'days15'=>0,'amount15'=>0,'hp_days15'=>0,'hprice15'=>0,'lp_days15'=>0,'lprice15'=>0,
			'days20'=>0,'amount20'=>0,'hp_days20'=>0,'hprice20'=>0,'lp_days20'=>0,'lprice20'=>0,
			'days25'=>0,'amount25'=>0,'hp_days25'=>0,'hprice25'=>0,'lp_days25'=>0,'lprice25'=>0,
			'days30'=>0,'amount30'=>0,'hp_days30'=>0,'hprice30'=>0,'lp_days30'=>0,'lprice30'=>0,
			'days35'=>0,'amount35'=>0,'hp_days35'=>0,'hprice35'=>0,'lp_days35'=>0,'lprice35'=>0,
			'days40'=>0,'amount40'=>0,'hp_days40'=>0,'hprice40'=>0,'lp_days40'=>0,'lprice40'=>0,
			'days45'=>0,'amount45'=>0,'hp_days45'=>0,'hprice45'=>0,'lp_days45'=>0,'lprice45'=>0,
			'days50'=>0,'amount50'=>0,'hp_days50'=>0,'hprice50'=>0,'lp_days50'=>0,'lprice50'=>0,
			'days55'=>0,'amount55'=>0,'hp_days55'=>0,'hprice55'=>0,'lp_days55'=>0,'lprice55'=>0,
			'days60'=>0,'amount60'=>0,'hp_days60'=>0,'hprice60'=>0,'lp_days60'=>0,'lprice60'=>0,
			'days65'=>0,'amount65'=>0,'hp_days65'=>0,'hprice65'=>0,'lp_days65'=>0,'lprice65'=>0,
			'days70'=>0,'amount70'=>0,'hp_days70'=>0,'hprice70'=>0,'lp_days70'=>0,'lprice70'=>0,
			'days75'=>0,'amount75'=>0,'hp_days75'=>0,'hprice75'=>0,'lp_days75'=>0,'lprice75'=>0,
			'days80'=>0,'amount80'=>0,'hp_days80'=>0,'hprice80'=>0,'lp_days80'=>0,'lprice80'=>0,
			'days85'=>0,'amount85'=>0,'hp_days85'=>0,'hprice85'=>0,'lp_days85'=>0,'lprice85'=>0,
			'days90'=>0,'amount90'=>0,'hp_days90'=>0,'hprice90'=>0,'lp_days90'=>0,'lprice90'=>0,
			);
	try {
                # 錯誤的話, 就不做了
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
		$sql = 'select * from history_data where stock_id=? order by days desc';
		$p = $dbh->prepare($sql);
		$p->execute(array($stock_id));
		$resData = $p->fetchAll(PDO::FETCH_ASSOC);
		$output = array('days'=>0,'amount'=>0,
				'hdays'=>0,'high_price'=>0,
				'ldays'=>0,'low_price'=>999999,
				'avg_amount'=>0);
			
		foreach($resData as $i=>$item)
		{
			if($output['amount'] < $item['deal_amount'])
			{
				$output['amount'] = $item['deal_amount'];
				$output['days'] = $item['days'];
			}
			if($output['high_price'] < $item['highest_price'])
			{
				$output['high_price'] = $item['highest_price'];
				$output['hdays'] = $item['days'];
			}
			if($output['low_price'] > $item['lowest_price'])
			{
				$output['low_price'] = $item['lowest_price'];
				$output['ldays'] = $item['days'];
			}
			if($isIndex)
			{
				$output['avg_amount'] += intval($item['deal_amount']);
			}

			switch($i)
			{
				case 0:	//昨天
					if(!$isIndex)
						$outputArray['lastamount'] = intval($item['deal_amount']/1000);
					else
						$outputArray['lastamount'] = intval($item['deal_amount']);
					$outputArray['lastprice'] = $item['end_price'];
					$outputArray['last_highprice'] = $item['highest_price'];
					$outputArray['last_lowprice'] = $item['lowest_price'];
					break;
				case 1:	//前天
					if(!$isIndex)
						$outputArray['lastamount2'] = intval($item['deal_amount']/1000);
					else
						$outputArray['lastamount2'] = intval($item['deal_amount']);
					$outputArray['lastprice2'] = $item['end_price'];
					break;
				case 2:	//前2天
					if($isIndex)
						$outputArray['lastamount3'] = intval($item['deal_amount']);
					else
						$outputArray['lastamount3'] = intval($item['deal_amount']/1000);
					//取得3日均量
					$outputArray['avgdays3amount'] = intval($output['avg_amount']/($i+1));
					$outputArray['lastprice3'] = $item['end_price'];
					break;
				case 3:	//前3天
					if(!$isIndex)
						$outputArray['lastamount4'] = intval($item['deal_amount']/1000);
					else
						$outputArray['lastamount4'] = intval($item['deal_amount']);
					$outputArray['lastprice4'] = $item['end_price'];
					break;	
				case 4:
					$outputArray['days5'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount5'] = intval($output['amount']/1000);
					else
						$outputArray['amount5'] = intval($output['amount']);
					$outputArray['hp_days5'] = $output['hdays'];
					$outputArray['hprice5'] = $output['high_price'];
					$outputArray['lp_days5'] = $output['ldays'];
					$outputArray['lprice5'] = $output['low_price'];
					break;
				case 9:
					$outputArray['days10'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount10'] = intval($output['amount']/1000);
					else
						$outputArray['amount10'] = intval($output['amount']);
					$outputArray['hp_days10'] = $output['hdays'];
					$outputArray['hprice10'] = $output['high_price'];
					$outputArray['lp_days10'] = $output['ldays'];
					$outputArray['lprice10'] = $output['low_price'];
					break;
				case 14:
					$outputArray['days15'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount15'] = intval($output['amount']/1000);
					else
						$outputArray['amount15'] = intval($output['amount']);
					$outputArray['hp_days15'] = $output['hdays'];
					$outputArray['hprice15'] = $output['high_price'];
					$outputArray['lp_days15'] = $output['ldays'];
					$outputArray['lprice15'] = $output['low_price'];
					break;
				case 19:
					$outputArray['days20'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount20'] = intval($output['amount']/1000);
					else
						$outputArray['amount20'] = intval($output['amount']);
					$outputArray['hp_days20'] = $output['hdays'];
					$outputArray['hprice20'] = $output['high_price'];
					$outputArray['lp_days20'] = $output['ldays'];
					$outputArray['lprice20'] = $output['low_price'];
					break;
				case 24:
					$outputArray['days25'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount25'] = intval($output['amount']/1000);
					else
						$outputArray['amount25'] = intval($output['amount']);
					$outputArray['hp_days25'] = $output['hdays'];
					$outputArray['hprice25'] = $output['high_price'];
					$outputArray['lp_days25'] = $output['ldays'];
					$outputArray['lprice25'] = $output['low_price'];
					break;
				case 29:
					$outputArray['days30'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount30'] = intval($output['amount']/1000);
					else
						$outputArray['amount30'] = intval($output['amount']);
					$outputArray['hp_days30'] = $output['hdays'];
					$outputArray['hprice30'] = $output['high_price'];
					$outputArray['lp_days30'] = $output['ldays'];
					$outputArray['lprice30'] = $output['low_price'];
					break;
				case 34:
					$outputArray['days35'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount35'] = intval($output['amount']/1000);
					else
						$outputArray['amount35'] = intval($output['amount']);
					$outputArray['hp_days35'] = $output['hdays'];
					$outputArray['hprice35'] = $output['high_price'];
					$outputArray['lp_days35'] = $output['ldays'];
					$outputArray['lprice35'] = $output['low_price'];
					break;
				case 39:
					$outputArray['days40'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount40'] = intval($output['amount']/1000);
					else
						$outputArray['amount40'] = intval($output['amount']);
					$outputArray['hp_days40'] = $output['hdays'];
					$outputArray['hprice40'] = $output['high_price'];
					$outputArray['lp_days40'] = $output['ldays'];
					$outputArray['lprice40'] = $output['low_price'];
					break;
				case 44:
					$outputArray['days45'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount45'] = intval($output['amount']/1000);
					else
						$outputArray['amount45'] = intval($output['amount']);
					$outputArray['hp_days45'] = $output['hdays'];
					$outputArray['hprice45'] = $output['high_price'];
					$outputArray['lp_days45'] = $output['ldays'];
					$outputArray['lprice45'] = $output['low_price'];
					break;
				case 49:
					$outputArray['days50'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount50'] = intval($output['amount']/1000);
					else
						$outputArray['amount50'] = intval($output['amount']);
					$outputArray['hp_days50'] = $output['hdays'];
					$outputArray['hprice50'] = $output['high_price'];
					$outputArray['lp_days50'] = $output['ldays'];
					$outputArray['lprice50'] = $output['low_price'];
					break;
				case 54:
					$outputArray['days55'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount55'] = intval($output['amount']/1000);
					else
						$outputArray['amount55'] = intval($output['amount']);
					$outputArray['hp_days55'] = $output['hdays'];
					$outputArray['hprice55'] = $output['high_price'];
					$outputArray['lp_days55'] = $output['ldays'];
					$outputArray['lprice55'] = $output['low_price'];
					break;
				case 59:
					$outputArray['days60'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount60'] = intval($output['amount']/1000);
					else
						$outputArray['amount60'] = intval($output['amount']);
					$outputArray['hp_days60'] = $output['hdays'];
					$outputArray['hprice60'] = $output['high_price'];
					$outputArray['lp_days60'] = $output['ldays'];
					$outputArray['lprice60'] = $output['low_price'];
					break;
				case 64:
					$outputArray['days65'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount65'] = intval($output['amount']/1000);
					else
						$outputArray['amount65'] = intval($output['amount']);
					$outputArray['hp_days65'] = $output['hdays'];
					$outputArray['hprice65'] = $output['high_price'];
					$outputArray['lp_days65'] = $output['ldays'];
					$outputArray['lprice65'] = $output['low_price'];
					break;
				case 69:
					$outputArray['days70'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount70'] = intval($output['amount']/1000);
					else
						$outputArray['amount70'] = intval($output['amount']);
					$outputArray['hp_days70'] = $output['hdays'];
					$outputArray['hprice70'] = $output['high_price'];
					$outputArray['lp_days70'] = $output['ldays'];
					$outputArray['lprice70'] = $output['low_price'];
					break;
				case 74:
					$outputArray['days75'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount75'] = intval($output['amount']/1000);
					else
						$outputArray['amount75'] = intval($output['amount']);
					$outputArray['hp_days75'] = $output['hdays'];
					$outputArray['hprice75'] = $output['high_price'];
					$outputArray['lp_days75'] = $output['ldays'];
					$outputArray['lprice75'] = $output['low_price'];
					break;
				case 79:
					$outputArray['days80'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount80'] = intval($output['amount']/1000);
					else
						$outputArray['amount80'] = intval($output['amount']);
					$outputArray['hp_days80'] = $output['hdays'];
					$outputArray['hprice80'] = $output['high_price'];
					$outputArray['lp_days80'] = $output['ldays'];
					$outputArray['lprice80'] = $output['low_price'];
					break;
				case 84:
					$outputArray['days85'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount85'] = intval($output['amount']/1000);
					else
						$outputArray['amount85'] = intval($output['amount']);
					$outputArray['hp_days85'] = $output['hdays'];
					$outputArray['hprice85'] = $output['high_price'];
					$outputArray['lp_days85'] = $output['ldays'];
					$outputArray['lprice85'] = $output['low_price'];
					break;
				case 89:
					$outputArray['days90'] = $output['days'];
					if(!$isIndex)
						$outputArray['amount90'] = intval($output['amount']/1000);
					else
						$outputArray['amount90'] = intval($output['amount']);
					$outputArray['hp_days90'] = $output['hdays'];
					$outputArray['hprice90'] = $output['high_price'];
					$outputArray['lp_days90'] = $output['ldays'];
					$outputArray['lprice90'] = $output['low_price'];
					break;
			}
		}
        } catch (PDOException $e) {
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/stock.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
        }
	return $outputArray;
}

function TextIntoDB($dbh,$data)
{
        try {
                # 錯誤的話, 就不做了
        //      $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
                $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
		$p = $dbh->prepare("select * from history_data where stock_id = ? order by days desc limit 1");
                $p->execute(array($data));
		if($p->rowCount() === 0)
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No Exists Stock : '. $data ."\n",3,'./log/stock.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' No Exists Stock : '. $data ."\n");
			return;
		}
		$item = $p->fetch(PDO::FETCH_ASSOC);
		$isIndex = (intval($item['stock_type']) === 0)?true:false;
		$outputArray = ComputeStockAmount($dbh,$item['stock_id'],$isIndex);
		switch($item['stock_type'])
		{
			case '0':
			case '1':
				$twse_stock_id = 'tse_'.$item['stock_id'].'.tw';
				break;
			case '2':
				$twse_stock_id = 'otc_'.$item['stock_id'].'.tw';
				break;
		}
					
		$outputArray['stock_id'] = $item['stock_id'];
		$outputArray['totalamount'] = $item['totalamount'];
		if(!$isIndex)
			$outputArray['lastamount'] = intval($item['deal_amount']/1000);
		else
			$outputArray['lastamount'] = intval($item['deal_amount']);
		$outputArray['lastprice'] = $item['end_price'];
		$outputArray['twse_stock_id'] = $twse_stock_id;
		$outputArray['stock_type'] = $item['stock_type'];
		$p2 = $dbh->prepare("insert into `all_stock_info` (stock_id,
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
			updated_at) values (:stock_id,:twse_stock_id,:stock_type,
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
			now()) on duplicate key update
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
	
		$p2->execute($outputArray);
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
DeleteAllStockInfo($dbh);
$stockArray = GetAllStock($dbh);
$banishStockArray = GetBanishStock($dbh);
foreach($stockArray as $stock)
{
	if(isset($banishStockArray[$stock->stock_id])) continue;
	TextIntoDB($dbh,$stock->stock_id);
}
unset($banishStockArray);
unset($stockArray);
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Finish'."\n",3,'./log/stock.log');
error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Finish'."\n");
exit;
?>

