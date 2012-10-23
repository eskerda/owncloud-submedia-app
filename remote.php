<?php

// only need filesystem apps
$RUNTIME_APPTYPES=array('filesystem','authentication');
OC_App::loadApps($RUNTIME_APPTYPES);

require_once(OC::$APPSROOT . '/apps/submedia/server/submedia.php');