<?php

/**
 *Description:着迷钩子扩展集合
 *author:Islander
 *date:13:52 2016/6/27
**/
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

$wgAutoloadClasses['JCommentsAPI'] = __DIR__ . '/JComments.api.php';
$wgAutoloadClasses['JCommentsUserInfoAPI'] = __DIR__ . '/JCommentsUserInfo.api.php';
$wgAutoloadClasses['JUserWikiAPI'] = __DIR__ . '/JUserWiki.api.php';
$wgAutoloadClasses['JUserMWikiAPI'] = __DIR__ . '/JUserMWiki.api.php';
$wgAutoloadClasses['JRecommendWikiAPI'] = __DIR__ . '/JRecommendWiki.api.php';
$wgAutoloadClasses['UserBoardAPI'] = __DIR__ . '/UserBoard.api.php';


$wgAPIModules['jcomments'] = 'JCommentsAPI';
$wgAPIModules['jcommentsuserinfo'] = 'JCommentsUserInfoAPI';
$wgAPIModules['juserwiki'] = 'JUserWikiAPI';
$wgAPIModules['jusermwiki'] = 'JUserMWikiAPI';
$wgAPIModules['jrecommendwiki'] = 'JRecommendWikiAPI';
$wgAPIModules['userboard'] = 'UserBoardAPI';

