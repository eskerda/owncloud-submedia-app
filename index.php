<?php
/**
 * Copyright (c) 2012 Blue-Systems <contact@blue-systems.com>
 * This file is licensed under the Affero General Public License version 3 or
 * later.
 * See the COPYING-README file.
 */


// Check if we are a user
OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('submedia');

OCP\App::setActiveNavigationEntry( 'submedia' );

$tmpl = new OCP\Template("submedia", "index");
$tmpl->printPage();