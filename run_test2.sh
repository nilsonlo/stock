#!/bin/bash
days_ago()
{
	date --date="$1 days ago" +%Y-%m-%d;
}
`./twse_data.php $(days_ago 1)`;
`./history_data.php $(days_ago 1)`;
`./history_data2.php $(days_ago 1)`;
`./getCorprationInfo.php sii`;
`./getCorprationInfo.php otc`;
`./checkJobValid.php $(days_ago 1)`;
