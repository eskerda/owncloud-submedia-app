<?php

$req_info = pathinfo($_SERVER['PATH_INFO']);
$action = $req_info['filename'];
$response = array();

try {
    $subsonic = new OCA\Submedia\Subsonic($_REQUEST);
    $userid = OC_User::getUser();

    switch ($action) {
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
        case 'search':
            $response = $subsonic->search($_REQUEST, 1);
            break;
        case 'search2':
            $response = $subsonic->search($_REQUEST);
            break;
        case 'search3':
            $response = $subsonic->search($_REQUEST, 3);
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
        case 'getAlbumList':
            $response = $subsonic->getAlbumList($_REQUEST);
            break;
        case 'getArtist':
            $response = $subsonic->getArtist($_REQUEST);
            break;
        case 'getAlbum':
            $response = $subsonic->getAlbum($_REQUEST);
            break;
        case 'getArtists':
            $response = $subsonic->getIndexes($_REQUEST, 180);
            break;
        case 'getCollectionInfo':
            $response = $subsonic->getCollectionInfo($_REQUEST);
            break;
        default:
            throw new Exception('Not Implemented', 30);
    }
} catch (Exception $e) {
    $error = array(
        'message' => $e->getMessage(),
        'code' => $e->getCode()
    );
}

$tmpl = new OCP\Template('submedia', OCA\Submedia\Subsonic::getFormat($_REQUEST));

$tmpl->assign('response', $response, false);
$tmpl->assign('action', $action, false);
if (isset($_REQUEST['callback'])) {
    $tmpl->assign('callback', $_REQUEST['callback']);
}

if (isset($error)) {
    $tmpl->assign('error', $error);
    $tmpl->assign('status', 'failed');
} else {
    $tmpl->assign('status', 'ok');
}

$tmpl->printPage();
