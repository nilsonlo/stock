#!/bin/bash
days_ago()
{
	date --date="$1 days ago" +%Y-%m-%d;
}
for num in {1..1}
do
	#`./history_data.php $(days_ago $num)`;
	#`./history_data2.php $(days_ago $num)`;
	`./otc_warrant_data.php $(days_ago $num)`;
	`./warrant_call_data.php $(days_ago $num)`;
	`./warrant_put_data.php $(days_ago $num)`;
done
