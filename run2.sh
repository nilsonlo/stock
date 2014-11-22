#!/bin/bash
days_ago()
{
        date --date="$1 days ago" +%Y-%m-%d;
}
`./parse_stock.php ./stock.csv ./block_list.csv`;
`./getHistBid.php`;
`./all_parse_stock.php $(days_ago 1)`;
`./checkJobValid2.php $(days_ago 0)`;
