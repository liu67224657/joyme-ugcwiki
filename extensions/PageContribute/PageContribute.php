<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

$wgResourceModules['ext.page.contribute.js'] = array(
	'scripts' => array('modules/index.js'),
	'messages' => array(),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'PageContribute'
);


$wgAutoloadClasses['JoymePageContribute'] = __DIR__ . '/PageContributeClass.php';
require_once( "$IP/extensions/PageContribute/PageContribute_AjaxFunctions.php" );

