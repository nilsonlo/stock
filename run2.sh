#!/bin/bash
days_ago()
{
        date --date="$1 days ago" +%Y-%m-%d;
}
# 抓取必要的股票監控即可
`./parse_stock.php ./stock.csv`;
# 取得相關權證的資訊
`./getHistBid.php`;
# 取得作幣版所需的股票
`./parse_cheat_stock.php`;
`./checkJobValid2.php $(days_ago 0)`;
