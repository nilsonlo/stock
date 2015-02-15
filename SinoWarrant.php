<?php
#永豐證券
class SinoWarrant {
	private $current_date = null;
	private $dbh = null;
	public function __construct($DB)
	{
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n",3,'./log/stock.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Start'."\n");
		$this->current_date = new DateTime();
		$this->dbh = new PDO($DB['DSN'],$DB['DB_USER'], $DB['DB_PWD'],
			array( PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8",
			PDO::ATTR_PERSISTENT => false));
		# 錯誤的話, 就不做了
		$this->dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_WARNING);
	}

	public function fetchData()
	{
		try
		{
			$p1 = $this->dbh->prepare("select stock_id,twse_stock_id from stock_info where stock_type!=0");
			$p2 = $this->dbh->prepare("insert into `warrant_data` (`stock_id`,`warrant_id`,`warrant_name`,`stock_name`,
		`warrant_type`,`warrant_strike`,`warrant_days`,`warrant_multi`,`stock_type`,`warrant_price`,`updated_at`) values 
		(:stock_id,:warrant_id,:warrant_name,:stock_name,:warrant_type,:warrant_strike,:warrant_days,
		:warrant_multi,:stock_type,:warrant_price,:updated_at) on duplicate key update warrant_name=:warrant_name,
		stock_name=:stock_name,warrant_type=:warrant_type,warrant_strike=:warrant_strike,warrant_days=:warrant_days,
		warrant_multi=:warrant_multi,stock_type=:stock_type,warrant_price=:warrant_price,updated_at=:updated_at");
			$p1->execute();
			$resData = $p1->fetchAll(PDO::FETCH_ASSOC);
			foreach($resData as $stockItem)
			{
				if(strpos($stockItem['twse_stock_id'],"tse") === false)
					$stock_type=2;
				else
					$stock_type=1;
				$url = "http://warrantchannel.sinotrade.com.tw/data/warrants/Get_wSearchResultScodes.aspx?ul=".
					$stockItem['stock_id'] ."&histv=60";
				$output = WebService::GetWebService($url);
				$outputArray = explode("|",$output);
				$url = "http://warrantchannel.sinotrade.com.tw/data/warrants/Get_wSearchResultCont.aspx?wcodelist=".
					$outputArray[0]."&ndays_sv=60";
				$output = WebService::GetWebService($url);
				$warrantObj = json_decode($output);
				foreach($warrantObj as $itemObj)
				{
					if($itemObj->item->callput === "C")
						$warrant_type = 1;
					else
						$warrant_type = 2;
					$p2->execute(array('stock_id'=>$itemObj->item->ulcode,
                        	                'warrant_id'=>$itemObj->item->scode,
                        	                'warrant_name'=>iconv('BIG5','UTF-8',$itemObj->item->sname),
                        	                'stock_name'=>iconv('BIG5','UTF-8',$itemObj->item->ulsname),
						'warrant_type'=>$warrant_type,
                                        	'warrant_strike'=>$itemObj->item->x,
                                        	'warrant_days'=>$itemObj->item->days,
						'warrant_multi'=>$itemObj->item->multi,
						'stock_type'=>$stock_type,
						'warrant_price'=>($itemObj->item->bid == '-')?"0.0":$itemObj->item->bid,
						'updated_at'=>$this->current_date->format("Ymd"),
					));
				}
				unset($output);
				unset($outArray);
				unset($warrant_obj);
			}
		}
		catch(PDOException $e)
		{
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n",3,'./log/stock.log');
			error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ .' Error : ('.$e->getLine().') '.$e->getMessage()."\n");
		}
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n",3,'./log/stock.log');
		error_log('['.date('Y-m-d H:i:s').'] '.__FILE__ . ' Finish'."\n");
	}
}

