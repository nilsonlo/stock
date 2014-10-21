#!/usr/bin/php -q
<?php
$day_ago = new DateTime('2015-03-09 00:00:00');
#$day_ago = new DateTime('2014-09-10 00:00:00');
$day_now = new DateTime('2014-09-21 00:00:00');
$interval = $day_ago->diff($day_now);
var_dump($interval);
# 找出的天數要+2
exit;
?>
