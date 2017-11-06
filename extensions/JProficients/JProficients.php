<?php
/**
 * Description:认证大神
 * Author: gradydong
 * Date: 2016/12/22
 * Time: 10:32
 * Copyright: Joyme.com
 */
if ( !defined( 'MEDIAWIKI' ) ) {
    die( "This is not a valid entry point.\n" );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['parserhook'][] = array(
    'path' => __FILE__,
    'name' => 'JProficients',
    'version' => '1.0',
    'author' => array( '' ),
    'descriptionmsg' => '',
    'url' => ''
);

// ResourceLoader support for MediaWiki 1.17+
$wgResourceModules['ext.jproficients.css'] = array(
    'styles' => 'JProficients.css',
    'localBasePath' => __DIR__,
    'remoteExtPath' => 'JProficients',
    'position' => 'top' // available since r85616
);

$wgResourceModules['ext.jproficients.js'] = array(
    'scripts' => 'JProficients.js',
    'messages' => array(
        'articlelist-once'
    ),
    'localBasePath' => __DIR__,
    'remoteExtPath' => 'JProficients'
);


// Set up the new special pages
$dir = __DIR__ . '/';
$wgMessagesDirs['JProficients'] = __DIR__ . '/i18n';

$wgAutoloadClasses['JProficientsHooks'] = __DIR__ . '/JProficients.hooks.php';
$wgHooks['ParserFirstCallInit'][] = 'JProficientsHooks::onParserFirstCallInit';
