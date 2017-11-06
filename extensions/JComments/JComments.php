<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}


$wgResourceModules['ext.jcomments.css'] = array(
	'styles' => array('modules/JComments.css','modules/photoGallery.css','modules/css/comment-daoju.css'),
	'messages' => array(),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'JComments',
	'position' => 'top'
);

$wgResourceModules['ext.jcomments.js'] = array(
	'scripts' => array('modules/joymeEmjoy.js','modules/JCommentcore.js','modules/JComments.js','modules/jquery.imageScroller.min.js','modules/jquery.photo.gallery.js'),
	'messages' => array(),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'JComments'
);
$wgAutoloadClasses['JCommentsHooks'] = __DIR__ . '/JComments.hooks.php';
$wgHooks['SkinAfterContent'][] = 'JCommentsHooks::onSkinAfterContent';
$wgHooks['BeforePageDisplay'][] = 'JCommentsHooks::onBeforePageDisplay';