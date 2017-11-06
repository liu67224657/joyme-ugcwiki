<?php
/**
 * Description:初心者
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
    'name' => 'JNovices',
    'version' => '1.0',
    'author' => array( '' ),
    'descriptionmsg' => '',
    'url' => ''
);

// ResourceLoader support for MediaWiki 1.17+
$wgResourceModules['ext.jnovices.css'] = array(
    'styles' => 'JNovices.css',
    'localBasePath' => __DIR__,
    'remoteExtPath' => 'JNovices',
    'position' => 'top' // available since r85616
);

$wgResourceModules['ext.jnovices.js'] = array(
    'scripts' => 'JNovices.js',
    'messages' => array(
        'articlelist-once'
    ),
    'localBasePath' => __DIR__,
    'remoteExtPath' => 'JNovices'
);


// Set up the new special pages
$dir = __DIR__ . '/';
$wgMessagesDirs['JNovices'] = __DIR__ . '/i18n';

$wgAutoloadClasses['JNovicesHooks'] = __DIR__ . '/JNovices.hooks.php';
$wgHooks['ParserFirstCallInit'][] = 'JNovicesHooks::onParserFirstCallInit';
