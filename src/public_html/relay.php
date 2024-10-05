<?php

namespace App;

use Exception;

// send the ANS reply to the other targets
$connections = [];
foreach ($relayTargets as $relay) {
    try {
        $relayUri = $relay . "?" . $_SERVER['QUERY_STRING'];
        $connectionid++;
        $connection = curl_init();
        curl_setopt($connection, CURLOPT_URL, $relayUri);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'HTTP_X_ANS_VERIFY_HASH: ' . $_SERVER['HTTP_X_ANS_VERIFY_HASH'] . '',
        ]);
        $connections[] = $connection;
        error_log("setting up curl channel for: " . $relayUri);
    } catch (Exception $e) {
        error_log($relay . " setup error: " . $e->getMessage());
    }
}
error_log("adding curl connections to multi call");
$mh = curl_multi_init();
foreach ($connections as $curlClient) {
    curl_multi_add_handle($mh, $curlClient);
}
error_log("running outbound callbacks");
do {
    $status = curl_multi_exec($mh, $active);
    if ($active) {
        curl_multi_select($mh);
    }
} while ($active && $status == CURLM_OK);
error_log("shutting down");
foreach ($connections as $curlClient) {
    curl_multi_remove_handle($mh, $curlClient);
}
curl_multi_close($mh);
error_log("relay done");
