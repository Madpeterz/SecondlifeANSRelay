<?php

namespace App;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

// send the ANS reply to the other targets
$promises = [];
$client = new Client();
$headers = ['HTTP_X_ANS_VERIFY_HASH' => $_SERVER['HTTP_X_ANS_VERIFY_HASH']];
foreach ($relayTargets as $relay) {
    try {
        $relayUri = $relay . "?" . $_SERVER['QUERY_STRING'];
        error_log("sending to " . $relayUri);
        $request = new Request("GET", $relayUri, $headers, "");
        $promises[] = $client->getAsync($request);
    } catch (Exception $e) {
        error_log("Failed to write to a relay target " . $relay . " because: " . $e->getMessage());
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
    error_log("tick(" . $delta . ")");
    $last = $now;
    $handler->tick();
}
