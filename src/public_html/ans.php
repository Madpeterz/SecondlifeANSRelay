<?php

namespace App;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\Psr7\Request;

@ini_set('display_errors', "0");
@ini_set('log_errors', "1");

$relayTargets = [];
$ansSalt = "notLoaded";

foreach (getenv() as $key => $value) {
    if (str_starts_with($key, "AnsRelay_") == true) {
        $relayTargets[] = $value;
    } elseif ($key == "AnsSalt") {
        $ansSalt = $value;
    }
}

if (count($relayTargets) == 0) {
    http_response_code(501);
    error_log('no relay targets loaded', 3, 'php://stdout');
    die("Not accepted here"); // no relays are setup
}

if ($ansSalt == "notLoaded") {
    http_response_code(501);
    error_log('ANS salt not loaded', 3, 'php://stdout');
    die("Not accepted here"); // ENV for ANS salt not setup
}

if (array_key_exists("HTTP_X_ANS_VERIFY_HASH", $_SERVER) == false) {
    http_response_code(501);
    error_log('HTTP_X_ANS_VERIFY_HASH is missing', 3, 'php://stdout');
    die("Not accepted here"); // HTTP_X_ANS_VERIFY_HASH is missing
}
$checkHash = $_SERVER['HTTP_X_ANS_VERIFY_HASH'];
if ($checkHash == null) {
    http_response_code(501);
    error_log('HTTP_X_ANS_VERIFY_HASH is empty', 3, 'php://stdout');
    die("Not accepted here"); // HTTP_X_ANS_VERIFY_HASH is empty
}
if (array_key_exists("QUERY_STRING", $_SERVER) == false) {
    http_response_code(501);
    error_log('QUERY_STRING is missing', 3, 'php://stdout');
    die("Not accepted here"); // QUERY_STRING is missing
}
$vaildateHash = sha1($_SERVER['QUERY_STRING'] . $ansSalt);
if ($checkHash != $vaildateHash) {
    http_response_code(501);
    error_log('hash checks failed', 3, 'php://stdout');
    die("Not accepted here"); // did not pass checks on the source
}

// reply to ANS that everything is good
http_response_code(200);
print "ok";

// send the ANS reply to the other targets
$promises = [];
$handler = new CurlMultiHandler();
$client = new Client(['handler' => $handle]);
$headers = ['HTTP_X_ANS_VERIFY_HASH' => $_SERVER['HTTP_X_ANS_VERIFY_HASH']];
foreach ($relayTargets as $relay) {
    try {
        error_log("sending to " . $url, 3, 'php://stdout');
        $relayUri = $url . "?" . $_SERVER['QUERY_STRING'];
        $request = new Request("GET", $relayUri, $headers, "");
        $promises[] = $client->getAsync($request);
    } catch (Exception $e) {
        error_log("Failed to write to a relay target " . $relay . " because: " . $e->getMessage(), 3, 'php://stdout');
    }
}

$done = 0;
$waiting = count($promises);
foreach ($promises as $p) {
    $p->then(function () use (&$done): void {
        $done++;
    });
}

$last = microtime(true);
while ($done < $waiting) {
    $now = microtime(true);
    $delta = round(($now - $last) * 1000);
    error_log("tick(" . $delta . ")", 3, 'php://stdout');
    $last = $now;
    $handler->tick();
}
