<?php
/**
 * Description:最新更新新增
 * Author: gradydong
 * Date: 2016/12/22
 * Time: 10:32
 * Copyright: Joyme.com
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This is not a valid entry point.\n" );
}

//$wgAutoloadClasses['JoymeRecentChanges'] = __DIR__ . '/JoymeRecentChangesClass.php';

//require_once( __DIR__ . '/JoymeRecentChanges_AjaxFunctions.php' );

// Extension credits that will show up on Special:Version
$wgExtensionCredits['parserhook'][] = array(
    'path' => __FILE__,
    'name' => 'JoymeRecentChanges',
    'version' => '1.0',
    'author' => array( '' ),
    'descriptionmsg' => '',
    'url' => ''
);


$wgResourceModules['ext.joymerecentchanges'] = array(
    'styles' => array(
        'JoymeRecentChanges.css'             => array( ),
    ),
    'scripts' => array(
//        'JoymeRecentChanges.js'

    ),
    'remoteBasePath'        => 'JoymeRecentChanges',
    'localBasePath'         => __DIR__,
    'position' => 'top',
);

// ResourceLoader support for MediaWiki 1.17+
/*$wgResourceModules['ext.joymerecentchanges.css'] = array(
    'styles' => 'JoymeRecentChanges.css',
    'localBasePath' => __DIR__,
    'remoteExtPath' => 'JoymeRecentChanges',
    'position' => 'top' // available since r85616
);

$wgResourceModules['ext.joymerecentchanges.js'] = array(
    'scripts' => 'JoymeRecentChanges.js',
    'localBasePath' => __DIR__,
    'remoteExtPath' => 'JoymeRecentChanges'
);*/

// Set up the new special pages
$dir = __DIR__ . '/';
$wgMessagesDirs['JoymeRecentChanges'] = __DIR__ . '/i18n';

$wgAutoloadClasses['JoymeRecentChangesHooks'] = __DIR__ . '/JoymeRecentChanges.hooks.php';
$wgHooks['ParserFirstCallInit'][] = 'JoymeRecentChangesHooks::onParserFirstCallInit';