#!/bin/bash
days_ago()
{
	date --date="$1 days ago" +%Y-%m-%d;
}
`./topStock.php`;
`./topStock2.php`;
#`./history_data2.php $(days_ago 0)`;
