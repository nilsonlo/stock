#!/bin/bash
days_ago()
{
        date --date="$1 days ago" +%Y-%m-%d;
}
`./parse_stock.php ./stock.csv`;
`./getHistBid.php`;
`./parse_stock_no_warrant.php`;
`./checkJobValid2.php $(days_ago 0)`;
