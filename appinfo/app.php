<?php


OCP\App::checkAppEnabled('media');
OCP\App::checkAppEnabled('submedia');

OC::$CLASSPATH['OC_Media_Collection'] = 'apps/media/lib_collection.php';
OC::$CLASSPATH['OC_Media_Subsonic'] = 'apps/submedia/lib/subsonic.php';

OCP\App::addNavigationEntry( array(
    'id' => 'submedia',
    'order' => 74,
    'href' => OCP\Util::linkTo( 'submedia', 'index.php' ),
    'name' => 'SubMedia',
    'icon' => OCP\Util::imagePath('core', 'places/music.svg')
));