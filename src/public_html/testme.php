<?php

@ini_set('display_errors', "off");
@ini_set('log_errors', "on");

$points = [
    'php://stdout',
    'php://stderr',
    '/proc/self/fd/2',
    '/proc/self/fd/1',
    '/proc/1/fd/1',
    '/dev/null',
    '/dev/stderr',
    '/dev/stdout',
];

foreach ($points as $p) {
    @ini_set('error_log', $p);
    error_log("test of :" . $p);
}
