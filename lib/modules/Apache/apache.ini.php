; <?php exit(); __halt_compiler(); ?>
[module]
name = "Httpd Apache Usage"
description = "This module is used to display and log Apache usage data."
status = "true"
has_settings = "true"
has_menu = "false"
logable = "true"
[logging]
args[] = '{"logfile":"apache_usage_%s.log","function":"logData"}'
[chart]
args[] = '{"id":"apache_usage","logfile":"apache_usage_%s.log","function":"getUsageData", "chart_function":"apache_usage", "label":"Apache Usage"}'
[info]
line[] = '{"format":"","args":"","type":"file","filename":"views/links.php"}'
line[] = '{"format":"High (%s): %s","args":"apache_high_time|apache_high"}'
line[] = '{"format":"Low (%s): %s","args":"apache_low_time|apache_low"}'
line[] = '{"format":"Mean: %s","args":"apache_mean"}'
line[] = '{"format":"Latest: %s","args":"apache_latest"}'
[settings]
overload = 3
serverstatus = "http://localhost/server-status"
display_limiting = "true"