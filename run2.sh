#!/bin/bash
days_ago()
{
        date --date="$1 days ago" +%Y-%m-%d;
}
`./parse_stock.php ./419.CSV`;
`./getHistBid.php`;
`./all_parse_stock.php $(days_ago 1)`;
`./checkJobValid2.php $(days_ago 0)`;
