<?php

$req_info = pathinfo($_SERVER['PATH_INFO']);
$action = $req_info['filename'];
$response = array();
try {
	$subsonic = new OC_Media_Subsonic($_REQUEST);
	$userid = OC_User::getUser();
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
		case 'stream':
			$subsonic->stream($_REQUEST);
			break;
		case 'search2':
			$response = $subsonic->search($_REQUEST);
			break;
		case 'getLicense':
			break;
		case 'getPlaylists':
			$response = $subsonic->getPlaylists();
			break;
		case 'createPlaylist':
			$subsonic->createPlaylist($_REQUEST, $_SERVER['QUERY_STRING']);
			break;
		case 'deletePlaylist':
			$response = $subsonic->deletePlaylist($_REQUEST);
			break;
		case 'getPlaylist':
			$response = $subsonic->getPlaylist($_REQUEST);
			break;
		case 'getCoverArt':
			$subsonic->outputCoverArt($_REQUEST);
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
if (OC_Media_Subsonic::getFormat($_REQUEST) == 'xml'){
	// HACK: a bug in owncloud somethimes converts booleans
	// to integers, making type casting *useless*. Hence, a
	// hack:

	function rec_wrapper($data, $boolean_keys){
		$res = array();
		foreach($data as $key=>$value){
			if (is_array($value)){
				$value = rec_wrapper($value, $boolean_keys);
			} else {
				if (in_array($key, $boolean_keys)){
					$value = $value === true?"true":"false";
				}
			}
			$res[$key] = $value;
		}
		return $res;
	}
	$response = rec_wrapper($response, array("isDir","isVideo"));
}
$tmpl->assign('response', $response);
$tmpl->assign('action', $action);
if (isset($_REQUEST['callback'])){
	$tmpl->assign('callback', $_REQUEST['callback']);
}

if (isset($error)){
	$tmpl->assign('error', $error);
	$tmpl->assign('status', 'failed');
} else {
	$tmpl->assign('status', 'ok');
}

$tmpl->printPage();