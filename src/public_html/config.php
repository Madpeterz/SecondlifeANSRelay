<?php

namespace App;

@ini_set('display_errors', "off");
@ini_set('log_errors', "on");
@ini_set('e_log', '/proc/self/fd/2');

function e_log(string $message): void
{
    global $debugAns;
    if ($debugAns == true) {
        error_log($message);
    }
}
