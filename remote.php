<?php
OCP\App::checkAppEnabled('media');
OCP\App::checkAppEnabled('submedia');
// only need filesystem apps
$RUNTIME_APPTYPES=array('filesystem','authentication');
OC_App::loadApps($RUNTIME_APPTYPES);

require_once(OC::$SERVERROOT . '/apps/submedia/server/submedia.php');
