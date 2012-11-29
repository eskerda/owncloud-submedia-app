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
								   					"html_entity_decode" );

$base = array(
	'subsonic-response' => array(
		'status' => $_['status'],
		'version' => '1.7.0',
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
<?php echo str_replace('\\/', '/', json_encode($base)); ?>
<?php if (isset($_['callback'])): ?>);<?php endif; ?>