<?php

$req_info = pathinfo($_SERVER['PATH_INFO']);
$action = $req_info['filename'];
try {
	$subsonic = new OC_Media_Subsonic($_REQUEST);
	$response = array();
	switch($action){
		case 'ping':
			// Pong >_<
			break;
		case 'getMusicFolders':
			$response = $subsonic->getMusicFolders();
			break;
		case 'getMusicDirectory':
			$response = $subsonic->getMusicDirectory($_REQUEST);
			break;
		case 'getIndexes':
			$response = $subsonic->getIndexes($_REQUEST);
			break;
		case 'getLicense':
			break;
		default:
			// Look at my horse, my horse is amazing!
	}
} catch (Exception $e){
	$error = array(
		'message' => $e->getMessage(),
		'code' => $e->getCode()
	);
}

$tmpl = new OCP\Template("submedia", OC_Media_Subsonic::getFormat($_REQUEST));
$tmpl->assign('response', $response);
$tmpl->assign('action', $action);
$tmpl->assign('response', $response);

if (isset($error)){
	$tmpl->assign('error', $error);
	$tmpl->assign('status', 'failed');
} else {
	$tmpl->assign('status', 'ok');
}

$tmpl->printPage();