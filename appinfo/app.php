<?php


OCP\App::checkAppEnabled('media');
OCP\App::checkAppEnabled('submedia');

OC::$CLASSPATH['OC_Media_Collection'] = 'apps/media/lib_collection.php';
OC::$CLASSPATH['OC_Media_Subsonic'] = 'apps/submedia/lib/subsonic.php';
OC::$CLASSPATH['OC_Media_Playlist'] = 'apps/submedia/lib/playlist.php';
