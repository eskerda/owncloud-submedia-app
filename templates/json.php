<?php

$errorReporting = false;
$isAllowAccessControl = true;
$allowedAccessControlOrigins = '*'; // *(any) or space separated value

if (!$errorReporting) {
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

/**
 * About boolean keys in json:
 * There was a reason why boolean keys where being transformed into
 * strings ('true', 'false'). Maybe some client needs it, but at
 * least in the latest subtunes version there's no need for it.
 */
/*
$_['response'] = OCA\Submedia\Utils::fixBooleanKeys(
    $_['response'],
    array('isDir', 'isVideo'),
    'true',
    'false',
    function($text) {
        return html_entity_decode($text, ENT_QUOTES);
    }
);
*/

$base = array(
    'subsonic-response' => array(
        'status' => $_['status'],
        'version' => OCA\Submedia\Subsonic::$api_version,
        'xmlns' => 'http://subsonic.org/restapi'
    )
);
if (!empty($_['error'])) {
    $base['subsonic-response']['error'] = array(
        'code' => $_['error']['code'],
        'message' => $_['error']['message']
    );
}
if (!empty($_['response']) && is_array($_['response'])) {
    $base['subsonic-response'] = array_merge(
        $base['subsonic-response'],
        $_['response']
    );
}

/**
 * while we wait for PHP 5.4, just use this
 * ugly replace to unescape slashes on url.
 *
 * Later, use json_encode($result, JSON_UNESCAPED_SLASHES);
 */
$options = 0;
if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
    $options = JSON_PRETTY_PRINT;
}
$content = str_replace('\\/', '/', json_encode($base, $options));

if ($isAllowAccessControl && !empty($allowedAccessControlOrigins)) {
    header('Access-Control-Allow-Origin: ' . $allowedAccessControlOrigins, true);
}

if (!empty($_['callback'])) {
    header('Content-Type: text/javascript;charset=UTF-8');
    echo $_['callback'] . '(' . $content . ');';
}
else {
    header('Content-Type: application/json;charset=UTF-8');
    echo $content;
}
