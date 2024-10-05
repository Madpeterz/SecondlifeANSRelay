<?php

namespace App;

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
    error_log('no relay targets loaded');
    die("Not accepted here"); // no relays are setup
}

if ($ansSalt == "notLoaded") {
    http_response_code(501);
    error_log('ANS salt not loaded');
    die("Not accepted here"); // ENV for ANS salt not setup
}

if (array_key_exists("HTTP_X_ANS_VERIFY_HASH", $_SERVER) == false) {
    http_response_code(501);
    error_log('HTTP_X_ANS_VERIFY_HASH is missing');
    die("Not accepted here"); // HTTP_X_ANS_VERIFY_HASH is missing
}
$checkHash = $_SERVER['HTTP_X_ANS_VERIFY_HASH'];
if ($checkHash == null) {
    http_response_code(501);
    error_log('HTTP_X_ANS_VERIFY_HASH is empty');
    die("Not accepted here"); // HTTP_X_ANS_VERIFY_HASH is empty
}
if (array_key_exists("QUERY_STRING", $_SERVER) == false) {
    http_response_code(501);
    error_log('QUERY_STRING is missing');
    die("Not accepted here"); // QUERY_STRING is missing
}
$vaildateHash = sha1($_SERVER['QUERY_STRING'] . $ansSalt);
if ($checkHash != $vaildateHash) {
    http_response_code(501);
    error_log('hash checks failed');
    die("Not accepted here"); // did not pass checks on the source
}
