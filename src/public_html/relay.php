<?php

namespace App;

use Exception;

// send the ANS reply to the other targets
$connections = [];
foreach ($relayTargets as $relay) {
    try {
        $relayUri = $relay . "?" . $_SERVER['QUERY_STRING'];
        $connection = curl_init();
        curl_setopt($connection, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($connection, CURLOPT_URL, $relayUri);
        curl_setopt($connection, CURLOPT_HEADER, true);
        curl_setopt($connection, CURLOPT_PROXY_SSL_VERIFYHOST, 0);
        curl_setopt($connection, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($connection, CURLOPT_HEADEROPT, CURLHEADER_UNIFIED);
        curl_setopt($connection, CURLOPT_HTTPHEADER, [
            "X-ANS-VERIFY-HASH: " . $_SERVER['HTTP_X_ANS_VERIFY_HASH'],
        ]);
        curl_setopt($connection, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connection, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($connection, CURLINFO_HEADER_OUT, $debugAns);
        $connections[] = $connection;
        e_log("setting up curl channel for: " . $relayUri);
    } catch (Exception $e) {
        e_log($relay . " setup error: " . $e->getMessage());
    }
}
e_log("adding curl connections to multi call");
$mh = curl_multi_init();
foreach ($connections as $curlClient) {
    curl_multi_add_handle($mh, $curlClient);
}
e_log("running outbound callbacks");
do {
    $status = curl_multi_exec($mh, $active);
    if ($active) {
        curl_multi_select($mh);
    }
} while ($active && $status == CURLM_OK);
e_log("shutting down");
foreach ($connections as $curlClient) {
    e_log("headers: " . curl_getinfo($curlClient, CURLINFO_HEADER_OUT));
    e_log("info: " . curl_multi_getcontent($curlClient));
    curl_multi_remove_handle($mh, $curlClient);
}
curl_multi_close($mh);
e_log("relay done");
