<?php


OCP\App::checkAppEnabled('media');
OCP\App::checkAppEnabled('submedia');

OC::$CLASSPATH['OC_Media_Collection'] = 'apps/media/lib_collection.php';
OC::$CLASSPATH['OC_Media_Collection_Extra'] = 'apps/submedia/lib/collection_extra.php';
OC::$CLASSPATH['OC_Media_Subsonic'] = 'apps/submedia/lib/subsonic.php';
OC::$CLASSPATH['OC_Media_Playlist'] = 'apps/submedia/lib/playlist.php';
OC::$CLASSPATH['OC_Media_LastFM'] = 'apps/submedia/lib/lastfm.php';
OC::$CLASSPATH['OC_Submedia_Utils'] = 'apps/submedia/lib/utils.php';
