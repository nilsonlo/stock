#!/bin/bash
days_ago()
{
	date --date="$1 days ago" +%Y-%m-%d;
}
# 抓取指數資訊
`./twse_data.php $(days_ago 0)`;
# 抓取上市資訊
`./history_data.php $(days_ago 0)`;
# 抓取上櫃資訊
`./history_data2.php $(days_ago 0)`;
# 抓取上市公司股本
`./getCorprationInfo.php sii`;
# 抓取上櫃公司股本
`./getCorprationInfo.php otc`;
# 稽核檢查
`./checkJobValid.php $(days_ago 0)`;
