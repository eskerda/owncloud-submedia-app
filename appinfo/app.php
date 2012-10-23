<?php
//OC::$CLASSPATH['OC_Media_Playlist'] = 'apps/media_playlists/lib/playlist.php';

OCP\App::addNavigationEntry( array(
    'id' => 'submedia',
    'order' => 74,
    'href' => OCP\Util::linkTo( 'submedia', 'index.php' ),
    'name' => 'SubMedia',
    'icon' => OCP\Util::imagePath('core', 'places/music.svg')
));