#!/bin/bash
days_ago()
{
	date --date="$1 days ago" +%Y-%m-%d;
}
for num in {1..130}
do
	#`./history_data.php $(days_ago $num)`;
	#`./history_data2.php $(days_ago $num)`;
	`./twse_data.php $(days_ago $num)`;
done
