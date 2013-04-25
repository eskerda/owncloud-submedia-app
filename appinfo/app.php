<?php

OCP\App::checkAppEnabled('media');
OCP\App::checkAppEnabled('submedia');

OC::$CLASSPATH['OCA\Media\Collection'] = 'media/lib/collection.php';
OC::$CLASSPATH['OCA\Submedia\Collection_Extra'] = 'submedia/lib/collection_extra.php';
OC::$CLASSPATH['OCA\Submedia\Subsonic'] = 'submedia/lib/subsonic.php';
OC::$CLASSPATH['OCA\Submedia\Playlist'] = 'submedia/lib/playlist.php';
OC::$CLASSPATH['OCA\Submedia\LastFM'] = 'submedia/lib/lastfm.php';
OC::$CLASSPATH['OCA\Submedia\Utils'] = 'submedia/lib/utils.php';
