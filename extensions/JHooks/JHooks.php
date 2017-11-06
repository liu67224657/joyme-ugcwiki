<?php

/**
 *Description:着迷钩子扩展集合
 *author:Islander
 *date:13:52 2016/6/27
**/
if ( !defined( 'MEDIAWIKI' ) ) {
	die( "This is not a valid entry point.\n" );
}

$wgAutoloadClasses['PageAddonsHooks'] = __DIR__ . '/PageAddons.hooks.php';

$wgHooks['PageContentSaveComplete'][] = 'PageAddonsHooks::pageContentSaveComplete';

// $wgHooks['ArticlePageDataAfter'][] = 'PageAddonsHooks::onArticlePageDataAfter';

