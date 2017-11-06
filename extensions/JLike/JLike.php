<?php

if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

$wgResourceModules['ext.jlike.js'] = array(
	'scripts' => array('modules/ShortComments.js','modules/JLike.js'),
	'messages' => array(),
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'JLike'
);
// 'modules/ClickLike.js',
// require_once( "$IP/extensions/JLike/JLike2_Ajaxfonctions.php" );

$wgAutoloadClasses['ShortCommentsPage'] = __DIR__ . '/ShortCommentsPage.php';
$wgAutoloadClasses['ClickLike'] = __DIR__ . '/ClickLike.php';

$wgAutoloadClasses['JLikeHooks'] = __DIR__ . '/JLike.hooks.php';
$wgHooks['BeforePageDisplay'][] = 'JLikeHooks::onBeforePageDisplay';
// $wgHooks['SkinAfterContent'][] = 'JLikeHooks::onSkinAfterContent';

// API
// $wgAutoloadClasses['ClickLikeAPI'] = __DIR__ . '/api/ClickLike.api.php';
// $wgAutoloadClasses['ShortCommentsAPI'] = __DIR__ . '/api/ShortComments.api.php';
// $wgAutoloadClasses['ShortCommentsLikeAPI'] = __DIR__ . '/api/ShortCommentsLike.api.php';
// $wgAPIModules['clicklike'] = 'ClickLikeAPI';
// $wgAPIModules['shortcomments'] = 'ShortCommentsAPI';
// $wgAPIModules['shortcommentslike'] = 'ShortCommentsLikeAPI';


require_once( "$IP/extensions/JLike/JLike_AjaxFunctions.php" );

