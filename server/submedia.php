<?php

OCP\App::checkAppEnabled('media');
require_once(OC::$APPSROOT . '/apps/media/lib_collection.php');
require_once(OC::$APPSROOT . '/apps/submedia/lib/subsonic.php');


$req_info = pathinfo($_SERVER['PATH_INFO']);
$action = $req_info['filename'];
try {
	$submedia = new OC_MEDIA_SUBSONIC($_REQUEST);
	$response = array();
	switch($action){
		case 'ping':
			// Pong >_<
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

$tmpl = new OCP\Template("submedia", OC_MEDIA_SUBSONIC::getFormat($_REQUEST));
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