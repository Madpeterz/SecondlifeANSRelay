<?php

namespace App;

include("config.php");
include("checks.php");
// reply to ANS that everything is good
http_response_code(200);
print "ok";

include("relay.php");
