<?php

@ini_set('display_errors', "off");
@ini_set('log_errors', "on");
@ini_set('error_log', '/proc/self/fd/2');
file_put_contents('php://stdout', 'stdout test');
file_put_contents('php://stderr', 'stderr test');
file_put_contents('/proc/self/fd/2', 'fd/2 test');
file_put_contents('/proc/self/fd/1', 'fd/1 test');
file_put_contents('/proc/1/fd/1', '1/fd/1 test');
file_put_contents('/dev/null', 'dev/null test');
file_put_contents('/dev/stderr', 'dev/stderr test');
file_put_contents('/dev/stdout', 'dev/stdout test');
