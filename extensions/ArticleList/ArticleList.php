<?php
/**
 * ArticleList extension
 */
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'ArticleList',
	'version' => '1.0',
	'author' => array( '' ),
	'descriptionmsg' => '',
	'url' => ''
);

// ResourceLoader support for MediaWiki 1.17+
$wgResourceModules['ext.articlelist.css'] = array(
	'styles' => 'articlelist.css',
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'ArticleList',
	'position' => 'top' // available since r85616
);

$wgResourceModules['ext.articlelist.js'] = array(
	'scripts' => 'articlelist.js',
	'messages' => array(
		'articlelist-once'
	),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'ArticleList'
);


// Set up the new special pages
$dir = __DIR__ . '/';
$wgMessagesDirs['ArticleList'] = __DIR__ . '/i18n';
// magic word setup
$wgExtensionMessagesFiles['ArticleListMagic'] = __DIR__ . '/ArticleList.i18n.php';

$wgAutoloadClasses['ArticleList'] = __DIR__ . '/ArticleList.class.php';

$wgHooks['ParserFirstCallInit'][] = 'ArticleList::onParserSetup';

