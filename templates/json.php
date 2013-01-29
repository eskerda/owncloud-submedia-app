<?php

$isAllowAccessControl = true;
$allowedAccessControlOrigins = '*'; // *(any) or space separated value

$errorReporting = false;

if (!$errorReporting){
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

$_['response'] = OC_Submedia_Utils::fixBooleanKeys( $_['response'],
                                                    array("isDir","isVideo"),
                                                    true, false,
                                                    function($text){
                                                        return html_entity_decode($text, ENT_QUOTES);
                                                    });

$base = array(
    'subsonic-response' => array(
        'status' => $_['status'],
        'version' => OC_Media_Subsonic::$api_version,
        'xmlns' => 'http://subsonic.org/restapi'
    )
);
if (isset($_['error'])){
    $base['subsonic-response']['error'] = array(
        'code' => $_['error']['code'],
        'message' => $_['error']['message']
    );
}
if (isset($_['response']) && is_array($_['response'])){
    foreach ($_['response'] as $key=>$r){
        $base['subsonic-response'][$key] = $r;
    }
}
if (!isset($_['callback'])){
    header ("Content-Type: application/json;charset=UTF-8");
} else {
    header ("Content-Type: application/json;charset=UTF-8");
}
if ($isAllowAccessControl && !empty($allowedAccessControlOrigins)) {
    header('Access-Control-Allow-Origin: ' . $allowedAccessControlOrigins, true);
}

    /**
    while we wait for PHP 5.4, just use this
    ugly replace to unescape slashes on url.

    Later, use json_encode($result, JSON_UNESCAPED_SLASHES);
    **/
?>
<?php if (isset($_['callback'])): ?><?php echo $_['callback']; ?>(<?php endif; ?>
<?php
    if (phpversion() >= '5.4.0')
        $options = JSON_PRETTY_PRINT;
    else
        $options = 0;
?>
<?php echo str_replace('\\/', '/', json_encode($base, $options)); ?>
<?php if (isset($_['callback'])): ?>);<?php endif; ?>
